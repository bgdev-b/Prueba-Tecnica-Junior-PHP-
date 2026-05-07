<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();
require_once __DIR__ . "/../app/middleware/auth.php";

header('Content-Type: application/json');

$id      = intval($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "UPDATE tasks SET status = IF(status='pending','completed','pending') WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $sel = $conn->prepare("SELECT status FROM tasks WHERE id = ?");
    $sel->bind_param("i", $id);
    $sel->execute();
    $row = $sel->get_result()->fetch_assoc();
    echo json_encode(['success' => true, 'status' => $row['status']]);
} else {
    echo json_encode(['success' => false]);
}
