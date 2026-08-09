/**
 * assets/js/auth.js
 *
 * Handles login, registration, logout and route guarding.
 */

const Auth = (() => {
    function isAuthenticated() {
        return Boolean(Api.getToken());
    }

    /**
     * Redirect to login if not authenticated. Call at the top of
     * protected pages (e.g. dashboard.php).
     */
    function requireAuth() {
        if (!isAuthenticated()) {
            window.location.href = `${window.location.origin}/task-tracker/pages/login.php`;
        }
    }

    /**
     * Redirect to dashboard if already authenticated. Call on
     * login/register pages so logged-in users skip straight through.
     */
    function redirectIfAuthenticated() {
        if (isAuthenticated()) {
            window.location.href = `${window.location.origin}/task-tracker/pages/dashboard.php`;
        }
    }

    async function login(email, password) {
        const response = await Api.post('/auth/login.php', { email, password });
        Api.setToken(response.data.token);
        Api.setUser(response.data.user);
        return response.data;
    }

    async function register(name, email, password) {
        const response = await Api.post('/auth/register.php', { name, email, password });
        return response.data;
    }

    async function logout() {
        try {
            await Api.post('/auth/logout.php', {});
        } catch (e) {
            // Even if the API call fails, we still clear the local token.
        }
        Api.clearToken();
        window.location.href = `${window.location.origin}/task-tracker/pages/login.php`;
    }

    function bindLoginForm() {
        const form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.clearFormErrors(form);

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('login-submit');

            let hasError = false;
            if (!email) {
                Utils.setFieldError('email', 'Email is required.');
                hasError = true;
            }
            if (!password) {
                Utils.setFieldError('password', 'Password is required.');
                hasError = true;
            }
            if (hasError) return;

            Utils.setButtonLoading(submitBtn, true, 'Logging in...');

            try {
                await login(email, password);
                Utils.showToast('Login successful. Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = `${window.location.origin}/task-tracker/pages/dashboard.php`;
                }, 500);
            } catch (err) {
                if (err.errors) {
                    Utils.applyApiErrors(form, err.errors);
                } else {
                    Utils.showToast(err.message || 'Login failed.', 'error');
                }
            } finally {
                Utils.setButtonLoading(submitBtn, false);
            }
        });
    }

    function bindRegisterForm() {
        const form = document.getElementById('register-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.clearFormErrors(form);

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('register-submit');

            let hasError = false;
            if (!name) {
                Utils.setFieldError('name', 'Name is required.');
                hasError = true;
            }
            if (!email) {
                Utils.setFieldError('email', 'Email is required.');
                hasError = true;
            }
            if (!password || password.length < 8) {
                Utils.setFieldError('password', 'Password must be at least 8 characters.');
                hasError = true;
            }
            if (hasError) return;

            Utils.setButtonLoading(submitBtn, true, 'Creating account...');

            try {
                await register(name, email, password);
                Utils.showToast('Account created! Please log in.', 'success');
                setTimeout(() => {
                    window.location.href = `${window.location.origin}/task-tracker/pages/login.php`;
                }, 700);
            } catch (err) {
                if (err.errors) {
                    Utils.applyApiErrors(form, err.errors);
                } else {
                    Utils.showToast(err.message || 'Registration failed.', 'error');
                }
            } finally {
                Utils.setButtonLoading(submitBtn, false);
            }
        });
    }

    function bindLogoutButton() {
        const btn = document.getElementById('logout-btn');
        if (!btn) return;
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindLoginForm();
        bindRegisterForm();
        bindLogoutButton();
    });

    return {
        isAuthenticated,
        requireAuth,
        redirectIfAuthenticated,
        login,
        register,
        logout,
    };
})();
