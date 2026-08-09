<?php
/**
 * POST /api/auth/logout.php
 *
 * JWTs are stateless, so there is no server-side session to destroy.
 * This endpoint simply confirms the request and instructs the client
 * to discard its stored token. The actual "logout" behavior happens
 * on the frontend (see assets/js/auth.js), which removes the JWT
 * from storage.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    error_response('Method not allowed.', 405);
}

// Confirms the token was valid at the time of logout (optional check).
require_auth();

success_response(null, 'Logged out successfully. Please remove the token on the client.');
