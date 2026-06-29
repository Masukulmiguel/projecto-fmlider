-- Fix: Add missing columns to licenciamentos table
-- Run this in Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

-- Add cliente_nome column
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS cliente_nome VARCHAR(255);

-- Add tipo column (separate from tipo_licenciamento)
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS tipo VARCHAR(100);

-- Add funcionario_responsavel column (name text, not just id)
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS funcionario_responsavel VARCHAR(255);

-- Reload PostgREST schema cache
NOTIFY pgrst, 'reload schema';
