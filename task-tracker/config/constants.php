<?php
/**
 * config/constants.php
 *
 * Reusable constants for the whole application.
 */

// --- JWT ---
if (!defined('JWT_ALGO')) {
    define('JWT_ALGO', 'HS256');
}
if (!defined('JWT_EXPIRATION_SECONDS')) {
    define('JWT_EXPIRATION_SECONDS', 60 * 60 * 24); // 24 hours
}

// --- Pagination ---
if (!defined('DEFAULT_PAGE')) {
    define('DEFAULT_PAGE', 1);
}
if (!defined('DEFAULT_LIMIT')) {
    define('DEFAULT_LIMIT', 10);
}
if (!defined('MAX_LIMIT')) {
    define('MAX_LIMIT', 100);
}

// --- Validation ---
if (!defined('MIN_PASSWORD_LENGTH')) {
    define('MIN_PASSWORD_LENGTH', 8);
}
if (!defined('MAX_NAME_LENGTH')) {
    define('MAX_NAME_LENGTH', 100);
}
if (!defined('MAX_EMAIL_LENGTH')) {
    define('MAX_EMAIL_LENGTH', 150);
}
if (!defined('MAX_CATEGORY_NAME_LENGTH')) {
    define('MAX_CATEGORY_NAME_LENGTH', 100);
}
if (!defined('MAX_TASK_TITLE_LENGTH')) {
    define('MAX_TASK_TITLE_LENGTH', 255);
}

// --- Task statuses (must match the ENUM in the tasks table exactly) ---
if (!defined('ALLOWED_TASK_STATUSES')) {
    define('ALLOWED_TASK_STATUSES', ['pending', 'in_progress', 'completed']);
}

// --- Standard response messages ---
if (!defined('MSG_UNAUTHORIZED')) {
    define('MSG_UNAUTHORIZED', 'Unauthorized. Please log in.');
}
if (!defined('MSG_NOT_FOUND')) {
    define('MSG_NOT_FOUND', 'Resource not found.');
}
if (!defined('MSG_VALIDATION_ERROR')) {
    define('MSG_VALIDATION_ERROR', 'Validation failed.');
}
if (!defined('MSG_SERVER_ERROR')) {
    define('MSG_SERVER_ERROR', 'Something went wrong. Please try again later.');
}
