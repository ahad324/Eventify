CREATE DATABASE IF NOT EXISTS eventify;
USE eventify;

CREATE TABLE IF NOT EXISTS events (
    id CHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS participants (
    id CHAR(36) PRIMARY KEY,
    event_id CHAR(36),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admins (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Default admin: admin / admin123
INSERT INTO admins (id, username, password) VALUES (
    UUID(), 
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
) ON DUPLICATE KEY UPDATE password=VALUES(password);

-- Sample Event
INSERT INTO events (id, title, description, event_date, location) VALUES (
    'e1234567-89ab-40cd-8ef0-1234567890ab', 
    'Tech Summit 2026', 
    'The premier university technology conference.', 
    '2026-06-15 09:00:00', 
    'Main Auditorium'
) ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Sample Participant
INSERT INTO participants (id, event_id, name, email, status) VALUES (
    UUID(),
    'e1234567-89ab-40cd-8ef0-1234567890ab',
    'John Doe',
    'john@example.com',
    'pending'
);
