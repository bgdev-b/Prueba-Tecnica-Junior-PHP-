<?php
session_start();
require_once "../app/config/db.php";
require_once "../app/middleware/auth.php";

header('Content-Type: application/json');

$id      = intval($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

echo json_encode(['success' => $stmt->affected_rows > 0]);
