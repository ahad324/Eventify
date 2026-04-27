<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/EventController.php';
require_once __DIR__ . '/../controllers/ParticipantController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/CertificateController.php';

use App\Config\Database;
use App\Controllers\EventController;
use App\Controllers\ParticipantController;
use App\Controllers\AdminController;
use App\Controllers\CertificateController;

try {
    $database = new Database();
    $db = $database->connect();
} catch (\Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

// Robust Routing
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $requestUri);
$apiPos = array_search('index.php', $pathParts);

if ($apiPos === false) {
    http_response_code(404);
    echo json_encode(['error' => 'API Endpoint not found']);
    exit;
}

$resource = $pathParts[$apiPos + 1] ?? '';
$subResource = $pathParts[$apiPos + 2] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($resource === 'admin') {
    $controller = new AdminController($db);
    $controller->processRequest($method, $subResource);
} else {
    switch ($resource) {
        case 'events':
            $controller = new EventController($db);
            $controller->processRequest($method, $subResource);
            break;

        case 'participants':
            $controller = new ParticipantController($db);
            $controller->processRequest($method, $subResource);
            break;

        case 'certificate':
            $controller = new CertificateController($db);
            $controller->processRequest($method);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => "Resource '$resource' not found"]);
            break;
    }
}
