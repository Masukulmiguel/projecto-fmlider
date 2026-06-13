-- Migration: 2024_01_05_000001_add_password_policy_and_lock.sql
-- Adds: password must-change policy + account lock state for funcionarios

ALTER TABLE users
    ADD COLUMN password_must_change TINYINT(1) NOT NULL DEFAULT 0 AFTER password,
    ADD COLUMN password_changed_at TIMESTAMP NULL AFTER password_must_change,
    ADD COLUMN password_expires_at TIMESTAMP NULL AFTER password_changed_at,
    ADD COLUMN locked_at TIMESTAMP NULL AFTER password_expires_at,
    ADD COLUMN locked_reason VARCHAR(255) NULL AFTER locked_at,
    ADD INDEX idx_pwd_change (password_must_change, password_changed_at),
    ADD INDEX idx_locked (locked_at);
