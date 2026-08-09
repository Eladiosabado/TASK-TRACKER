/**
 * assets/js/categories.js
 *
 * Responsible for:
 *   - Loading categories
 *   - Populating category selects/filters used by tasks.js
 *   - Create / edit / delete category management UI
 */

const Categories = (() => {
    let cachedCategories = [];
    const el = {};

    function cacheDom() {
        el.list = document.getElementById('categories-list');
        el.emptyState = document.getElementById('categories-empty-state');
        el.addForm = document.getElementById('category-add-form');
        el.addInput = document.getElementById('category-add-input');
        el.addBtn = document.getElementById('category-add-btn');

        el.editModal = document.getElementById('category-edit-modal');
        el.editForm = document.getElementById('category-edit-form');
        el.editInput = document.getElementById('category-edit-input');
        el.editCloseBtn = document.getElementById('category-edit-close');
        el.editCancelBtn = document.getElementById('category-edit-cancel');
        el.editSubmitBtn = document.getElementById('category-edit-submit');
    }

    async function loadCategories() {
        try {
            const response = await Api.get('/categories/index.php');
            cachedCategories = response.data;
            renderCategoryList();
            if (typeof Tasks !== 'undefined') {
                Tasks.populateCategorySelect(cachedCategories);
                Tasks.populateCategoryFilter(cachedCategories);
            }
        } catch (err) {
            Utils.showToast(err.message || 'Could not load categories.', 'error');
        }
    }

    function renderCategoryList() {
        if (!el.list) return;
        el.list.innerHTML = '';

        if (!cachedCategories.length) {
            if (el.emptyState) el.emptyState.classList.remove('hidden');
            return;
        }
        if (el.emptyState) el.emptyState.classList.add('hidden');

        cachedCategories.forEach(cat => {
            const item = document.createElement('li');
            item.className = 'category-item';
            item.innerHTML = `
                <span class="category-name">${Utils.escapeHtml(cat.name)}</span>
                <span class="category-actions">
                    <button class="btn btn-sm btn-secondary" data-action="edit" data-id="${cat.id}" data-name="${Utils.escapeHtml(cat.name)}">Edit</button>
                    <button class="btn btn-sm btn-danger" data-action="delete" data-id="${cat.id}">Delete</button>
                </span>
            `;
            el.list.appendChild(item);
        });
    }

    async function handleAddSubmit(e) {
        e.preventDefault();
        Utils.clearFormErrors(el.addForm);
        const name = el.addInput.value.trim();

        if (!name) {
            Utils.setFieldError('category-add-input', 'Category name is required.');
            return;
        }

        Utils.setButtonLoading(el.addBtn, true, 'Adding...');
        try {
            await Api.post('/categories/create.php', { name });
            Utils.showToast('Category added.', 'success');
            el.addInput.value = '';
            await loadCategories();
        } catch (err) {
            if (err.errors) {
                Utils.applyApiErrors(el.addForm, err.errors);
            } else {
                Utils.showToast(err.message || 'Could not add category.', 'error');
            }
        } finally {
            Utils.setButtonLoading(el.addBtn, false);
        }
    }

    let editingId = null;

    function openEditModal(id, name) {
        editingId = id;
        el.editInput.value = name;
        Utils.clearFormErrors(el.editForm);
        el.editModal.classList.add('modal-open');
    }

    function closeEditModal() {
        editingId = null;
        el.editModal.classList.remove('modal-open');
    }

    async function handleEditSubmit(e) {
        e.preventDefault();
        Utils.clearFormErrors(el.editForm);
        const name = el.editInput.value.trim();

        if (!name) {
            Utils.setFieldError('category-edit-input', 'Category name is required.');
            return;
        }

        Utils.setButtonLoading(el.editSubmitBtn, true, 'Saving...');
        try {
            await Api.put(`/categories/update.php?id=${editingId}`, { name });
            Utils.showToast('Category updated.', 'success');
            closeEditModal();
            await loadCategories();
        } catch (err) {
            if (err.errors) {
                Utils.applyApiErrors(el.editForm, err.errors);
            } else {
                Utils.showToast(err.message || 'Could not update category.', 'error');
            }
        } finally {
            Utils.setButtonLoading(el.editSubmitBtn, false);
        }
    }

    async function handleDelete(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;
        try {
            await Api.del(`/categories/delete.php?id=${id}`);
            Utils.showToast('Category deleted.', 'success');
            await loadCategories();
        } catch (err) {
            // Includes the "assigned to N task(s)" 409 message from the API.
            Utils.showToast(err.message || 'Could not delete category.', 'error');
        }
    }

    function bindEvents() {
        if (el.addForm) el.addForm.addEventListener('submit', handleAddSubmit);
        if (el.editForm) el.editForm.addEventListener('submit', handleEditSubmit);
        if (el.editCloseBtn) el.editCloseBtn.addEventListener('click', closeEditModal);
        if (el.editCancelBtn) el.editCancelBtn.addEventListener('click', closeEditModal);

        if (el.list) {
            el.list.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.dataset.id;
                if (btn.dataset.action === 'edit') {
                    openEditModal(id, btn.dataset.name);
                } else if (btn.dataset.action === 'delete') {
                    handleDelete(id);
                }
            });
        }
    }

    function init() {
        cacheDom();
        bindEvents();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.body.dataset.page === 'dashboard') {
            init();
        }
    });

    return { init, loadCategories };
})();
