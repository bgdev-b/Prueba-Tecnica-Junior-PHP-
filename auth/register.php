<?php
session_start();
require_once "../app/config/db.php";

if (isset($_POST['register'])) {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!$name || !$email || !$password) {
        $_SESSION['register_error'] = "Todos los campos son obligatorios";
        $_SESSION['active_form'] = 'register';
        header("Location: ../index.php");
        exit();
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
    }

    header("Location: ../index.php");
    exit();
}
