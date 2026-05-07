<?php
function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return trim((string)$value);
}

$dbUrl = env_value('DATABASE_URL');
if ($dbUrl === '') {
    $dbUrl = env_value('MYSQL_URL');
}

if ($dbUrl !== '') {
    $parts = parse_url($dbUrl);
    $host = $parts['host'] ?? 'localhost';
    $port = (int)($parts['port'] ?? 3306);
    $username = isset($parts['user']) ? rawurldecode($parts['user']) : 'root';
    $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'todo_app';
} else {
    $host = env_value('DB_HOST', 'localhost');
    $dbname = env_value('DB_NAME', 'todo_app');
    $username = env_value('DB_USER', 'root');
    $password = env_value('DB_PASSWORD', '');
    $port = (int)env_value('DB_PORT', '3306');
}

$conn = new mysqli($host, $username, $password, $dbname, $port);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
