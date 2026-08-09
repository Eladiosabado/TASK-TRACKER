<?php
/**
 * middleware/auth.php
 *
 * Require this file at the top of any protected API endpoint.
 *
 * It will:
 *   1. Read the Authorization header
 *   2. Require the "Bearer TOKEN" format
 *   3. Validate the JWT signature and expiration
 *   4. Expose the authenticated user id via require_auth()
 *   5. Send HTTP 401 and stop execution if anything fails
 *
 * The frontend can NEVER inject its own user_id — endpoints must
 * always call require_auth() and use the returned user id.
 */

require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/jwt.php';

if (!function_exists('require_auth')) {
    /**
     * Validates the request's JWT and returns the authenticated
     * user's id. Halts the script with a 401 response on failure.
     */
    function require_auth(): int
    {
        $token = get_bearer_token();

        if (!$token) {
            error_response(MSG_UNAUTHORIZED, 401);
        }

        $payload = Jwt::decode($token);

        if (!$payload || !isset($payload['user_id'])) {
            error_response('Invalid or expired token.', 401);
        }

        $userId = filter_var($payload['user_id'], FILTER_VALIDATE_INT);

        if ($userId === false || $userId <= 0) {
            error_response('Invalid or expired token.', 401);
        }

        return (int) $userId;
    }
}

if (!function_exists('get_authenticated_payload')) {
    /**
     * Same as require_auth() but returns the full decoded payload
     * (useful for endpoints like /auth/me.php that also need email).
     */
    function get_authenticated_payload(): array
    {
        $token = get_bearer_token();

        if (!$token) {
            error_response(MSG_UNAUTHORIZED, 401);
        }

        $payload = Jwt::decode($token);

        if (!$payload || !isset($payload['user_id'])) {
            error_response('Invalid or expired token.', 401);
        }

        return $payload;
    }
}
