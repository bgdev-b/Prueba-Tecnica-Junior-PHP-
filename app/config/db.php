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
    $parts    = parse_url($dbUrl);
    $host     = $parts['host'] ?? 'localhost';
    $port     = (int)($parts['port'] ?? 3306);
    $username = isset($parts['user']) ? rawurldecode($parts['user']) : 'root';
    $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
    $dbname   = isset($parts['path']) ? ltrim($parts['path'], '/') : 'todo_app';
} else {
    $host     = env_value('DB_HOST', 'localhost');
    $dbname   = env_value('DB_NAME', 'todo_app');
    $username = env_value('DB_USER', 'root');
    $password = env_value('DB_PASSWORD', '');
    $port     = (int)env_value('DB_PORT', '3306');
}

$conn = new mysqli($host, $username, $password, $dbname, $port);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

class DbSessionHandler implements SessionHandlerInterface
{
    public function __construct(private mysqli $db) {}

    public function open(string $path, string $name): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->db->prepare(
            "SELECT data FROM sessions WHERE id = ? AND expires_at > NOW() LIMIT 1"
        );
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $lifetime = (int) ini_get('session.gc_maxlifetime') ?: 1440;
        $expires  = date('Y-m-d H:i:s', time() + $lifetime);
        $stmt = $this->db->prepare(
            "INSERT INTO sessions (id, data, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)"
        );
        $stmt->bind_param('sss', $id, $data, $expires);
        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param('s', $id);
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->db->query("DELETE FROM sessions WHERE expires_at < NOW()");
        return $this->db->affected_rows;
    }
}

session_set_save_handler(new DbSessionHandler($conn), true);
