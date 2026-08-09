<?php
/**
 * index.php
 *
 * Root entry point. Since authentication state lives in the
 * browser's localStorage (JWT), the redirect decision is made
 * client-side: authenticated users go to the dashboard, everyone
 * else goes to the login page.
 */
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tracker</title>
    <meta http-equiv="refresh" content="0; url=<?php echo htmlspecialchars(FRONTEND_URL, ENT_QUOTES); ?>/pages/login.php">
</head>
<body>
    <script>
        const token = localStorage.getItem('task_tracker_token');
        const base = window.location.origin + '/task-tracker';
        window.location.href = token ? `${base}/pages/dashboard.php` : `${base}/pages/login.php`;
    </script>
    <noscript>
        <p>JavaScript is required. <a href="<?php echo htmlspecialchars(FRONTEND_URL, ENT_QUOTES); ?>/pages/login.php">Continue to login</a>.</p>
    </noscript>
</body>
</html>
