<?php
/**
 * GET /api/tasks/stats.php
 *
 * Requires authentication. Returns total/pending/in_progress/completed
 * counts for the authenticated user's tasks only.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$pdo = get_db_connection();

$stmt = $pdo->prepare('
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed
    FROM tasks
    WHERE user_id = :user_id
');
$stmt->execute(['user_id' => $userId]);
$row = $stmt->fetch();

success_response([
    'total'       => (int) ($row['total'] ?? 0),
    'pending'     => (int) ($row['pending'] ?? 0),
    'in_progress' => (int) ($row['in_progress'] ?? 0),
    'completed'   => (int) ($row['completed'] ?? 0),
], 'Statistics retrieved successfully.');
