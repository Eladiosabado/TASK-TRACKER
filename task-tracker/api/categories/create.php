<?php
/**
 * POST /api/categories/create.php
 *
 * Body: { "name": "School" }
 *
 * Requires authentication. Category names are globally unique.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    error_response('Method not allowed.', 405);
}

require_auth();

$input = get_json_input();
$name  = sanitize_string($input['name'] ?? '');

$errors = [];

if (!validate_required($name)) {
    $errors['name'] = 'Category name is required.';
} elseif (!validate_max_length($name, MAX_CATEGORY_NAME_LENGTH)) {
    $errors['name'] = 'Category name must be at most ' . MAX_CATEGORY_NAME_LENGTH . ' characters.';
}

if (!empty($errors)) {
    error_response(MSG_VALIDATION_ERROR, 422, $errors);
}

$pdo = get_db_connection();

$stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
$stmt->execute(['name' => $name]);

if ($stmt->fetch()) {
    error_response('A category with this name already exists.', 409);
}

$stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
$stmt->execute(['name' => $name]);

$categoryId = (int) $pdo->lastInsertId();

success_response([
    'id'   => $categoryId,
    'name' => $name,
], 'Category created successfully.', 201);
