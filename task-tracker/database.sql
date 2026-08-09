-- =========================================================
-- Task Tracker Database Schema
-- =========================================================

CREATE DATABASE IF NOT EXISTS task_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE task_tracker;

-- ---------------------------------------------------------
-- USERS TABLE
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- CATEGORIES TABLE
-- ---------------------------------------------------------
CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY unique_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- TASKS TABLE
-- ---------------------------------------------------------
CREATE TABLE tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    status ENUM(
        'pending',
        'in_progress',
        'completed'
    ) NOT NULL DEFAULT 'pending',

    due_date DATE NULL,

    category_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_tasks_user_id (user_id),
    KEY idx_tasks_category_id (category_id),
    KEY idx_tasks_status (status),
    KEY idx_tasks_due_date (due_date),
    KEY idx_tasks_title (title),

    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- SAMPLE CATEGORIES (no plaintext passwords are ever inserted;
-- create your test account through /pages/register.php)
-- ---------------------------------------------------------
INSERT INTO categories (name) VALUES
    ('School'),
    ('Work'),
    ('Personal'),
    ('Projects');
