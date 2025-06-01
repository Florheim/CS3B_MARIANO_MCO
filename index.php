<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .task-card {
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .task-card.completed {
            background-color: #e9ecef;
            text-decoration: line-through;
            opacity: 0.7;
        }
        .task-card .card-body {
            padding: 1rem;
        }
        .task-card .card-title {
            margin-bottom: .5rem;
        }
        .task-card .card-subtitle {
            font-size: .875em;
            color: #6c757d;
            margin-bottom: .5rem;
        }
        .task-card .card-text {
            margin-bottom: 1rem;
        }
        .task-card .badge {
            font-size: .75em;
            padding: .35em .65em;
            border-radius: .25rem;
            margin-right: 5px;
        }
        .badge-pending { background-color: #ffc107; color: #343a40; } 
        .badge-in-progress { background-color: #17a2b8; color: white; } 
        .badge-completed { background-color: #28a745; color: white; } 

        .card-actions {
            margin-top: 10px;
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }
        .modal-body label {
            font-weight: bold;
        }
        .navbar-brand {
            font-weight: bold;
            color: #007bff !important;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['user_id_task_app'])) {
        header("Location: login_account.php"); 
        exit;
    }
    $loggedInUsername = $_SESSION['username_task_app'] ?? 'User';
    ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Task Manager</a>
            <span class="navbar-text me-3">
                Welcome, <?= htmlspecialchars($loggedInUsername) ?>!
            </span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center mb-4">Your Tasks</h1>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Add New Task</div>
            <div class="card-body">
                <form id="addTaskForm">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="taskTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="taskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate" name="due_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="taskStatus" class="form-label">Status</label>
                            <select class="form-select" id="taskStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="in progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus-circle"></i> Add Task</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">Filter & Sort Tasks</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchTask" placeholder="Search by title or description">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterStatus">
                            <option value="">Status</option>
                            <option value="pending">Pending</option>
                            <option value="in progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="sortBy">
                            <option value="created_at_desc">Created At (Newest First)</option>
                            <option value="created_at_asc">Created At (Oldest First)</option>
                            <option value="due_date_asc">Due Date (Soonest First)</option>
                            <option value="due_date_desc">Due Date (Latest First)</option>
                            <option value="status_asc">Status (A-Z)</option>
                            <option value="title_asc">Title (A-Z)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="taskList" class="row">
            </div>
    </div>

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        <input type="hidden" id="editTaskId" name="id">
                        <div class="mb-3">
                            <label for="editTaskTitle" class="form-label">Task Title</label>
                            <input type="text" class="form-control" id="editTaskTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editTaskDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editTaskDueDate" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="editTaskDueDate" name="due_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTaskStatus" class="form-label">Status</label>
                                <select class="form-select" id="editTaskStatus" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="in progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addTaskForm = document.getElementById('addTaskForm');
            const editTaskForm = document.getElementById('editTaskForm');
            const taskList = document.getElementById('taskList');
            const editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
            const filterStatus = document.getElementById('filterStatus');
            const sortBy = document.getElementById('sortBy');
            const searchTask = document.getElementById('searchTask');

            fetchTasks();

            addTaskForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(addTaskForm);
                const response = await fetch('status_tasks.php?action=add_task', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    addTaskForm.reset(); 
                    fetchTasks(); 
                } else {
                    alert('Error adding task: ' + result.error);
                }
            });

            editTaskForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(editTaskForm);
                const response = await fetch('status_tasks.php?action=update_task', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    editTaskModal.hide(); 
                    fetchTasks(); 
                } else {
                    alert('Error updating task: ' + result.error);
                }
            });

            filterStatus.addEventListener('change', fetchTasks);
            sortBy.addEventListener('change', fetchTasks);
            searchTask.addEventListener('input', fetchTasks); 

            async function fetchTasks() {
                taskList.innerHTML = '<div class="text-center p-5">Loading tasks...</div>'; 
                const selectedSort = sortBy.value.split('_');
                const sort_by = selectedSort[0];
                const sort_order = selectedSort[1] || 'DESC'; 
                const filter_status = filterStatus.value;
                const search_query = searchTask.value;

                const queryParams = new URLSearchParams({
                    action: 'get_tasks',
                    sort_by: sort_by,
                    sort_order: sort_order,
                    filter_status: filter_status,
                    search_query: search_query
                });

                const response = await fetch(`status_tasks.php?${queryParams.toString()}`);
                const result = await response.json();

                taskList.innerHTML = ''; 

                if (result.success && result.tasks.length > 0) {
                    result.tasks.forEach(task => {
                        const taskCard = document.createElement('div');
                        taskCard.className = `col-md-6 col-lg-4`; 
                        taskCard.innerHTML = `
                            <div class="card task-card ${task.status === 'completed' ? 'completed' : ''}">
                                <div class="card-body">
                                    <h5 class="card-title">${task.title}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted">Due: ${task.due_date || 'N/A'}</h6>
                                    <p class="card-text">${task.description || 'No description.'}</p>
                                    <span class="badge badge-${task.status.replace(/\s/g, '-') || 'pending'}">${task.status}</span>
                                    <span class="badge bg-secondary">Created: ${new Date(task.created_at).toLocaleDateString()}</span>
                                    <div class="card-actions">
                                        <button class="btn btn-success btn-sm mark-done-btn" data-id="${task.id}" ${task.status === 'completed' ? 'disabled' : ''}><i class="fas fa-check"></i> Done</button>
                                        <button class="btn btn-info btn-sm edit-btn" data-id="${task.id}"
                                            data-title="${task.title}"
                                            data-description="${task.description}"
                                            data-due_date="${task.due_date || ''}"
                                            data-status="${task.status}"><i class="fas fa-edit"></i> Edit</button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="${task.id}"><i class="fas fa-trash-alt"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        taskList.appendChild(taskCard);
                    });
                    addActionButtonListeners(); 
                } else if (result.tasks && result.tasks.length === 0) {
                    taskList.innerHTML = '<div class="col-12 text-center p-5 text-muted">No tasks found. Add a new one!</div>';
                } else {
                    taskList.innerHTML = `<div class="col-12 text-center p-5 text-danger">Error loading tasks: ${result.error || 'Unknown error'}</div>`;
                }
            }

            function addActionButtonListeners() {
                document.querySelectorAll('.mark-done-btn').forEach(button => {
                    button.addEventListener('click', async function() {
                        const taskId = this.dataset.id;
                        if (confirm('Are you sure you want to mark this task as completed?')) {
                            const formData = new FormData();
                            formData.append('id', taskId);
                            const response = await fetch('status_tasks.php?action=mark_done', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert(result.message);
                                fetchTasks();
                            } else {
                                alert('Error marking task as done: ' + result.error);
                            }
                        }
                    });
                });


                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        document.getElementById('editTaskId').value = this.dataset.id;
                        document.getElementById('editTaskTitle').value = this.dataset.title;
                        document.getElementById('editTaskDescription').value = this.dataset.description;
                        document.getElementById('editTaskDueDate').value = this.dataset.due_date;
                        document.getElementById('editTaskStatus').value = this.dataset.status;
                        editTaskModal.show();
                    });
                });

                // Delete Task
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', async function() {
                        const taskId = this.dataset.id;
                        if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                            const formData = new FormData();
                            formData.append('id', taskId);
                            const response = await fetch('status_tasks.php?action=delete_task', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                alert(result.message);
                                fetchTasks();
                            } else {
                                alert('Error deleting task: ' + result.error);
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>