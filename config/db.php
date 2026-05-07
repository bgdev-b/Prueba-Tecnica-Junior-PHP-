<?php
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'todo_app';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
