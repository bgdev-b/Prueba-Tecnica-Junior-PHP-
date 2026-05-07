<?php
ob_start();
session_start();
require_once "../app/config/db.php";
require_once "../app/middleware/auth.php";
ob_end_clean();

header('Content-Type: application/json');

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (!$title) {
    echo json_encode(['success' => false, 'message' => 'Task title is required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, NULLIF(?, ''))");
$stmt->bind_param("iss", $user_id, $title, $description);
$stmt->execute();
$task_id = $conn->insert_id;

echo json_encode([
    'success'     => true,
    'id'          => $task_id,
    'title'       => $title,
    'description' => $description
]);
