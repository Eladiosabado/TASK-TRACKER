<?php
/**
 * config/database.php
 *
 * Creates and returns a PDO connection to MySQL using environment
 * configuration values. Uses prepared-statement-friendly settings.
 */

require_once __DIR__ . '/env.php';

if (!function_exists('get_db_connection')) {
    function get_db_connection(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $host     = getenv('DB_HOST') ?: 'localhost';
        $port     = getenv('DB_PORT') ?: '3306';
        $dbName   = getenv('DB_NAME') ?: 'task_tracker';
        $user     = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            // Never leak connection details to the client.
            error_log('Database connection failed: ' . $e->getMessage());

            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please try again later.',
            ]);
            exit;
        }

        return $pdo;
    }
}
