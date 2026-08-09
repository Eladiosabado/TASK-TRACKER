/**
 * assets/js/utils.js
 *
 * Reusable frontend utility functions:
 *   - Toast notifications
 *   - Debounce
 *   - Form helpers
 *   - Loading helpers
 *   - HTML escaping
 */

const Utils = (() => {
    /**
     * Escape a string for safe insertion into innerHTML.
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    /**
     * Show a toast notification.
     * @param {string} message
     * @param {'success'|'error'|'info'} type
     */
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        // Trigger enter animation
        requestAnimationFrame(() => toast.classList.add('toast-visible'));

        setTimeout(() => {
            toast.classList.remove('toast-visible');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    /**
     * Debounce a function call.
     */
    function debounce(fn, delay = 400) {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(null, args), delay);
        };
    }

    /**
     * Toggle a button's loading state (disables it and swaps label text).
     */
    function setButtonLoading(button, isLoading, loadingText = 'Please wait...') {
        if (!button) return;
        if (isLoading) {
            button.dataset.originalText = button.dataset.originalText || button.textContent;
            button.textContent = loadingText;
            button.disabled = true;
            button.classList.add('is-loading');
        } else {
            button.textContent = button.dataset.originalText || button.textContent;
            button.disabled = false;
            button.classList.remove('is-loading');
        }
    }

    /**
     * Show a field-level validation error under a form field.
     */
    function setFieldError(fieldId, message) {
        const errorEl = document.getElementById(`${fieldId}-error`);
        const inputEl = document.getElementById(fieldId);
        if (errorEl) errorEl.textContent = message || '';
        if (inputEl) inputEl.classList.toggle('input-error', Boolean(message));
    }

    /**
     * Clear all validation errors within a form.
     */
    function clearFormErrors(form) {
        if (!form) return;
        form.querySelectorAll('.field-error').forEach(el => (el.textContent = ''));
        form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    }

    /**
     * Apply a map of { fieldName: message } errors returned by the API.
     */
    function applyApiErrors(form, errors) {
        if (!errors) return;
        Object.keys(errors).forEach(field => {
            setFieldError(field, errors[field]);
        });
    }

    /**
     * Format an ISO date (YYYY-MM-DD) into a friendlier display string.
     */
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const [year, month, day] = dateStr.split('-');
        if (!year || !month || !day) return dateStr;
        const date = new Date(Number(year), Number(month) - 1, Number(day));
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    /**
     * Human-friendly status label.
     */
    function statusLabel(status) {
        const map = {
            pending: 'Pending',
            in_progress: 'In Progress',
            completed: 'Completed',
        };
        return map[status] || status;
    }

    return {
        escapeHtml,
        showToast,
        debounce,
        setButtonLoading,
        setFieldError,
        clearFormErrors,
        applyApiErrors,
        formatDate,
        statusLabel,
    };
})();
