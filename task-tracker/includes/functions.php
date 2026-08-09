<?php
/**
 * includes/functions.php
 *
 * General-purpose helper functions shared across the app.
 */

if (!function_exists('sanitize_string')) {
    function sanitize_string(?string $value): string
    {
        return trim((string) $value);
    }
}

if (!function_exists('h')) {
    /**
     * HTML-escape a value for safe output in .php templates.
     */
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_bearer_token')) {
    /**
     * Extract the Bearer token from the Authorization header.
     * Handles common server configurations where the header may
     * arrive via getallheaders() or $_SERVER.
     */
    function get_bearer_token(): ?string
    {
        $authHeader = null;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $authHeader = $value;
                    break;
                }
            }
        }

        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

if (!function_exists('current_page_int')) {
    function current_page_int($value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT);
        if ($page === false || $page < 1) {
            return DEFAULT_PAGE;
        }
        return $page;
    }
}

if (!function_exists('current_limit_int')) {
    function current_limit_int($value): int
    {
        $limit = filter_var($value, FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1) {
            return DEFAULT_LIMIT;
        }
        return min($limit, MAX_LIMIT);
    }
}
