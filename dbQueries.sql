CREATE DATABASE sarpa;

USE sarpa;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('super_admin', 'user', 'partner') NOT NULL DEFAULT 'user',
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    address TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE snake_sightings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    snake_type VARCHAR(100),
    description TEXT,
    status ENUM('reported', 'accepted', 'captured') DEFAULT 'reported',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at INT NOT NULL,
    created_at INT DEFAULT UNIX_TIMESTAMP(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE sarpa.login_attempts ADD COLUMN unlock_time INT NULL;