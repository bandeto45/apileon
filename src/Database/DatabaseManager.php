<?php

namespace Apileon\Database;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseManager
{
    private static ?PDO $connection = null;
    private static array $config = [];
    private static bool $transactionActive = false;

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    private static function connect(): void
    {
        $config = self::getDefaultConfig();
        $connectionName = $config['default'];
        $connectionConfig = $config['connections'][$connectionName];

        try {
            $dsn = self::buildDsn($connectionConfig);
            
            self::$connection = new PDO(
                $dsn,
                $connectionConfig['username'] ?? null,
                $connectionConfig['password'] ?? null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$connectionConfig['charset']}",
                    PDO::ATTR_PERSISTENT => false
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    private static function buildDsn(array $config): string
    {
        switch ($config['driver']) {
            case 'mysql':
                return sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );

            case 'pgsql':
                return sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $config['host'],
                    $config['port'],
                    $config['database']
                );

            case 'sqlite':
                return 'sqlite:' . $config['database'];

            default:
                throw new RuntimeException("Unsupported database driver: {$config['driver']}");
        }
    }

    private static function getDefaultConfig(): array
    {
        if (!empty(self::$config)) {
            return self::$config;
        }

        // Load from config file
        $configPath = __DIR__ . '/../../config/database.php';
        if (file_exists($configPath)) {
            return require $configPath;
        }

        throw new RuntimeException("Database configuration not found");
    }

    public static function beginTransaction(): bool
    {
        if (self::$transactionActive) {
            return false;
        }

        $result = self::getConnection()->beginTransaction();
        if ($result) {
            self::$transactionActive = true;
        }

        return $result;
    }

    public static function commit(): bool
    {
        if (!self::$transactionActive) {
            return false;
        }

        $result = self::getConnection()->commit();
        if ($result) {
            self::$transactionActive = false;
        }

        return $result;
    }

    public static function rollback(): bool
    {
        if (!self::$transactionActive) {
            return false;
        }

        $result = self::getConnection()->rollback();
        if ($result) {
            self::$transactionActive = false;
        }

        return $result;
    }

    public static function isTransactionActive(): bool
    {
        return self::$transactionActive;
    }

    public static function disconnect(): void
    {
        if (self::$transactionActive) {
            self::rollback();
        }
        
        self::$connection = null;
    }
}
