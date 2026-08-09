<?php
$pageTitle = 'Register · Task Tracker';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark"></span>
            <span>Task Tracker</span>
        </div>

        <h1>Create your account</h1>
        <p class="auth-subtitle">Start tracking your tasks in minutes.</p>

        <form id="register-form" novalidate>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" autocomplete="name" required>
                <span class="field-error" id="name-error"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" required>
                <span class="field-error" id="email-error"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required>
                <span class="field-error" id="password-error"></span>
            </div>

            <button type="submit" id="register-submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo h(FRONTEND_URL); ?>/pages/login.php">Log in</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Auth !== 'undefined') Auth.redirectIfAuthenticated();
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
