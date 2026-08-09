<?php
$pageTitle = 'Log In · Task Tracker';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark"></span>
            <span>Task Tracker</span>
        </div>

        <h1>Welcome back</h1>
        <p class="auth-subtitle">Log in to manage your tasks.</p>

        <form id="login-form" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" required>
                <span class="field-error" id="email-error"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
                <span class="field-error" id="password-error"></span>
            </div>

            <button type="submit" id="login-submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?php echo h(FRONTEND_URL); ?>/pages/register.php">Register</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Auth !== 'undefined') Auth.redirectIfAuthenticated();
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
