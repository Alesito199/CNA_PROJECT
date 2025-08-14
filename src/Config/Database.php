<?php

namespace CNA\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static ?Database $instance = null;

    private function __construct() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            $this->connect();
        }
        return self::$connection;
    }

    private function connect(): void
    {
        try {
            $host = Config::get('database.host');
            $port = Config::get('database.port');
            $database = Config::get('database.database');
            $username = Config::get('database.username');
            $password = Config::get('database.password');

            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];

            self::$connection = new PDO($dsn, $username, $password, $options);
            
            // Set timezone
            self::$connection->exec("SET timezone = '" . Config::get('app.timezone', 'UTC') . "'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new \RuntimeException("Query failed: " . $e->getMessage());
        }
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ") RETURNING id";
        
        $stmt = $this->query($sql, $data);
        $result = $stmt->fetch();
        
        return $result['id'];
    }

    public function update(string $table, array $data, array $where): int
    {
        $setClause = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        $whereClause = array_map(fn($col) => "{$col} = :where_{$col}", array_keys($where));
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        
        // Prefix where parameters to avoid conflicts
        $whereParams = array_combine(
            array_map(fn($key) => 'where_' . $key, array_keys($where)),
            array_values($where)
        );
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = array_map(fn($col) => "{$col} = :{$col}", array_keys($where));
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    public function tableExists(string $table): bool
    {
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = :table
        )";
        
        $result = $this->fetch($sql, ['table' => $table]);
        return $result['exists'] ?? false;
    }
}