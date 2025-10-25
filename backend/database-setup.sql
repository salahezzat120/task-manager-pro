-- Task Management Database Setup
-- Run this in phpMyAdmin

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS task_management;
USE task_management;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  creator_id bigint unsigned NOT NULL,
  assignee_id bigint unsigned NOT NULL,
  title varchar(255) NOT NULL,
  description text,
  due_date date NOT NULL,
  priority enum('low','medium','high') NOT NULL DEFAULT 'medium',
  is_completed tinyint(1) NOT NULL DEFAULT '0',
  completed_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY tasks_creator_id_foreign (creator_id),
  KEY tasks_assignee_id_foreign (assignee_id),
  CONSTRAINT tasks_assignee_id_foreign FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT tasks_creator_id_foreign FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO users (email, password, created_at, updated_at) VALUES 
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert sample tasks
INSERT INTO tasks (creator_id, assignee_id, title, description, due_date, priority, created_at, updated_at) VALUES 
(1, 2, 'Complete project documentation', 'Write comprehensive documentation for the task management system', '2024-12-31', 'high', NOW(), NOW()),
(1, 2, 'Setup database', 'Configure MySQL database with proper tables and relationships', '2024-12-25', 'medium', NOW(), NOW()),
(2, 1, 'Review code', 'Review the Laravel backend code for any issues', '2024-12-20', 'low', NOW(), NOW());


