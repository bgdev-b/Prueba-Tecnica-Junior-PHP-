function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}

const STORAGE_KEY = 'tasks_cache';
const DRAFT_TITLE_KEY = 'draft_title';
const DRAFT_DESC_KEY = 'draft_desc';

function readTasksFromDOM() {
    return Array.from(document.querySelectorAll('.task-item')).map(el => ({
        id: el.dataset.id,
        title: el.querySelector('.task-title')?.textContent.trim() ?? '',
        description: el.querySelector('.task-description')?.textContent.trim() ?? '',
        status: el.classList.contains('completed') ? 'completed' : 'pending'
    }));
}

function saveTasksToStorage() {
    try {
        const filter = new URLSearchParams(window.location.search).get('filter') || 'all';
        localStorage.setItem(STORAGE_KEY + '_' + filter, JSON.stringify(readTasksFromDOM()));
    } catch (_) { }
}

// ─────────────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-task-btn');
    const taskInput = document.getElementById('task-input');
    const taskDescriptionInput = document.getElementById('task-description');

    if (!addBtn) return;

    // Restore drafts
    if (taskInput && localStorage.getItem(DRAFT_TITLE_KEY)) {
        taskInput.value = localStorage.getItem(DRAFT_TITLE_KEY);
    }
    if (taskDescriptionInput && localStorage.getItem(DRAFT_DESC_KEY)) {
        taskDescriptionInput.value = localStorage.getItem(DRAFT_DESC_KEY);
    }

    // Persist drafts while typing
    taskInput?.addEventListener('input', () =>
        localStorage.setItem(DRAFT_TITLE_KEY, taskInput.value));
    taskDescriptionInput?.addEventListener('input', () =>
        localStorage.setItem(DRAFT_DESC_KEY, taskDescriptionInput.value));

    // Save initial server-rendered task list
    saveTasksToStorage();

    document.querySelectorAll('#task-filters .nav-link').forEach(btn => {
        btn.addEventListener('click', function () {
            window.location.href = `dashboard.php?filter=${this.dataset.filter}`;
        });
    });

    addBtn.addEventListener('click', addTask);
    taskInput.addEventListener('keydown', e => { if (e.key === 'Enter') addTask(); });

    function addTask() {
        const title = taskInput.value.trim();
        const description = taskDescriptionInput ? taskDescriptionInput.value.trim() : '';
        if (!title) {
            Swal.fire({ icon: 'warning', title: 'Empty task', text: 'Please write something first.', confirmButtonColor: '#7494ec' });
            return;
        }

        fetch('tasks/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    taskInput.value = '';
                    if (taskDescriptionInput) taskDescriptionInput.value = '';
                    localStorage.removeItem(DRAFT_TITLE_KEY);
                    localStorage.removeItem(DRAFT_DESC_KEY);
                    prependTask(data.id, data.title, data.description ?? '');
                    document.getElementById('empty-state')?.remove();
                    saveTasksToStorage();
                    Swal.fire({ icon: 'success', title: 'Task added!', showConfirmButton: false, timer: 1200, timerProgressBar: true });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save the task. Try again.' });
            });
    }

    function prependTask(id, title, description = '') {
        const div = document.createElement('div');
        div.className = 'task-item';
        div.dataset.id = id;

        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-3';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'task-checkbox form-check-input';

        const textWrapper = document.createElement('div');
        textWrapper.className = 'flex-grow-1';

        const titleSpan = document.createElement('span');
        titleSpan.className = 'task-title d-block';
        titleSpan.textContent = title;
        textWrapper.appendChild(titleSpan);

        if (description.trim().length > 0) {
            const descSmall = document.createElement('small');
            descSmall.className = 'task-description d-block mt-1 text-muted';
            description.trim().split('\n').forEach((line, i, arr) => {
                descSmall.appendChild(document.createTextNode(line));
                if (i < arr.length - 1) descSmall.appendChild(document.createElement('br'));
            });
            textWrapper.appendChild(descSmall);
        }

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-link text-danger p-0 delete-btn';
        deleteBtn.title = 'Delete';
        deleteBtn.innerHTML = '<i class="bi bi-trash3"></i>';

        row.appendChild(checkbox);
        row.appendChild(textWrapper);
        row.appendChild(deleteBtn);
        div.appendChild(row);

        document.getElementById('task-list').prepend(div);
        bindTaskEvents(div);
    }

    document.querySelectorAll('.task-item').forEach(bindTaskEvents);

    function bindTaskEvents(item) {
        item.querySelector('.task-checkbox').addEventListener('change', function () {
            fetch('tasks/toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${item.dataset.id}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'completed') {
                            item.classList.add('completed');
                            this.checked = true;
                        } else {
                            item.classList.remove('completed');
                            this.checked = false;
                        }
                        saveTasksToStorage();
                    }
                });
        });

        item.querySelector('.delete-btn').addEventListener('click', function () {
            Swal.fire({
                title: 'Delete task?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#7494ec',
                confirmButtonText: 'Yes, delete it'
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch('tasks/delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${item.dataset.id}`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();
                            saveTasksToStorage();
                            Swal.fire({ icon: 'success', title: 'Deleted!', showConfirmButton: false, timer: 1000 });
                            if (!document.querySelector('.task-item')) {
                                const empty = document.createElement('div');
                                empty.id = 'empty-state';
                                empty.className = 'text-center text-muted py-5';
                                empty.innerHTML = '<i class="bi bi-inbox fs-1 d-block mb-2"></i>No tasks here. Add one above!';
                                document.getElementById('task-list').appendChild(empty);
                            }
                        }
                    });
            });
        });
    }
});
