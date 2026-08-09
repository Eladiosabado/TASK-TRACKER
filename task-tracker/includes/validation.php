<?php
/**
 * includes/validation.php
 *
 * Reusable validation helper functions for API input.
 */

require_once __DIR__ . '/../config/constants.php';

if (!function_exists('validate_required')) {
    function validate_required($value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return $value !== null && $value !== '';
    }
}

if (!function_exists('validate_email')) {
    function validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validate_max_length')) {
    function validate_max_length(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }
}

if (!function_exists('validate_min_length')) {
    function validate_min_length(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }
}

if (!function_exists('validate_task_status')) {
    function validate_task_status(string $status): bool
    {
        return in_array($status, ALLOWED_TASK_STATUSES, true);
    }
}

if (!function_exists('validate_date')) {
    function validate_date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('validate_int')) {
    function validate_int($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}

if (!function_exists('get_json_input')) {
    /**
     * Reads and decodes the raw JSON request body.
     * Returns an empty array if the body is missing or invalid.
     */
    function get_json_input(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}
