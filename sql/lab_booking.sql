-- 1️⃣ Create database
CREATE DATABASE IF NOT EXISTS labbooking;
USE labbooking;

-- 2️⃣ Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3️⃣ Admin keys table
CREATE TABLE IF NOT EXISTS admin_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hashed_key VARCHAR(255) NOT NULL,
    is_used TINYINT(1) DEFAULT 0
);

-- 4️⃣ Equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Optional: sample equipment entries
INSERT INTO equipment (name, description) VALUES
('Microscope','Optical microscope for biology lab'),
('Oscilloscope','Digital oscilloscope for electronics lab'),
('3D Printer','Maker lab 3D printing machine'),
('CNC Machine','Computer-controlled cutting machine');

-- 5️⃣ Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS booking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_booking_id INT,
    user_id INT,
    equipment_id INT,
    date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME,
    moved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (original_booking_id),
    INDEX (user_id),
    INDEX (equipment_id),
    CONSTRAINT fk_bh_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_bh_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL
);
