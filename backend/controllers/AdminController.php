<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Event.php';

use App\Models\Admin;
use App\Models\Event;
use PDO;

class AdminController
{
    private PDO $db;
    private Admin $adminModel;
    private Event $eventModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->adminModel = new Admin($db);
        $this->eventModel = new Event($db);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function processRequest(string $method, string $action): void
    {
        switch ($action) {
            case 'login':
                if ($method === 'POST') $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'participants':
                if ($method === 'GET') $this->listParticipants();
                break;
            case 'approve':
                if ($method === 'POST') $this->updateStatus('approved');
                break;
            case 'reject':
                if ($method === 'POST') $this->updateStatus('rejected');
                break;
            case 'events':
                if ($method === 'POST') $this->createEvent();
                if ($method === 'DELETE') $this->deleteEvent();
                break;
            case 'gallery':
                if ($method === 'POST') $this->uploadGallery();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
                break;
        }
    }

    private function uploadGallery(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            return;
        }

        $eventId = $_POST['event_id'] ?? '';
        if (isset($_FILES['images'])) {
            $files = $_FILES['images'];
            $count = count($files['name']);
            $success = 0;

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $url = $this->handleUpload($file);
                    if ($url && $this->eventModel->addGalleryImage($eventId, $url)) {
                        $success++;
                    }
                }
            }
            echo json_encode(['message' => "Successfully uploaded {$success} images"]);
        }
    }

    private function createEvent(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            return;
        }

        $data = $_POST;
        $banner_url = null;

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $banner_url = $this->handleUpload($_FILES['banner']);
        }

        $eventData = [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'event_date' => $data['event_date'] ?? '',
            'location' => $data['location'] ?? '',
            'banner_url' => $banner_url
        ];

        if ($this->eventModel->create($eventData)) {
            echo json_encode(['message' => 'Event created']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create event']);
        }
    }

    private function handleUpload(array $file): ?string
    {
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'backend/uploads/' . $filename;
        }

        return null;
    }

    private function deleteEvent(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            return;
        }

        $id = $_GET['id'] ?? '';
        if ($this->eventModel->delete($id)) {
            echo json_encode(['message' => 'Event deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete event']);
        }
    }

    private function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $admin = $this->adminModel->getByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            echo json_encode(['message' => 'Login successful', 'status' => 'success']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials', 'status' => 'error']);
        }
    }

    private function logout(): void
    {
        session_destroy();
        echo json_encode(['message' => 'Logged out']);
    }

    private function updateStatus(string $status): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['participant_id'] ?? '';

        $stmt = $this->db->prepare("UPDATE participants SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            echo json_encode(['message' => "Participant {$status}"]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
        }
    }

    private function listParticipants(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $stmt = $this->db->query("
            SELECT p.*, e.title as event_title 
            FROM participants p 
            JOIN events e ON p.event_id = e.id 
            ORDER BY p.registered_at DESC
        ");
        echo json_encode($stmt->fetchAll());
    }
}
