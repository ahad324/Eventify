<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Participant
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO participants (id, event_id, name, email, status) 
                VALUES (:id, :event_id, :name, :email, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => bin2hex(random_bytes(18)), // Simple UUID-like id
            'event_id' => $data['event_id'],
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }

    public function getByEvent(string $eventId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM participants WHERE event_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
}
