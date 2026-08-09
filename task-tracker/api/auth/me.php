<?php
/**
 * GET /api/auth/me.php
 *
 * Requires a valid JWT. Returns the authenticated user's profile.
 * The password hash is never returned.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT id, name, email, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    error_response('User not found.', 404);
}

success_response([
    'id'         => (int) $user['id'],
    'name'       => $user['name'],
    'email'      => $user['email'],
    'created_at' => $user['created_at'],
    'updated_at' => $user['updated_at'],
], 'User retrieved successfully.');
