<?php
/**
 * GET /api/tasks/show.php?id=1
 *
 * Requires authentication. The task must belong to the
 * authenticated user; another user's task must never be returned.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$taskId = $_GET['id'] ?? null;

if (!$taskId || !validate_int($taskId)) {
    error_response('A valid task id is required.', 400);
}
$taskId = (int) $taskId;

$pdo = get_db_connection();

$stmt = $pdo->prepare('
    SELECT
        t.id, t.title, t.description, t.status, t.due_date,
        t.category_id, t.user_id, t.created_at, t.updated_at,
        c.name AS category_name
    FROM tasks t
    INNER JOIN categories c ON c.id = t.category_id
    WHERE t.id = :id AND t.user_id = :user_id
    LIMIT 1
');
$stmt->execute(['id' => $taskId, 'user_id' => $userId]);
$task = $stmt->fetch();

if (!$task) {
    // Returning 404 (rather than 403) avoids leaking whether the
    // task id belongs to someone else.
    error_response('Task not found.', 404);
}

success_response([
    'id'            => (int) $task['id'],
    'title'         => $task['title'],
    'description'   => $task['description'],
    'status'        => $task['status'],
    'due_date'      => $task['due_date'],
    'category_id'   => (int) $task['category_id'],
    'category_name' => $task['category_name'],
    'user_id'       => (int) $task['user_id'],
    'created_at'    => $task['created_at'],
    'updated_at'    => $task['updated_at'],
], 'Task retrieved successfully.');
