<?php
/**
 * POST /api/auth/login.php
 *
 * Body: { "email": "...", "password": "..." }
 *
 * Verifies credentials and returns a signed JWT on success.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    error_response('Method not allowed.', 405);
}

$input = get_json_input();

$email    = sanitize_string($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

$errors = [];

if (!validate_required($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!validate_email($email)) {
    $errors['email'] = 'A valid email address is required.';
}

if (!validate_required($password)) {
    $errors['password'] = 'Password is required.';
}

if (!empty($errors)) {
    error_response(MSG_VALIDATION_ERROR, 422, $errors);
}

$pdo = get_db_connection();

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    error_response('Invalid email or password.', 401);
}

$token = Jwt::encode([
    'user_id' => (int) $user['id'],
    'email'   => $user['email'],
]);

success_response([
    'token' => $token,
    'user'  => [
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
    ],
], 'Login successful.');
