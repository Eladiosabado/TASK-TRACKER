<?php
/**
 * POST /api/tasks/create.php
 *
 * Body:
 * {
 *   "title": "Finish project",
 *   "description": "Complete project",
 *   "status": "pending",
 *   "due_date": "2026-08-20",
 *   "category_id": 1
 * }
 *
 * Requires authentication. user_id is ALWAYS taken from the
 * validated JWT — the frontend cannot set it.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$input = get_json_input();

$title       = sanitize_string($input['title'] ?? '');
$description = array_key_exists('description', $input) ? trim((string) $input['description']) : null;
$status      = sanitize_string($input['status'] ?? 'pending');
$dueDate     = array_key_exists('due_date', $input) ? sanitize_string((string) $input['due_date']) : '';
$categoryId  = $input['category_id'] ?? null;

$errors = [];

if (!validate_required($title)) {
    $errors['title'] = 'Title is required.';
} elseif (!validate_max_length($title, MAX_TASK_TITLE_LENGTH)) {
    $errors['title'] = 'Title must be at most ' . MAX_TASK_TITLE_LENGTH . ' characters.';
}

if (!validate_task_status($status)) {
    $errors['status'] = 'Status must be one of: ' . implode(', ', ALLOWED_TASK_STATUSES);
}

if ($dueDate !== '' && !validate_date($dueDate)) {
    $errors['due_date'] = 'Due date must be a valid date in YYYY-MM-DD format.';
}

if (!validate_required($categoryId) || !validate_int($categoryId)) {
    $errors['category_id'] = 'A valid category is required.';
}

if (!empty($errors)) {
    error_response(MSG_VALIDATION_ERROR, 422, $errors);
}

$categoryId = (int) $categoryId;

$pdo = get_db_connection();

// Ensure the category actually exists
$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $categoryId]);
if (!$stmt->fetch()) {
    error_response(MSG_VALIDATION_ERROR, 422, ['category_id' => 'Selected category does not exist.']);
}

$stmt = $pdo->prepare('
    INSERT INTO tasks (title, description, status, due_date, category_id, user_id)
    VALUES (:title, :description, :status, :due_date, :category_id, :user_id)
');
$stmt->execute([
    'title'       => $title,
    'description' => $description !== '' ? $description : null,
    'status'      => $status,
    'due_date'    => $dueDate !== '' ? $dueDate : null,
    'category_id' => $categoryId,
    'user_id'     => $userId, // Always the authenticated user, never from the client
]);

$taskId = (int) $pdo->lastInsertId();

success_response([
    'id'          => $taskId,
    'title'       => $title,
    'description' => $description !== '' ? $description : null,
    'status'      => $status,
    'due_date'    => $dueDate !== '' ? $dueDate : null,
    'category_id' => $categoryId,
    'user_id'     => $userId,
], 'Task created successfully.', 201);
