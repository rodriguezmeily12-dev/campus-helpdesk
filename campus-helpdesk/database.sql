-- Campus IT Help Desk - database setup
-- Import this file in phpMyAdmin (Import tab) or run it in the MySQL command line.
-- It creates the database and the table that stores the form submissions.

CREATE DATABASE IF NOT EXISTS helpdesk_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE helpdesk_db;

CREATE TABLE IF NOT EXISTS support_requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  NULL,
    user_type   VARCHAR(20)  NOT NULL,
    category    VARCHAR(50)  NOT NULL,
    priority    VARCHAR(10)  NOT NULL,
    subject     VARCHAR(150) NOT NULL,
    description TEXT         NOT NULL,
    status      VARCHAR(20)  NOT NULL DEFAULT 'Open',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
