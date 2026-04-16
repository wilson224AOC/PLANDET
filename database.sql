CREATE DATABASE IF NOT EXISTS meeting_system;
USE meeting_system;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE, -- Código único de solicitud
    dni VARCHAR(20) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    area VARCHAR(20),
    correo VARCHAR(40) NOT NULL,
    motivo TEXT,
    requested_date DATE DEFAULT NULL, -- Fecha sugerida por el usuario (opcional)
    scheduled_start DATETIME DEFAULT NULL, -- Hora asignada inicio
    scheduled_end DATETIME DEFAULT NULL, -- Hora asignada fin
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meeting_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    channel VARCHAR(30) NOT NULL,
    event_type VARCHAR(30) NOT NULL,
    status VARCHAR(20) NOT NULL,
    recipient VARCHAR(150) NOT NULL,
    message_body TEXT NOT NULL,
    provider_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_meeting_notifications_meeting
        FOREIGN KEY (meeting_id) REFERENCES meetings(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS allowed_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_token VARCHAR(64) NOT NULL UNIQUE,
    description VARCHAR(255),
    is_approved BOOLEAN DEFAULT FALSE,
    last_accessed TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar admin por defecto (Usuario: admin, Password: admin123)
-- El hash generado para 'admin123' usando PASSWORD_DEFAULT
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$T36oKx9kA1qxbgpLaDnXR.uSvRDMCt3.fIxsoftwrZCfNdycMts46'); 
-- Usuario secundario con hash correcto para 'admin123'
INSERT INTO admins (username, password) VALUES ('admin2', '$2y$10$T36oKx9kA1qxbgpLaDnXR.uSvRDMCt3.fIxsoftwrZCfNdycMts46'); 
-- Nota: Reemplazaremos esto con un hash real en el setup o instruiremos al usuario.
