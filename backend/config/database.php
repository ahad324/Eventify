<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private string $host = 'localhost';
    private string $db_name = 'eventify';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn = null;

    public function connect(): ?PDO
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            // First attempt: Connect to the specific database
            $pdo = $this->tryConnect();
            
            // Check if tables are initialized (check for admins table)
            $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
            if ($stmt->rowCount() === 0) {
                $this->autoSetup();
                return $this->tryConnect();
            }
            return $pdo;
        } catch (PDOException $e) {
            // If database doesn't exist (Error 1049)
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                $this->autoSetup();
                return $this->tryConnect();
            }
            throw $e;
        }
    }

    private function tryConnect(): PDO
    {
        $this->conn = new PDO(
            "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
            $this->username,
            $this->password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $this->conn;
    }

    private function autoSetup(): void
    {
        try {
            $pdo = new PDO("mysql:host={$this->host}", $this->username, $this->password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}`");
            $pdo->exec("USE `{$this->db_name}`");

            $sqlFile = __DIR__ . '/../../database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) $pdo->exec($stmt);
                }
            }
        } catch (PDOException $e) {
            // Log or ignore if already setup
        }
    }
}
