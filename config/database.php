<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $environment = strtolower((string) (getenv('APP_ENV') ?: 'local'));
        $host = (string) (getenv('DB_HOST') ?: '127.0.0.1');
        $port = (string) (getenv('DB_PORT') ?: '3306');
        $db = (string) (getenv('DB_NAME') ?: 'spa_management');
        $user = (string) (getenv('DB_USER') ?: ($environment === 'local' ? 'root' : ''));
        $pass = (string) (getenv('DB_PASSWORD') ?: '');

        if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            throw new RuntimeException('DB_PORT must be a valid TCP port.');
        }
        if ($environment === 'production' && ($user === '' || $pass === '')) {
            throw new RuntimeException('Set DB_USER and DB_PASSWORD in the production environment.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        self::$connection = new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$connection;
    }
}