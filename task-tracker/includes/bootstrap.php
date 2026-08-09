<?php
/**
 * includes/bootstrap.php
 *
 * Included at the top of every API endpoint. Loads configuration,
 * sets common headers, and converts uncaught errors into JSON
 * responses instead of leaking HTML error pages.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/jwt.php';

// --- CORS (relevant if the frontend is ever served from a different origin) ---
$allowedOrigin = defined('FRONTEND_URL') ? FRONTEND_URL : '*';
header("Access-Control-Allow-Origin: {$allowedOrigin}");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Preflight requests never need to reach endpoint logic.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// --- Convert PHP errors/exceptions into clean JSON responses ---
set_exception_handler(function (Throwable $e): void {
    error_log('Uncaught exception: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => MSG_SERVER_ERROR,
    ]);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line): bool {
    // Respect @-suppressed errors
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
