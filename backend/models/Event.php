<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Event
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM events ORDER BY event_date ASC");
        return $stmt->fetchAll();
    }

    public function getById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
