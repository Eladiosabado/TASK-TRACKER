<?php
/**
 * POST /api/auth/register.php
 *
 * Body: { "name": "...", "email": "...", "password": "..." }
 *
 * Creates a new user account. Passwords are hashed with
 * password_hash() and never stored in plaintext.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    error_response('Method not allowed.', 405);
}

$input = get_json_input();

$name     = sanitize_string($input['name'] ?? '');
$email    = sanitize_string($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

$errors = [];

if (!validate_required($name)) {
    $errors['name'] = 'Name is required.';
} elseif (!validate_max_length($name, MAX_NAME_LENGTH)) {
    $errors['name'] = 'Name must be at most ' . MAX_NAME_LENGTH . ' characters.';
}

if (!validate_required($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!validate_email($email)) {
    $errors['email'] = 'A valid email address is required.';
} elseif (!validate_max_length($email, MAX_EMAIL_LENGTH)) {
    $errors['email'] = 'Email must be at most ' . MAX_EMAIL_LENGTH . ' characters.';
}

if (!validate_required($password)) {
    $errors['password'] = 'Password is required.';
} elseif (!validate_min_length($password, MIN_PASSWORD_LENGTH)) {
    $errors['password'] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
}

if (!empty($errors)) {
    error_response(MSG_VALIDATION_ERROR, 422, $errors);
}

$pdo = get_db_connection();

// Check duplicate email
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    error_response('An account with this email already exists.', 409);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
);
$stmt->execute([
    'name'     => $name,
    'email'    => $email,
    'password' => $hashedPassword,
]);

$userId = (int) $pdo->lastInsertId();

success_response([
    'id'    => $userId,
    'name'  => $name,
    'email' => $email,
], 'Account created successfully.', 201);
