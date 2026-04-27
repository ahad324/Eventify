<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Admin
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }
}
