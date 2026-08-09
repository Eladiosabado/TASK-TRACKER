<?php
/**
 * PUT /api/tasks/update.php?id=1
 *
 * Body may include any of: title, description, status, due_date, category_id
 *
 * Requires authentication. Ownership is verified before any update
 * is applied — a user can never modify another user's task.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'PUT') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$taskId = $_GET['id'] ?? null;
if (!$taskId || !validate_int($taskId)) {
    error_response('A valid task id is required.', 400);
}
$taskId = (int) $taskId;

$pdo = get_db_connection();

// Verify the task exists AND belongs to the authenticated user
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
$stmt->execute(['id' => $taskId, 'user_id' => $userId]);
$existing = $stmt->fetch();

if (!$existing) {
    error_response('Task not found.', 404);
}

$input = get_json_input();

// Only update fields that were actually provided; fall back to existing values.
$title       = array_key_exists('title', $input) ? sanitize_string($input['title']) : $existing['title'];
$description = array_key_exists('description', $input) ? trim((string) $input['description']) : $existing['description'];
$status      = array_key_exists('status', $input) ? sanitize_string($input['status']) : $existing['status'];
$dueDate     = array_key_exists('due_date', $input) ? sanitize_string((string) $input['due_date']) : $existing['due_date'];
$categoryId  = array_key_exists('category_id', $input) ? $input['category_id'] : $existing['category_id'];

$errors = [];

if (!validate_required($title)) {
    $errors['title'] = 'Title is required.';
} elseif (!validate_max_length($title, MAX_TASK_TITLE_LENGTH)) {
    $errors['title'] = 'Title must be at most ' . MAX_TASK_TITLE_LENGTH . ' characters.';
}

if (!validate_task_status($status)) {
    $errors['status'] = 'Status must be one of: ' . implode(', ', ALLOWED_TASK_STATUSES);
}

if ($dueDate !== null && $dueDate !== '' && !validate_date($dueDate)) {
    $errors['due_date'] = 'Due date must be a valid date in YYYY-MM-DD format.';
}

if (!validate_required($categoryId) || !validate_int($categoryId)) {
    $errors['category_id'] = 'A valid category is required.';
}

if (!empty($errors)) {
    error_response(MSG_VALIDATION_ERROR, 422, $errors);
}

$categoryId = (int) $categoryId;

$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $categoryId]);
if (!$stmt->fetch()) {
    error_response(MSG_VALIDATION_ERROR, 422, ['category_id' => 'Selected category does not exist.']);
}

$stmt = $pdo->prepare('
    UPDATE tasks
    SET title = :title,
        description = :description,
        status = :status,
        due_date = :due_date,
        category_id = :category_id
    WHERE id = :id AND user_id = :user_id
');
$stmt->execute([
    'title'       => $title,
    'description' => ($description !== '' && $description !== null) ? $description : null,
    'status'      => $status,
    'due_date'    => ($dueDate !== '' && $dueDate !== null) ? $dueDate : null,
    'category_id' => $categoryId,
    'id'          => $taskId,
    'user_id'     => $userId,
]);

success_response([
    'id'          => $taskId,
    'title'       => $title,
    'description' => ($description !== '' && $description !== null) ? $description : null,
    'status'      => $status,
    'due_date'    => ($dueDate !== '' && $dueDate !== null) ? $dueDate : null,
    'category_id' => $categoryId,
    'user_id'     => $userId,
], 'Task updated successfully.');
