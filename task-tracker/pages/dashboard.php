<?php
$pageTitle = 'Dashboard · Task Tracker';
$bodyDataPage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<header class="app-header">
    <div class="app-brand">
        <span class="brand-mark"></span>
        <span>Task Tracker</span>
    </div>
    <div class="app-header-right">
        <div class="current-user">Signed in as <strong id="current-user-name">…</strong></div>
        <button id="logout-btn" class="btn btn-sm btn-secondary">Log Out</button>
    </div>
</header>

<main class="app-main">

    <!-- Statistics -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Tasks</div>
            <div class="stat-value" id="stat-total">0</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-label">Pending</div>
            <div class="stat-value" id="stat-pending">0</div>
        </div>
        <div class="stat-card stat-progress">
            <div class="stat-label">In Progress</div>
            <div class="stat-value" id="stat-in-progress">0</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-label">Completed</div>
            <div class="stat-value" id="stat-completed">0</div>
        </div>
    </section>

    <!-- Task Management -->
    <section class="panel">
        <div class="panel-header">
            <h2>Tasks</h2>
            <button id="add-task-btn" class="btn btn-primary">+ Add Task</button>
        </div>

        <div class="toolbar">
            <div class="form-group search-field">
                <input type="search" id="task-search" placeholder="Search by title…" aria-label="Search tasks">
            </div>
            <div class="form-group">
                <select id="status-filter" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <select id="category-filter" aria-label="Filter by category">
                    <option value="">All Categories</option>
                </select>
            </div>
        </div>

        <div id="tasks-loading-state" class="state-block hidden">
            <span class="state-icon">⏳</span>
            Loading tasks…
        </div>

        <div id="tasks-empty-state" class="state-block hidden">
            <span class="state-icon">🗂️</span>
            No tasks found. Try adjusting your filters or add a new task.
        </div>

        <div id="tasks-table-wrapper" class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tasks-table-body"></tbody>
            </table>
        </div>

        <div id="pagination" class="pagination"></div>
    </section>

    <!-- Category Management -->
    <section class="panel">
        <div class="panel-header">
            <h2>Categories</h2>
        </div>

        <form id="category-add-form" class="category-add-row" novalidate>
            <div class="form-group">
                <input type="text" id="category-add-input" placeholder="New category name…" aria-label="New category name">
                <span class="field-error" id="category-add-input-error"></span>
            </div>
            <button type="submit" id="category-add-btn" class="btn btn-primary">Add</button>
        </form>

        <div id="categories-empty-state" class="state-block hidden">
            <span class="state-icon">🏷️</span>
            No categories yet. Add one above.
        </div>

        <ul id="categories-list" class="categories-list"></ul>
    </section>

</main>

<!-- Task Modal (Create/Edit) -->
<div id="task-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="task-modal-title">Add Task</h3>
            <button type="button" id="task-modal-close" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="task-form" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label for="task-title">Title</label>
                    <input type="text" id="task-title" required>
                    <span class="field-error" id="task-title-error"></span>
                </div>

                <div class="form-group">
                    <label for="task-description">Description</label>
                    <textarea id="task-description"></textarea>
                    <span class="field-error" id="task-description-error"></span>
                </div>

                <div class="form-group">
                    <label for="task-status">Status</label>
                    <select id="task-status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                    <span class="field-error" id="task-status-error"></span>
                </div>

                <div class="form-group">
                    <label for="task-due-date">Due Date</label>
                    <input type="date" id="task-due-date">
                    <span class="field-error" id="task-due-date-error"></span>
                </div>

                <div class="form-group">
                    <label for="task-category">Category</label>
                    <select id="task-category" required>
                        <option value="">Select a category</option>
                    </select>
                    <span class="field-error" id="task-category-error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="task-modal-cancel" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="task-submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="modal-overlay">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3>Delete Task</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this task? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" id="delete-cancel-btn" class="btn btn-secondary">Cancel</button>
            <button type="button" id="delete-confirm-btn" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Category Edit Modal -->
<div id="category-edit-modal" class="modal-overlay">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button type="button" id="category-edit-close" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="category-edit-form" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label for="category-edit-input">Category Name</label>
                    <input type="text" id="category-edit-input" required>
                    <span class="field-error" id="category-edit-input-error"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="category-edit-cancel" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="category-edit-submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php
$pageScripts = [
    '/assets/js/categories.js',
    '/assets/js/tasks.js',
    '/assets/js/dashboard.js',
];
require_once __DIR__ . '/../includes/footer.php';
?>
