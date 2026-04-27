<?php
/**
 * Eventify One-Hit Setup Script
 * Handles database setup, admin creation, and folder structure.
 */

require_once __DIR__ . '/backend/config/database.php';

use App\Config\Database;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Eventify Setup</title>
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: 'Inter', system-ui, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .setup-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            backdrop-filter: blur(10px);
        }
        h1 { margin-top: 0; font-size: 1.5rem; color: #fff; letter-spacing: 2px; }
        .log-entry { margin: 10px 0; font-size: 0.9rem; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.05); }
        .success { border-left: 4px solid #00ff88; }
        .error { border-left: 4px solid #ff4444; }
        .info { border-left: 4px solid #00d4ff; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #fff;
            color: #000;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255,255,255,0.1); }
    </style>
</head>
<body>
<div class='setup-card'>
    <h1>EVENTIFY SETUP</h1>";

function log_message($msg, $type = 'info') {
    echo "<div class='log-entry $type'>$msg</div>";
}

try {
    // 1. Create Uploads Directory
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        if (mkdir($uploadsDir, 0777, true)) {
            log_message("Created uploads directory.", "success");
        } else {
            throw new Exception("Failed to create uploads directory.");
        }
    } else {
        log_message("Uploads directory already exists.", "info");
    }

    // 2. Database Connection and Auto-Setup
    log_message("Connecting to database...", "info");
    $dbConfig = new Database();
    $conn = $dbConfig->connect();
    
    if ($conn) {
        log_message("Database initialized and schema verified.", "success");
        log_message("Setup complete! You can now use the system.", "success");
        echo "<a href='frontend/admin/index.html' class='btn'>Go to Admin Login</a>";
        echo " <a href='frontend/index.html' class='btn' style='background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.2);'>Go to Home</a>";
    }

} catch (Exception $e) {
    log_message("Error: " . $e->getMessage(), "error");
    log_message("Please ensure MySQL is running and database 'eventify' exists or can be created.", "info");
}

echo "</div>
</body>
</html>";
