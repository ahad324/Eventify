<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

class CertificateController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function processRequest(string $method): void
    {
        if ($method !== 'GET') {
            http_response_code(405);
            return;
        }

        $id = $_GET['id'] ?? '';
        
        $stmt = $this->db->prepare("
            SELECT p.name, p.status, e.title as event_title 
            FROM participants p
            JOIN events e ON p.event_id = e.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data || $data['status'] !== 'approved') {
            http_response_code(403);
            echo json_encode(['error' => 'Certificate not available or unauthorized']);
            return;
        }

        echo json_encode($data);
    }
}
