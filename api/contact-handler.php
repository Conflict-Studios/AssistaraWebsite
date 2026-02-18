<?php
/**
 * Assistara Contact Form Handler
 * Verarbeitet Formulareinsendungen, validiert Daten, sendet Emails
 */

// Konfiguration laden (ZUERST!)
require_once __DIR__ . '/config.php';

// Fehlerbehandlung
error_reporting(ENVIRONMENT === 'development' ? E_ALL : 0);
ini_set('display_errors', ENVIRONMENT === 'development' ? '1' : '0');

// Session starten für CSRF und Rate Limiting
session_start();

// CORS Headers (nur von eigener Domain erlauben)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST-Requests erlaubt']);
    exit;
}

/**
 * Loggt Fehler in Datei
 */
function logError($message) {
    if (!LOG_ERRORS) return;
    
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}

/**
 * Rate Limiting - Prüft ob IP zu viele Requests sendet
 */
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'contact_submissions_' . md5($ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_request' => time()];
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['first_request'];
    
    // Fenster abgelaufen? Reset counter
    if ($elapsed > RATE_LIMIT_WINDOW) {
        $_SESSION[$key] = ['count' => 1, 'first_request' => time()];
        return true;
    }
    
    // Zu viele Anfragen?
    if ($data['count'] >= MAX_SUBMISSIONS_PER_IP) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * CSRF Token validieren
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * reCAPTCHA v3 validieren
 */
function validateRecaptcha($token) {
    if (empty(RECAPTCHA_SECRET_KEY)) {
        logError('reCAPTCHA Secret Key nicht konfiguriert');
        return ENVIRONMENT === 'development'; // In Dev ohne Key durchlassen
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        logError('reCAPTCHA Verifikation fehlgeschlagen: Netzwerkfehler');
        return false;
    }
    
    $response = json_decode($result, true);
    
    if (!$response['success']) {
        logError('reCAPTCHA Verifikation fehlgeschlagen: ' . json_encode($response));
        return false;
    }
    
    // Score prüfen
    if ($response['score'] < RECAPTCHA_MIN_SCORE) {
        logError("reCAPTCHA Score zu niedrig: {$response['score']}");
        return false;
    }
    
    return true;
}

/**
 * Input bereinigen
 */
function sanitizeInput($input, $maxLength = null) {
    $cleaned = trim($input);
    $cleaned = stripslashes($cleaned);
    $cleaned = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
    
    if ($maxLength && strlen($cleaned) > $maxLength) {
        $cleaned = substr($cleaned, 0, $maxLength);
    }
    
    return $cleaned;
}

/**
 * Formulardaten validieren
 */
function validateFormData($data) {
    $errors = [];
    
    // Pflichtfelder
    if (empty($data['name'])) {
        $errors[] = 'Name ist erforderlich';
    } elseif (strlen($data['name']) > MAX_NAME_LENGTH) {
        $errors[] = 'Name ist zu lang';
    }
    
    if (empty($data['email'])) {
        $errors[] = 'E-Mail ist erforderlich';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültige E-Mail-Adresse';
    } elseif (strlen($data['email']) > MAX_EMAIL_LENGTH) {
        $errors[] = 'E-Mail-Adresse ist zu lang';
    }
    
    if (empty($data['message'])) {
        $errors[] = 'Nachricht ist erforderlich';
    } elseif (strlen($data['message']) > MAX_MESSAGE_LENGTH) {
        $errors[] = 'Nachricht ist zu lang';
    }
    
    if (empty($data['contactType'])) {
        $errors[] = 'Kontaktart ist erforderlich';
    }
    
    // Telefon optional, aber wenn vorhanden validieren
    if (!empty($data['phone']) && strlen($data['phone']) > MAX_PHONE_LENGTH) {
        $errors[] = 'Telefonnummer ist zu lang';
    }
    
    // Honeypot prüfen (sollte leer sein)
    if (!empty($data['website'])) {
        $errors[] = 'Spam erkannt';
    }
    
    return $errors;
}

/**
 * Email-Templates laden
 */
function loadEmailTemplate($templateName, $data) {
    $templatePath = __DIR__ . '/email-templates/' . $templateName;
    
    if (!file_exists($templatePath)) {
        logError("Email-Template nicht gefunden: $templatePath");
        return false;
    }
    
    $template = file_get_contents($templatePath);
    
    // Platzhalter ersetzen
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    
    return $template;
}

/**
 * Email senden mit PHPMailer (oder Fallback auf mail())
 */
function sendEmail($to, $toName, $subject, $htmlBody, $textBody) {
    // Prüfen ob PHPMailer verfügbar ist
    $phpmailerPath = __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
    
    if (file_exists($phpmailerPath)) {
        // PHPMailer verwenden
        return sendEmailWithPHPMailer($to, $toName, $subject, $htmlBody, $textBody);
    } else {
        // Fallback auf native mail() Funktion
        return sendEmailWithNativeMailer($to, $toName, $subject, $htmlBody, $textBody);
    }
}

/**
 * Email senden mit PHPMailer
 */
function sendEmailWithPHPMailer($to, $toName, $subject, $htmlBody, $textBody) {
    require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';
    require_once __DIR__ . '/vendor/PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP-Konfiguration
        if (!empty(SMTP_HOST)) {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
        }
        
        // Absender
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Empfänger
        $mail->addAddress($to, $toName);
        
        // Inhalt
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        logError("Email-Versand fehlgeschlagen: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Email senden mit nativer PHP mail() Funktion (Fallback)
 */
function sendEmailWithNativeMailer($to, $toName, $subject, $htmlBody, $textBody) {
    try {
        // Headers für HTML-Email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Email senden
        $success = mail($to, $subject, $htmlBody, $headers);
        
        if (!$success) {
            logError("Native mail() Funktion fehlgeschlagen für: $to");
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        logError("Email-Versand fehlgeschlagen: " . $e->getMessage());
        return false;
    }
}


// ===== HAUPTLOGIK =====

try {
    // Rate Limiting prüfen
    if (!checkRateLimit()) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.'
        ]);
        exit;
    }
    
    // POST-Daten holen
    $postData = json_decode(file_get_contents('php://input'), true);
    if ($postData === null) {
        $postData = $_POST;
    }
    
    // Debug-Modus: Token-Info ausgeben
    if (ENVIRONMENT === 'development') {
        error_log("Session Token: " . ($_SESSION['csrf_token'] ?? 'nicht vorhanden'));
        error_log("Received Token: " . ($postData['csrf_token'] ?? 'nicht vorhanden'));
    }
    
    // CSRF Token validieren (im Development-Modus weniger streng)
    if (ENVIRONMENT === 'production') {
        if (!validateCSRFToken($postData['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Ungültiges Sicherheitstoken. Bitte laden Sie die Seite neu.'
            ]);
            exit;
        }
    } elseif (ENVIRONMENT === 'development') {
        // Im Development-Modus nur warnen, aber nicht blockieren
        if (!validateCSRFToken($postData['csrf_token'] ?? '')) {
            error_log("WARNUNG: CSRF-Token ungültig (wird im Dev-Modus ignoriert)");
        }
    }
    
    // reCAPTCHA validieren (im Development-Modus weniger streng)
    if (ENVIRONMENT === 'production') {
        if (!validateRecaptcha($postData['recaptcha_token'] ?? '')) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Sicherheitsprüfung fehlgeschlagen. Bitte versuchen Sie es erneut.'
            ]);
            exit;
        }
    } elseif (ENVIRONMENT === 'development') {
        // Im Development-Modus nur warnen
        if (!validateRecaptcha($postData['recaptcha_token'] ?? '')) {
            error_log("WARNUNG: reCAPTCHA-Validierung fehlgeschlagen (wird im Dev-Modus ignoriert)");
        }
    }
    
    // Daten bereinigen
    $formData = [
        'name' => sanitizeInput($postData['name'] ?? '', MAX_NAME_LENGTH),
        'email' => sanitizeInput($postData['email'] ?? '', MAX_EMAIL_LENGTH),
        'phone' => sanitizeInput($postData['phone'] ?? '', MAX_PHONE_LENGTH),
        'contactType' => sanitizeInput($postData['contactType'] ?? ''),
        'message' => sanitizeInput($postData['message'] ?? '', MAX_MESSAGE_LENGTH),
        'website' => sanitizeInput($postData['website'] ?? ''), // Honeypot
        'privacy' => isset($postData['privacy']),
        'timestamp' => date('d.m.Y H:i:s')
    ];
    
    // Validieren
    $validationErrors = validateFormData($formData);
    if (!empty($validationErrors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => implode(', ', $validationErrors)
        ]);
        exit;
    }
    
    // Kontakttyp-Labels
    $contactTypes = [
        'general' => 'Allgemeine Anfrage',
        'consultation' => 'Beratungswunsch',
        'application' => 'Bewerbung',
        'other' => 'Sonstiges'
    ];
    $formData['contactTypeLabel'] = $contactTypes[$formData['contactType']] ?? $formData['contactType'];
    
    // Admin-Benachrichtigung senden
    $adminSubject = "Neue Kontaktanfrage: {$formData['contactTypeLabel']}";
    $adminHtml = loadEmailTemplate('admin-notification.html', $formData);
    $adminText = "Neue Kontaktanfrage von {$formData['name']}\n\n" .
                 "Kontaktart: {$formData['contactTypeLabel']}\n" .
                 "E-Mail: {$formData['email']}\n" .
                 "Telefon: {$formData['phone']}\n\n" .
                 "Nachricht:\n{$formData['message']}\n\n" .
                 "Gesendet am: {$formData['timestamp']}";
    
    $adminSent = sendEmail(ADMIN_EMAIL, 'Assistara Team', $adminSubject, $adminHtml, $adminText);
    
    if (!$adminSent) {
        throw new Exception('Admin-Email konnte nicht gesendet werden');
    }
    
    // Bestätigungs-Email an Absender senden
    $userSubject = "Bestätigung Ihrer Anfrage - Assistara";
    $userHtml = loadEmailTemplate('user-confirmation.html', $formData);
    $userText = "Vielen Dank für Ihre Anfrage, {$formData['name']}!\n\n" .
                "Wir haben Ihre Nachricht erhalten und werden uns so schnell wie möglich bei Ihnen melden.\n\n" .
                "Ihre Anfrage:\n" .
                "Kontaktart: {$formData['contactTypeLabel']}\n" .
                "Nachricht: {$formData['message']}\n\n" .
                "Mit freundlichen Grüßen\n" .
                "Ihr Assistara-Team\n\n" .
                "Assistara\nTelefon: +49 6221 67 91 450\nE-Mail: info@assistara.de\n" .
                "www.assistara.de";
    
    $userSent = sendEmail($formData['email'], $formData['name'], $userSubject, $userHtml, $userText);
    
    // Erfolgreiche Antwort
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank für Ihre Nachricht! Wir haben Ihnen eine Bestätigungsemail gesendet.'
    ]);
    
} catch (Exception $e) {
    logError('Fehler bei Formularverarbeitung: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut oder kontaktieren Sie uns telefonisch.'
    ]);
}
