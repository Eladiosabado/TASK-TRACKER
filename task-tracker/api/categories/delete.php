<?php
/**
 * DELETE /api/categories/delete.php?id=1
 *
 * Requires authentication. Because tasks.category_id has an
 * ON DELETE RESTRICT foreign key, categories that are still
 * assigned to at least one task cannot be deleted. We check for
 * this proactively and return a friendly 409 Conflict instead of
 * letting the database throw a raw FK violation.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'DELETE') {
    error_response('Method not allowed.', 405);
}

require_auth();

$categoryId = $_GET['id'] ?? null;

if (!$categoryId || !validate_int($categoryId)) {
    error_response('A valid category id is required.', 400);
}
$categoryId = (int) $categoryId;

$pdo = get_db_connection();

$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $categoryId]);
if (!$stmt->fetch()) {
    error_response('Category not found.', 404);
}

// Check whether any task still references this category
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM tasks WHERE category_id = :id');
$stmt->execute(['id' => $categoryId]);
$taskCount = (int) $stmt->fetch()['total'];

if ($taskCount > 0) {
    error_response(
        "This category is assigned to {$taskCount} task(s) and cannot be deleted. Reassign or delete those tasks first.",
        409
    );
}

$stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
$stmt->execute(['id' => $categoryId]);

success_response(null, 'Category deleted successfully.');
