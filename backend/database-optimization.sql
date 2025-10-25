-- Database Optimization for Task Management System
-- Performance indexes and constraints for production use

USE task_management;

-- Add indexes for performance optimization
-- Index on email for fast user lookups during authentication
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Index on due_date for efficient task sorting and filtering
CREATE INDEX IF NOT EXISTS idx_tasks_due_date ON tasks(due_date);

-- Index on assignee_id for fast task filtering by user
CREATE INDEX IF NOT EXISTS idx_tasks_assignee_id ON tasks(assignee_id);

-- Index on creator_id for creator-based queries
CREATE INDEX IF NOT EXISTS idx_tasks_creator_id ON tasks(creator_id);

-- Index on is_completed for status filtering
CREATE INDEX IF NOT EXISTS idx_tasks_completed ON tasks(is_completed);

-- Index on priority for priority-based filtering
CREATE INDEX IF NOT EXISTS idx_tasks_priority ON tasks(priority);

-- Composite index for common query patterns
CREATE INDEX IF NOT EXISTS idx_tasks_assignee_due ON tasks(assignee_id, due_date);
CREATE INDEX IF NOT EXISTS idx_tasks_assignee_status ON tasks(assignee_id, is_completed);

-- Add created_at and updated_at columns if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE tasks 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add constraints for data integrity
ALTER TABLE tasks 
ADD CONSTRAINT IF NOT EXISTS chk_priority CHECK (priority IN ('low', 'medium', 'high'));

-- Optimize table storage
OPTIMIZE TABLE users;
OPTIMIZE TABLE tasks;

-- Show index information
SHOW INDEX FROM users;
SHOW INDEX FROM tasks;
