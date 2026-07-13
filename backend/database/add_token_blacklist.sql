-- Add token_blacklisted_at column for logout invalidation (PostgreSQL/Supabase)
ALTER TABLE users ADD COLUMN token_blacklisted_at TIMESTAMPTZ DEFAULT NULL;
