<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();

if (isset($_POST['login'])) {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (!$email || !$password) {
        die("Todos los campos son obligatorios");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            session_write_close();
            header("Location: /dashboard.php");
            exit();
        }
    }

    $_SESSION['login_error'] = "Incorrect email or password";
    $_SESSION['active_form'] = 'login';

    header("Location: /");
    exit();
}
