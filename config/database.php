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

        $host = '127.0.0.1';
        $port = '3306';
        $db   = 'spa_management';
        $user = 'root';
        $pass = '';

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