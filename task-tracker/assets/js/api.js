/**
 * assets/js/api.js
 *
 * Central Fetch API wrapper:
 *   - Base URL configuration
 *   - Authorization header injection
 *   - JSON parsing
 *   - Consistent error handling
 */

const Api = (() => {
    // Adjust if your local folder name differs from "task-tracker".
    const BASE_URL = `${window.location.origin}/task-tracker/api`;

    const TOKEN_KEY = 'task_tracker_token';
    const USER_KEY = 'task_tracker_user';

    function getToken() {
        return localStorage.getItem(TOKEN_KEY);
    }

    function setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    }

    function clearToken() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    }

    function setUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    }

    function getUser() {
        const raw = localStorage.getItem(USER_KEY);
        return raw ? JSON.parse(raw) : null;
    }

    /**
     * Core request function.
     * @param {string} path e.g. "/tasks/index.php?page=1"
     * @param {object} options fetch options (method, body, etc.)
     */
    async function request(path, options = {}) {
        const url = `${BASE_URL}${path}`;

        const headers = {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        };

        const token = getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        let response;
        try {
            response = await fetch(url, {
                ...options,
                headers,
            });
        } catch (networkError) {
            throw { success: false, message: 'Network error. Please check your connection.', status: 0 };
        }

        let payload = null;
        try {
            payload = await response.json();
        } catch (parseError) {
            payload = { success: false, message: 'Unexpected server response.' };
        }

        if (!response.ok || payload.success === false) {
            // Auto-logout on 401 (expired/invalid token)
            if (response.status === 401) {
                clearToken();
                if (!window.location.pathname.includes('login.php')) {
                    window.location.href = `${window.location.origin}/task-tracker/pages/login.php`;
                }
            }

            throw {
                success: false,
                message: payload.message || 'Something went wrong.',
                errors: payload.errors || null,
                status: response.status,
            };
        }

        return payload;
    }

    function get(path) {
        return request(path, { method: 'GET' });
    }

    function post(path, body) {
        return request(path, { method: 'POST', body: JSON.stringify(body || {}) });
    }

    function put(path, body) {
        return request(path, { method: 'PUT', body: JSON.stringify(body || {}) });
    }

    function del(path) {
        return request(path, { method: 'DELETE' });
    }

    return {
        BASE_URL,
        getToken,
        setToken,
        clearToken,
        setUser,
        getUser,
        get,
        post,
        put,
        del,
    };
})();
