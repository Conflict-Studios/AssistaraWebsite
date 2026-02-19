<?php
/**
 * Router für PHP Built-in Server
 * Simuliert .htaccess Clean URLs für lokale Entwicklung
 * 
 * Verwendung: php -S localhost:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Entferne trailing slashes (außer bei root /)
if ($uri !== '/' && substr($uri, -1) === '/') {
    $newUri = rtrim($uri, '/');
    header('Location: ' . $newUri, true, 301);
    exit;
}

// Statische Dateien direkt ausliefern
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Mapping für Clean URLs (case-insensitive)
$routes = [
    '/kontakt' => '/kontakt.html',
    '/leistungen' => '/leistungen.html',
    '/ueber-uns' => '/ueber-uns.html',
    '/impressum' => '/impressum.html',
    '/datenschutz' => '/datenschutz.html',
    
    // Verschachtelte Leistungen-URLs
    '/leistungen/persoenliche' => '/persoenliche.html',
    '/leistungen/schul-individual-begleitung' => '/schul-individual-begleitung.html',
    '/leistungen/personliches-budget' => '/personliches-budget.html',
];

// Redirects für alte URLs
$redirects = [
    '/persoenliche' => '/Leistungen/Persoenliche',
    '/schul-individual-begleitung' => '/Leistungen/Schul-Individual-Begleitung',
    '/personliches-budget' => '/Leistungen/Personliches-Budget',
];

// Case-insensitive Matching
$lowerUri = strtolower($uri);

// Prüfen ob Redirect benötigt wird
if (isset($redirects[$lowerUri])) {
    header('Location: ' . $redirects[$lowerUri], true, 301);
    exit;
}

// Root zur index.html
if ($uri === '/' || $uri === '') {
    $uri = '/index.html';
}
// Clean URL zu .html
elseif (isset($routes[$lowerUri])) {
    $uri = $routes[$lowerUri];
}
// Falls .html direkt aufgerufen wird, auch erlauben
elseif (!preg_match('/\.html$/', $uri)) {
    // Prüfen ob .html Datei existiert
    $htmlFile = __DIR__ . $uri . '.html';
    if (file_exists($htmlFile)) {
        $uri = $uri . '.html';
    }
}

// Datei ausliefern
$filePath = __DIR__ . $uri;

if (file_exists($filePath) && !is_dir($filePath)) {
    // MIME-Type setzen
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'php' => 'text/html',
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    // PHP-Dateien ausführen, andere inkludieren
    if ($ext === 'php') {
        include $filePath;
    } else {
        readfile($filePath);
    }
    return true;
}

// 404 - zeige 404.html
header("HTTP/1.0 404 Not Found");
header('Content-Type: text/html; charset=UTF-8');
include __DIR__ . '/404.html';
