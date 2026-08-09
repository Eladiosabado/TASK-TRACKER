<?php
/**
 * PUT /api/categories/update.php?id=1
 *
 * Body: { "name": "New Name" }
 *
 * Requires authentication.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'PUT') {
    error_response('Method not allowed.', 405);
}

require_auth();

$categoryId = $_GET['id'] ?? null;

if (!$categoryId || !validate_int($categoryId)) {
    error_response('A valid category id is required.', 400);
}
$categoryId = (int) $categoryId;

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

// Ensure the category exists
$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $categoryId]);
if (!$stmt->fetch()) {
    error_response('Category not found.', 404);
}

// Ensure the new name is not taken by a different category
$stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name AND id != :id LIMIT 1');
$stmt->execute(['name' => $name, 'id' => $categoryId]);
if ($stmt->fetch()) {
    error_response('A category with this name already exists.', 409);
}

$stmt = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :id');
$stmt->execute(['name' => $name, 'id' => $categoryId]);

success_response([
    'id'   => $categoryId,
    'name' => $name,
], 'Category updated successfully.');
