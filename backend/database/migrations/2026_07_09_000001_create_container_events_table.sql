-- Migration: Create container_events table for tracking cache
-- Run this in Supabase SQL Editor

CREATE TABLE IF NOT EXISTS container_events (
  id BIGSERIAL PRIMARY KEY,
  container_number TEXT NOT NULL UNIQUE,
  carrier TEXT NOT NULL,
  events JSONB NOT NULL DEFAULT '[]',
  cached_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Index for cache lookup (6 hour TTL)
CREATE INDEX IF NOT EXISTS idx_container_events_number
  ON container_events (container_number);

CREATE INDEX IF NOT EXISTS idx_container_events_cached_at
  ON container_events (cached_at);

-- Enable RLS (allow public read, service role write)
ALTER TABLE container_events ENABLE ROW LEVEL SECURITY;

-- Public can read cached tracking data
CREATE POLICY "Public read container_events"
  ON container_events FOR SELECT
  USING (true);

-- Service role can insert/update
CREATE POLICY "Service write container_events"
  ON container_events FOR ALL
  USING (auth.role() = 'service_role');

-- Auto-cleanup old cache entries (older than 24 hours)
-- Run this as a Supabase cron job or manually
-- DELETE FROM container_events WHERE cached_at < NOW() - INTERVAL '24 hours';
