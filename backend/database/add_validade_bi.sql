-- Add validade_bi column to motoristas table
ALTER TABLE IF EXISTS public.motoristas ADD COLUMN IF NOT EXISTS validade_bi DATE;
