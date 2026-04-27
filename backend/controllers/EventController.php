<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../models/Event.php';

use App\Models\Event;
use PDO;

class EventController
{
    private PDO $db;
    private Event $eventModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->eventModel = new Event($db);
    }

    public function processRequest(string $method, string $action = ''): void
    {
        if ($action === 'gallery') {
            $this->getGallery();
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getAllEvents();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    private function getAllEvents(): void
    {
        $events = $this->eventModel->getAll();
        echo json_encode($events);
    }

    private function getGallery(): void
    {
        $eventId = $_GET['event_id'] ?? '';
        if (empty($eventId)) {
            http_response_code(400);
            return;
        }
        echo json_encode($this->eventModel->getGallery($eventId));
    }
}
