<?php
/**
 * GET /api/categories/index.php
 *
 * Requires authentication. Returns all categories (global, not
 * scoped per-user, since categories are shared across the app).
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    error_response('Method not allowed.', 405);
}

require_auth();

$pdo = get_db_connection();
$stmt = $pdo->query('SELECT id, name, created_at, updated_at FROM categories ORDER BY name ASC');
$categories = $stmt->fetchAll();

$categories = array_map(function ($row) {
    return [
        'id'         => (int) $row['id'],
        'name'       => $row['name'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
    ];
}, $categories);

success_response($categories, 'Categories retrieved successfully.');
