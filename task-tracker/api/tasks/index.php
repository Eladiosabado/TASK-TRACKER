<?php
/**
 * GET /api/tasks/index.php
 *
 * Query params:
 *   status=pending|in_progress|completed
 *   category_id=<int>
 *   search=<string>   (matches task title with LIKE)
 *   page=<int>
 *   limit=<int>
 *
 * Requires authentication. Only returns tasks belonging to the
 * authenticated user — every query enforces WHERE user_id = :user_id.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../middleware/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    error_response('Method not allowed.', 405);
}

$userId = require_auth();

$status     = isset($_GET['status']) ? sanitize_string($_GET['status']) : '';
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$search     = isset($_GET['search']) ? sanitize_string($_GET['search']) : '';
$page       = current_page_int($_GET['page'] ?? DEFAULT_PAGE);
$limit      = current_limit_int($_GET['limit'] ?? DEFAULT_LIMIT);

if ($status !== '' && !validate_task_status($status)) {
    error_response('Invalid status filter.', 422, ['status' => 'Must be one of: ' . implode(', ', ALLOWED_TASK_STATUSES)]);
}

if ($categoryId !== '' && !validate_int($categoryId)) {
    error_response('Invalid category filter.', 422, ['category_id' => 'Must be a valid integer.']);
}

$pdo = get_db_connection();

$where  = ['t.user_id = :user_id'];
$params = ['user_id' => $userId];

if ($status !== '') {
    $where[] = 't.status = :status';
    $params['status'] = $status;
}

if ($categoryId !== '') {
    $where[] = 't.category_id = :category_id';
    $params['category_id'] = (int) $categoryId;
}

if ($search !== '') {
    $where[] = 't.title LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$whereSql = implode(' AND ', $where);

// --- Total count for pagination ---
$countSql  = "SELECT COUNT(*) AS total FROM tasks t WHERE {$whereSql}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int) $countStmt->fetch()['total'];

$totalPages = $total > 0 ? (int) ceil($total / $limit) : 0;
$offset     = ($page - 1) * $limit;

// --- Data query ---
$dataSql = "
    SELECT
        t.id, t.title, t.description, t.status, t.due_date,
        t.category_id, t.user_id, t.created_at, t.updated_at,
        c.name AS category_name
    FROM tasks t
    INNER JOIN categories c ON c.id = t.category_id
    WHERE {$whereSql}
    ORDER BY t.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($dataSql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$tasks = array_map(function ($row) {
    return [
        'id'            => (int) $row['id'],
        'title'         => $row['title'],
        'description'   => $row['description'],
        'status'        => $row['status'],
        'due_date'      => $row['due_date'],
        'category_id'   => (int) $row['category_id'],
        'category_name' => $row['category_name'],
        'user_id'       => (int) $row['user_id'],
        'created_at'    => $row['created_at'],
        'updated_at'    => $row['updated_at'],
    ];
}, $rows);

json_response([
    'success' => true,
    'data'    => $tasks,
    'pagination' => [
        'page'        => $page,
        'limit'       => $limit,
        'total'       => $total,
        'total_pages' => $totalPages,
    ],
], 200);
