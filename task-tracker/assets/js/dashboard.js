/**
 * assets/js/dashboard.js
 *
 * Responsible for:
 *   - Loading authenticated user info
 *   - Loading and rendering statistics cards
 *   - Bootstrapping the dashboard page
 */

const Dashboard = (() => {
    async function loadUserInfo() {
        const nameEl = document.getElementById('current-user-name');
        try {
            const response = await Api.get('/auth/me.php');
            const user = response.data;
            Api.setUser(user);
            if (nameEl) nameEl.textContent = user.name;
        } catch (err) {
            // requireAuth's 401 handling in api.js will redirect automatically.
            if (nameEl) nameEl.textContent = '';
        }
    }

    async function loadStats() {
        const totalEl = document.getElementById('stat-total');
        const pendingEl = document.getElementById('stat-pending');
        const inProgressEl = document.getElementById('stat-in-progress');
        const completedEl = document.getElementById('stat-completed');

        try {
            const response = await Api.get('/tasks/stats.php');
            const stats = response.data;
            if (totalEl) totalEl.textContent = stats.total;
            if (pendingEl) pendingEl.textContent = stats.pending;
            if (inProgressEl) inProgressEl.textContent = stats.in_progress;
            if (completedEl) completedEl.textContent = stats.completed;
        } catch (err) {
            Utils.showToast('Could not load statistics.', 'error');
        }
    }

    async function refreshAll() {
        await Promise.all([loadStats()]);
    }

    async function init() {
        Auth.requireAuth();
        await loadUserInfo();
        await loadStats();

        if (typeof Categories !== 'undefined') {
            await Categories.loadCategories();
        }
        if (typeof Tasks !== 'undefined') {
            await Tasks.init();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.body.dataset.page === 'dashboard') {
            init();
        }
    });

    return { init, loadStats, loadUserInfo, refreshAll };
})();
