<?php
require_once __DIR__ . "/app/config/db.php";
require_once __DIR__ . "/app/middleware/auth.php";

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$filter = $_GET['filter'] ?? 'all';
$sql    = "SELECT * FROM tasks WHERE user_id = ?";
if ($filter === 'pending') {
    $sql .= " AND status = 'pending'";
} elseif ($filter === 'completed') {
    $sql .= " AND status = 'completed'";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="dashboard-body">

    <nav class="navbar shadow-sm px-4">
        <span class="navbar-brand fw-bold fs-5">
            <i class="bi bi-check2-square me-1"></i> My Tasks
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">Hello, <strong><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></strong></span>
            <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container py-5 mx-auto" style="max-width: 860px;">

        <div class="card shadow-sm mb-4 task-entry-wrapper">
            <div class="card-body task-entry-card">
                <div class="input-group task-input-group">
                    <input type="text" id="task-input" class="form-control form-control-lg"
                        placeholder="Write a new task..." maxlength="255">
                    <button id="add-task-btn" class="btn btn-primary px-4">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
                <textarea id="task-description" class="form-control mt-3" rows="3"
                    placeholder="Optional description..." maxlength="1000"></textarea>
            </div>
        </div>

        <ul class="nav nav-pills mb-3 justify-content-center" id="task-filters">
            <li class="nav-item">
                <button class="nav-link <?= $filter === 'all'       ? 'active' : '' ?>" data-filter="all">All</button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $filter === 'pending'   ? 'active' : '' ?>" data-filter="pending">
                    <i class="bi bi-hourglass-split me-1"></i>Pending
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $filter === 'completed' ? 'active' : '' ?>" data-filter="completed">
                    <i class="bi bi-check-circle me-1"></i>Completed
                </button>
            </li>
        </ul>

        <div id="task-list">
            <?php if (empty($tasks)): ?>
                <div id="empty-state" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No tasks here. Add one above!
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task['status'] === 'completed' ? 'completed' : '' ?>"
                        data-id="<?= $task['id'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" class="task-checkbox form-check-input"
                                <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
                            <div class="flex-grow-1">
                                <span class="task-title d-block">
                                    <?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if (!empty($task['description'])): ?>
                                    <small class="task-description d-block mt-1 text-muted">
                                        <?= nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-link text-danger p-0 delete-btn" title="Delete">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js?v=<?= filemtime(__DIR__ . '/assets/js/script.js') ?>"></script>
</body>

</html>