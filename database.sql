-- Equipment Alarm System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS equipment_alarm_system;
USE equipment_alarm_system;

-- Equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('Voltage', 'Current', 'Oil') NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Alarms table
CREATE TABLE IF NOT EXISTS alarms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    classification ENUM('Urgent', 'Emergency', 'Ordinary') NOT NULL,
    equipment_id INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);

-- Activated Alarms table
CREATE TABLE IF NOT EXISTS activated_alarms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alarm_id INT NOT NULL,
    entry_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    exit_date DATETIME NULL,
    status ENUM('on', 'off') DEFAULT 'on',
    trigger_count INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (alarm_id) REFERENCES alarms(id) ON DELETE CASCADE
);

-- System Logs table
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    user_ip VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Add some test data
INSERT INTO equipment (name, serial_number, type) VALUES
('Power Generator', 'PG12345', 'Voltage'),
('Current Sensor', 'CS54321', 'Current'),
('Oil Transformer', 'OT98765', 'Oil');

INSERT INTO alarms (description, classification, equipment_id) VALUES
('High Voltage Detected', 'Urgent', 1),
('Low Current Warning', 'Ordinary', 2),
('Oil Level Critical', 'Emergency', 3),
('Temperature Warning', 'Ordinary', 1); 