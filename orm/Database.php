<?php
namespace ORM;

use PDO;
use Exception;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(string $dsn, string $user, string $password): void
    {
        if (self::$connection) {
            return; // zaten bağlı
        }

        try {
            self::$connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getConnection(): PDO
    {
        if (!self::$connection) {
            throw new Exception("Database not connected. Call Database::connect() first.");
        }

        return self::$connection;
    }
}
