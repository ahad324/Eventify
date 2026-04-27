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
            
            // Comprehensive Schema Check
            $needsSetup = false;
            
            // Check for event_gallery table
            $stmt = $pdo->query("SHOW TABLES LIKE 'event_gallery'");
            if ($stmt->rowCount() === 0) $needsSetup = true;

            // Check for banner_url in events
            if (!$needsSetup) {
                $columns = $pdo->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('banner_url', $columns)) $needsSetup = true;
            }

            if ($needsSetup) {
                $this->autoSetup();
                $this->conn = null; // Reset connection for fresh state
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

            // Check for missing banner_url specifically
            $stmt = $pdo->query("SHOW TABLES LIKE 'events'");
            if ($stmt->rowCount() > 0) {
                $columns = $pdo->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array('banner_url', $columns)) {
                    $pdo->exec("ALTER TABLE events ADD COLUMN banner_url VARCHAR(255) DEFAULT NULL AFTER location");
                }
            }

            // Standard setup for tables
            $sqlFile = __DIR__ . '/../../database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        // Skip INSERTs if tables have data to avoid duplicates
                        if (stripos($stmt, 'INSERT INTO') === 0) continue;
                        $pdo->exec($stmt);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("AutoSetup Error: " . $e->getMessage());
        }
    }
}
