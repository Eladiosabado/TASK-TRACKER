/**
 * assets/js/tasks.js
 *
 * Responsible for:
 *   - Loading tasks (search, filter, pagination)
 *   - Rendering the task table
 *   - Create / edit via modal
 *   - Delete with confirmation
 */

const Tasks = (() => {
    const state = {
        page: 1,
        limit: 10,
        status: '',
        categoryId: '',
        search: '',
        totalPages: 1,
        editingTaskId: null,
    };

    const el = {};

    function cacheDom() {
        el.tableBody = document.getElementById('tasks-table-body');
        el.emptyState = document.getElementById('tasks-empty-state');
        el.loadingState = document.getElementById('tasks-loading-state');
        el.tableWrapper = document.getElementById('tasks-table-wrapper');
        el.searchInput = document.getElementById('task-search');
        el.statusFilter = document.getElementById('status-filter');
        el.categoryFilter = document.getElementById('category-filter');
        el.pagination = document.getElementById('pagination');
        el.addTaskBtn = document.getElementById('add-task-btn');

        el.modal = document.getElementById('task-modal');
        el.modalTitle = document.getElementById('task-modal-title');
        el.modalForm = document.getElementById('task-form');
        el.modalCloseBtn = document.getElementById('task-modal-close');
        el.modalCancelBtn = document.getElementById('task-modal-cancel');
        el.modalSubmitBtn = document.getElementById('task-submit');
        el.categorySelect = document.getElementById('task-category');

        el.deleteModal = document.getElementById('delete-modal');
        el.deleteConfirmBtn = document.getElementById('delete-confirm-btn');
        el.deleteCancelBtn = document.getElementById('delete-cancel-btn');
    }

    function buildQueryString() {
        const params = new URLSearchParams();
        if (state.status) params.set('status', state.status);
        if (state.categoryId) params.set('category_id', state.categoryId);
        if (state.search) params.set('search', state.search);
        params.set('page', state.page);
        params.set('limit', state.limit);
        return params.toString();
    }

    function showLoading(isLoading) {
        if (el.loadingState) el.loadingState.classList.toggle('hidden', !isLoading);
        if (el.tableWrapper) el.tableWrapper.classList.toggle('hidden', isLoading);
    }

    async function loadTasks() {
        showLoading(true);
        if (el.emptyState) el.emptyState.classList.add('hidden');

        try {
            const response = await Api.get(`/tasks/index.php?${buildQueryString()}`);
            renderTasks(response.data);
            renderPagination(response.pagination);
            state.totalPages = response.pagination.total_pages;
        } catch (err) {
            Utils.showToast(err.message || 'Could not load tasks.', 'error');
        } finally {
            showLoading(false);
        }
    }

    function renderTasks(tasks) {
        if (!el.tableBody) return;
        el.tableBody.innerHTML = '';

        if (!tasks || tasks.length === 0) {
            if (el.emptyState) el.emptyState.classList.remove('hidden');
            if (el.tableWrapper) el.tableWrapper.classList.add('hidden');
            return;
        }

        if (el.emptyState) el.emptyState.classList.add('hidden');
        if (el.tableWrapper) el.tableWrapper.classList.remove('hidden');

        tasks.forEach(task => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td data-label="Title">
                    <div class="task-title">${Utils.escapeHtml(task.title)}</div>
                    ${task.description ? `<div class="task-desc">${Utils.escapeHtml(task.description)}</div>` : ''}
                </td>
                <td data-label="Status">
                    <span class="badge badge-${task.status}">${Utils.escapeHtml(Utils.statusLabel(task.status))}</span>
                </td>
                <td data-label="Category">${Utils.escapeHtml(task.category_name)}</td>
                <td data-label="Due Date">${Utils.escapeHtml(Utils.formatDate(task.due_date))}</td>
                <td data-label="Actions" class="task-actions">
                    <button class="btn btn-sm btn-secondary" data-action="edit" data-id="${task.id}">Edit</button>
                    <button class="btn btn-sm btn-danger" data-action="delete" data-id="${task.id}">Delete</button>
                </td>
            `;
            el.tableBody.appendChild(tr);
        });
    }

    function renderPagination(pagination) {
        if (!el.pagination) return;
        el.pagination.innerHTML = '';

        const { page, total_pages } = pagination;

        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'btn btn-sm btn-secondary';
        prevBtn.disabled = page <= 1;
        prevBtn.addEventListener('click', () => {
            state.page = Math.max(1, state.page - 1);
            loadTasks();
        });
        el.pagination.appendChild(prevBtn);

        const maxButtons = 5;
        let start = Math.max(1, page - Math.floor(maxButtons / 2));
        let end = Math.min(total_pages, start + maxButtons - 1);
        start = Math.max(1, end - maxButtons + 1);

        for (let p = start; p <= end; p++) {
            const btn = document.createElement('button');
            btn.textContent = p;
            btn.className = 'btn btn-sm ' + (p === page ? 'btn-primary' : 'btn-secondary');
            btn.addEventListener('click', () => {
                state.page = p;
                loadTasks();
            });
            el.pagination.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'btn btn-sm btn-secondary';
        nextBtn.disabled = page >= total_pages || total_pages === 0;
        nextBtn.addEventListener('click', () => {
            state.page = Math.min(total_pages, state.page + 1);
            loadTasks();
        });
        el.pagination.appendChild(nextBtn);
    }

    function populateCategorySelect(categories) {
        if (!el.categorySelect) return;
        el.categorySelect.innerHTML = '<option value="">Select a category</option>';
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            el.categorySelect.appendChild(opt);
        });
    }

    function populateCategoryFilter(categories) {
        if (!el.categoryFilter) return;
        el.categoryFilter.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            el.categoryFilter.appendChild(opt);
        });
    }

    function openModal(task = null) {
        state.editingTaskId = task ? task.id : null;
        el.modalTitle.textContent = task ? 'Edit Task' : 'Add Task';
        el.modalSubmitBtn.textContent = task ? 'Save Changes' : 'Create Task';

        Utils.clearFormErrors(el.modalForm);
        el.modalForm.reset();

        if (task) {
            document.getElementById('task-title').value = task.title || '';
            document.getElementById('task-description').value = task.description || '';
            document.getElementById('task-status').value = task.status || 'pending';
            document.getElementById('task-due-date').value = task.due_date || '';
            document.getElementById('task-category').value = task.category_id || '';
        } else {
            document.getElementById('task-status').value = 'pending';
        }

        el.modal.classList.add('modal-open');
    }

    function closeModal() {
        el.modal.classList.remove('modal-open');
        state.editingTaskId = null;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        Utils.clearFormErrors(el.modalForm);

        const payload = {
            title: document.getElementById('task-title').value.trim(),
            description: document.getElementById('task-description').value.trim(),
            status: document.getElementById('task-status').value,
            due_date: document.getElementById('task-due-date').value,
            category_id: document.getElementById('task-category').value,
        };

        Utils.setButtonLoading(el.modalSubmitBtn, true, 'Saving...');

        try {
            if (state.editingTaskId) {
                await Api.put(`/tasks/update.php?id=${state.editingTaskId}`, payload);
                Utils.showToast('Task updated successfully.', 'success');
            } else {
                await Api.post('/tasks/create.php', payload);
                Utils.showToast('Task created successfully.', 'success');
            }
            closeModal();
            await loadTasks();
            if (typeof Dashboard !== 'undefined') Dashboard.loadStats();
        } catch (err) {
            if (err.errors) {
                Utils.applyApiErrors(el.modalForm, err.errors);
            } else {
                Utils.showToast(err.message || 'Could not save task.', 'error');
            }
        } finally {
            Utils.setButtonLoading(el.modalSubmitBtn, false);
        }
    }

    let pendingDeleteId = null;

    function openDeleteModal(id) {
        pendingDeleteId = id;
        el.deleteModal.classList.add('modal-open');
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        el.deleteModal.classList.remove('modal-open');
    }

    async function confirmDelete() {
        if (!pendingDeleteId) return;
        Utils.setButtonLoading(el.deleteConfirmBtn, true, 'Deleting...');
        try {
            await Api.del(`/tasks/delete.php?id=${pendingDeleteId}`);
            Utils.showToast('Task deleted successfully.', 'success');
            closeDeleteModal();
            await loadTasks();
            if (typeof Dashboard !== 'undefined') Dashboard.loadStats();
        } catch (err) {
            Utils.showToast(err.message || 'Could not delete task.', 'error');
        } finally {
            Utils.setButtonLoading(el.deleteConfirmBtn, false);
        }
    }

    async function handleEditClick(id) {
        try {
            const response = await Api.get(`/tasks/show.php?id=${id}`);
            openModal(response.data);
        } catch (err) {
            Utils.showToast(err.message || 'Could not load task.', 'error');
        }
    }

    function bindEvents() {
        if (el.searchInput) {
            const debouncedSearch = Utils.debounce(() => {
                state.search = el.searchInput.value.trim();
                state.page = 1;
                loadTasks();
            }, 400);
            el.searchInput.addEventListener('input', debouncedSearch);
        }

        if (el.statusFilter) {
            el.statusFilter.addEventListener('change', () => {
                state.status = el.statusFilter.value;
                state.page = 1;
                loadTasks();
            });
        }

        if (el.categoryFilter) {
            el.categoryFilter.addEventListener('change', () => {
                state.categoryId = el.categoryFilter.value;
                state.page = 1;
                loadTasks();
            });
        }

        if (el.addTaskBtn) {
            el.addTaskBtn.addEventListener('click', () => openModal(null));
        }

        if (el.modalCloseBtn) el.modalCloseBtn.addEventListener('click', closeModal);
        if (el.modalCancelBtn) el.modalCancelBtn.addEventListener('click', closeModal);
        if (el.modalForm) el.modalForm.addEventListener('submit', handleFormSubmit);

        if (el.tableBody) {
            el.tableBody.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.dataset.id;
                if (btn.dataset.action === 'edit') {
                    handleEditClick(id);
                } else if (btn.dataset.action === 'delete') {
                    openDeleteModal(id);
                }
            });
        }

        if (el.deleteConfirmBtn) el.deleteConfirmBtn.addEventListener('click', confirmDelete);
        if (el.deleteCancelBtn) el.deleteCancelBtn.addEventListener('click', closeDeleteModal);
    }

    async function init() {
        cacheDom();
        bindEvents();
        await loadTasks();
    }

    return {
        init,
        loadTasks,
        populateCategorySelect,
        populateCategoryFilter,
    };
})();
