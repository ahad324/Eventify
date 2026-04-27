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

    private function ensureAuthenticated(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public function processRequest(string $method, string $action): void
    {
        // Admin actions require authentication
        if ($action !== 'login') {
            $this->ensureAuthenticated();
        }

        switch ($action) {
            case 'login':
                if ($method === 'POST') $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'me':
                $this->getMe();
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
                if ($method === 'POST') {
                    $this->createEvent();
                } else if ($method === 'GET') {
                    // Reuse public event controller logic or direct model call
                    require_once __DIR__ . '/../models/Event.php';
                    $eventModel = new \App\Models\Event($this->db);
                    echo json_encode($eventModel->getAll());
                }
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
                        'size' => $files['size'][$i],
                        'error' => $files['error'][$i]
                    ];
                    try {
                        $url = $this->handleUpload($file);
                        if ($url && $this->eventModel->addGalleryImage($eventId, $url)) {
                            $success++;
                        }
                    } catch (\Exception $e) {
                        error_log("Gallery Upload Error: " . $e->getMessage());
                    }
                }
            }
            echo json_encode(['message' => "Successfully uploaded {$success} images"]);
        }
    }

    private function createEvent(): void
    {
        try {
            $data = $_POST;
            $banner_url = null;

            if (empty($data['title']) || empty($data['event_date'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and Date are required']);
                return;
            }

            if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
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
                echo json_encode(['message' => 'Event created', 'status' => 'success']);
            } else {
                throw new \Exception("Failed to create event in database.");
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage(), 'status' => 'error']);
        }
    }

    private function handleUpload(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE in HTML form',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
            ];
            throw new \Exception($errors[$file['error']] ?? 'Unknown upload error');
        }

        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0777, true)) {
                throw new \Exception("Server configuration error: Cannot create upload directory. Please check permissions.");
            }
        }

        if (!is_writable($uploadDir)) {
            throw new \Exception("Server configuration error: Upload directory is not writable.");
        }

        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $fileName;
        }

        throw new \Exception("Failed to save uploaded file. Check folder permissions.");
    }

    private function deleteEvent(): void
    {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Event ID is required']);
            return;
        }

        if ($this->eventModel->delete($id)) {
            echo json_encode(['message' => 'Event deleted', 'status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete event', 'status' => 'error']);
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

    private function getMe(): void
    {
        if (isset($_SESSION['admin_id'])) {
            echo json_encode(['id' => $_SESSION['admin_id']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
        }
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
