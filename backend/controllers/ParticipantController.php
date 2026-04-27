<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../models/Participant.php';

use App\Models\Participant;
use PDO;

class ParticipantController
{
    private PDO $db;
    private Participant $model;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new Participant($db);
    }

    public function processRequest(string $method, string $action = ''): void
    {
        if ($action === 'status') {
            $this->checkStatus();
            return;
        }

        switch ($method) {
            case 'POST':
                $this->register();
                break;
            case 'GET':
                $this->list();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    private function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['event_id'], $data['name'], $data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        if ($this->model->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Registration successful']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    private function list(): void
    {
        $eventId = $_GET['event_id'] ?? '';
        echo json_encode($this->model->getByEvent($eventId));
    }

    private function checkStatus(): void
    {
        $email = $_GET['email'] ?? '';
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email required']);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT p.*, e.title as event_title 
            FROM participants p 
            JOIN events e ON p.event_id = e.id 
            WHERE p.email = ?
        ");
        $stmt->execute([$email]);
        echo json_encode($stmt->fetchAll());
    }
}
