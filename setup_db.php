<?php

declare(strict_types=1);

require_once __DIR__ . '/backend/config/database.php';
use App\Config\Database;

try {
    $db = (new Database())->connect();

    // Create Tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS events (
            id CHAR(36) PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_date DATETIME NOT NULL,
            location VARCHAR(255),
            banner_url VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS event_gallery (
            id CHAR(36) PRIMARY KEY,
            event_id CHAR(36),
            image_url VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS participants (
            id CHAR(36) PRIMARY KEY,
            event_id CHAR(36),
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS admins (
            id CHAR(36) PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        )"
    ];

    foreach ($queries as $sql) {
        $db->exec($sql);
    }

    // Default admin: admin / admin123
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT IGNORE INTO admins (id, username, password) VALUES (UUID(), 'admin', ?)");
    $stmt->execute([$password]);

    // Sample Event
    $eventId = 'e1234567-89ab-40cd-8ef0-1234567890ab';
    $stmt = $db->prepare("INSERT IGNORE INTO events (id, title, description, event_date, location) VALUES (?, 'Tech Summit 2026', 'The premier university technology conference.', '2026-06-15 09:00:00', 'Main Auditorium')");
    $stmt->execute([$eventId]);

    echo "<div style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 0 auto; background: #f0f0f0; border-radius: 8px;'>";
    echo "<h1 style='color: #000;'>SETUP SUCCESSFUL</h1>";
    echo "<p>Database tables have been created and initialized.</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin / admin123</li>";
    echo "<li><strong>Sample Event:</strong> Tech Summit 2026</li>";
    echo "</ul>";
    echo "<a href='frontend/admin/index.html' style='display: inline-block; padding: 1rem 2rem; background: #000; color: #fff; text-decoration: none; font-weight: 900;'>GO TO ADMIN LOGIN</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
