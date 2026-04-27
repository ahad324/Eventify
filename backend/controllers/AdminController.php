<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../models/Admin.php';

use App\Models\Admin;
use PDO;

class AdminController
{
    private PDO $db;
    private Admin $adminModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->adminModel = new Admin($db);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function processRequest(string $method, array $uri): void
    {
        $action = $uri[count($uri) - 1] ?? '';

        switch ($action) {
            case 'login':
                if ($method === 'POST') $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'approve':
                if ($method === 'POST') $this->updateStatus('approved');
                break;
            case 'reject':
                if ($method === 'POST') $this->updateStatus('rejected');
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
                break;
        }
    }

    private function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $admin = $this->adminModel->getByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            echo json_encode(['message' => 'Login successful']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
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
}
