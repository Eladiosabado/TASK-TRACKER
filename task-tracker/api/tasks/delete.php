<?php
/**
 * DELETE /api/tasks/delete.php?id=1
 *
 * Requires authentication. Only deletes a task that belongs to the
 * authenticated user.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'DELETE') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$taskId = $_GET['id'] ?? null;
if (!$taskId || !validate_int($taskId)) {
    error_response('A valid task id is required.', 400);
}
$taskId = (int) $taskId;

$pdo = get_db_connection();

$stmt = $pdo->prepare('SELECT id FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
$stmt->execute(['id' => $taskId, 'user_id' => $userId]);

if (!$stmt->fetch()) {
    error_response('Task not found.', 404);
}

$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $taskId, 'user_id' => $userId]);

success_response(null, 'Task deleted successfully.');
