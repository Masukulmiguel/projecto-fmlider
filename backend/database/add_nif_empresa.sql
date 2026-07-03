-- Add nif_empresa column to motoristas table
ALTER TABLE IF EXISTS public.motoristas ADD COLUMN IF NOT EXISTS nif_empresa VARCHAR(10);
