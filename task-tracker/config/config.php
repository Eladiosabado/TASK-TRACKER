<?php
/**
 * config/config.php
 *
 * Loads core application configuration from environment variables.
 */

require_once __DIR__ . '/env.php';

if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost/task-tracker');
}

if (!defined('FRONTEND_URL')) {
    define('FRONTEND_URL', getenv('FRONTEND_URL') ?: 'http://localhost/task-tracker');
}

if (!defined('JWT_SECRET')) {
    $secret = getenv('JWT_SECRET');
    if (!$secret) {
        // Fail loudly in a controlled way rather than using a guessable default.
        $secret = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';
    }
    define('JWT_SECRET', $secret);
}

// Basic error display settings.
// In production, set display_errors to 0 and rely on the log file.
$appEnv = getenv('APP_ENV') ?: 'development';

if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Route PHP errors to our own log file instead of the public webroot.
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

date_default_timezone_set('UTC');
