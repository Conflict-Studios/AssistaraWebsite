# PHPMailer Installation

## Option 1: Manuelle Installation (Empfohlen für Netcup)

1. **PHPMailer herunterladen:**
   - Besuchen Sie: https://github.com/PHPMailer/PHPMailer/releases
   - Laden Sie die neueste Version herunter (z.B. PHPMailer-6.x.zip)

2. **Dateien extrahieren:**
   Extrahieren Sie die ZIP-Datei und kopieren Sie diese Dateien in den Ordner `api/vendor/PHPMailer/`:
   - PHPMailer.php
   - SMTP.php
   - Exception.php
   - POP3.php (optional)
   - OAuth.php (optional)

3. **Verzeichnisstruktur:**
   ```
   api/
   ├── vendor/
   │   └── PHPMailer/
   │       ├── PHPMailer.php
   │       ├── SMTP.php
   │       └── Exception.php
   ├── contact-handler.php
   └── config.php
   ```

## Option 2: Via Composer (Falls auf dem Server verfügbar)

Führen Sie im `api/` Verzeichnis aus:
```bash
composer require phpmailer/phpmailer
```

Dann ändern Sie in `contact-handler.php` die require-Pfade zu:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Option 3: Alternative ohne PHPMailer

Falls Sie PHPMailer nicht installieren möchten, können Sie die PHP `mail()` Funktion verwenden:

Ersetzen Sie die `sendEmail()` Funktion in `contact-handler.php` mit:

```php
function sendEmail($to, $toName, $subject, $htmlBody, $textBody) {
    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $htmlBody, $headers);
}
```

**Hinweis:** Die `mail()` Funktion ist weniger zuverlässig und bietet weniger Funktionen als PHPMailer.

## Netcup SMTP-Konfiguration

Nach der Installation konfigurieren Sie in `api/config.php`:

```php
define('SMTP_HOST', 'smtp.netcup.net');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'info@assistara.de');
define('SMTP_PASSWORD', 'IHR_EMAIL_PASSWORT');
```

**SMTP-Zugangsdaten finden Sie im Netcup Customer Control Panel (CCP) unter "E-Mail" → "E-Mail-Konten".**
