-- Migration: 2024_01_06_000001_chat_sender_nullable.sql
-- Allows NULL sender_id on chat_messages so admin -> user messages
-- can be stored as (sender_id = NULL, receiver_id = user_id)

ALTER TABLE chat_messages MODIFY COLUMN sender_id BIGINT UNSIGNED NULL;
