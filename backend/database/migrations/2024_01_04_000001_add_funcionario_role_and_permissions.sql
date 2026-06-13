-- Migration: 2024_01_04_000001_add_funcionario_role_and_permissions.sql
-- Adds: 'funcionario' role and per-user permissions JSON column

ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'cliente', 'funcionario') DEFAULT 'cliente';

ALTER TABLE users
    ADD COLUMN permissions JSON NULL AFTER role,
    ADD COLUMN position VARCHAR(100) NULL AFTER permissions,
    ADD INDEX idx_role (role);
