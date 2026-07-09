-- Migration: Add tracking fields to processos table
-- Run this in Supabase SQL Editor

-- Add tracking status field (mirrors contentores.estado values)
ALTER TABLE processos
  ADD COLUMN IF NOT EXISTS tracking_status VARCHAR(30) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS tracking_events JSONB DEFAULT '[]',
  ADD COLUMN IF NOT EXISTS tracking_updated_at TIMESTAMPTZ DEFAULT NULL;

-- Add index for tracking queries
CREATE INDEX IF NOT EXISTS idx_processos_tracking_status
  ON processos (tracking_status);

-- Comment the columns
COMMENT ON COLUMN processos.tracking_status IS 'Container tracking status mapped from carrier events (aguardando_chegada, chegou_ao_porto, em_terminal, na_base, agendado_para_entrega, em_transporte, entregue, em_transito, devolvido)';
COMMENT ON COLUMN processos.tracking_events IS 'Cached carrier tracking events (JSONB array)';
COMMENT ON COLUMN processos.tracking_updated_at IS 'Last tracking data refresh timestamp';
