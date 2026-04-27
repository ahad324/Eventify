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

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO events (id, title, description, event_date, location, banner_url) 
            VALUES (UUID(), ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['location'],
            $data['banner_url'] ?? null
        ]);
    }

    public function update(string $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE events 
            SET title = ?, description = ?, event_date = ?, location = ?, banner_url = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['location'],
            $data['banner_url'] ?? null,
            $id
        ]);
    }

    public function addGalleryImage(string $eventId, string $imageUrl): bool
    {
        $stmt = $this->db->prepare("INSERT INTO event_gallery (id, event_id, image_url) VALUES (UUID(), ?, ?)");
        return $stmt->execute([$eventId, $imageUrl]);
    }

    public function getGallery(string $eventId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM event_gallery WHERE event_id = ? ORDER BY created_at DESC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function delete(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
