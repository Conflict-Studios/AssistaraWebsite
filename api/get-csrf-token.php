<?php
/**
 * CSRF Token Generator
 * Generiert einen Token für Formularsicherheit
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

// Token generieren wenn noch nicht vorhanden
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'csrf_token' => $_SESSION['csrf_token']
]);
