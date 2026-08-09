<?php
http_response_code(404);
$pageTitle = 'Page Not Found · Task Tracker';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
    <div class="auth-card" style="text-align:center;">
        <div class="auth-brand" style="justify-content:center;">
            <span class="brand-mark"></span>
            <span>Task Tracker</span>
        </div>
        <h1>404</h1>
        <p class="auth-subtitle">The page you're looking for doesn't exist.</p>
        <a class="btn btn-primary" href="<?php echo h(FRONTEND_URL); ?>/pages/login.php">Back to Login</a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
