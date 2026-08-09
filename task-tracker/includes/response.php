<?php
/**
 * includes/response.php
 *
 * Standardized JSON response helpers used by every API endpoint.
 */

if (!function_exists('json_response')) {
    /**
     * Send a raw JSON response and terminate the script.
     */
    function json_response(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('success_response')) {
    function success_response($data = null, string $message = 'Operation successful', int $statusCode = 200): void
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        json_response($payload, $statusCode);
    }
}

if (!function_exists('error_response')) {
    function error_response(string $message = 'Something went wrong', int $statusCode = 400, $errors = null): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        json_response($payload, $statusCode);
    }
}
