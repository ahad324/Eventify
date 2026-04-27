<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
use App\Config\Database;

try {
    $db = (new Database())->connect();
    
    // admin / admin123
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$password]);

    if ($stmt->rowCount() === 0) {
        // If user doesn't exist, create it
        $stmt = $db->prepare("INSERT INTO admins (id, username, password) VALUES (UUID(), 'admin', ?)");
        $stmt->execute([$password]);
    }

    echo "SUCCESS: Admin password has been reset to 'admin123'.<br>";
    echo "<a href='../frontend/admin/index.html'>Go to Login</a>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
