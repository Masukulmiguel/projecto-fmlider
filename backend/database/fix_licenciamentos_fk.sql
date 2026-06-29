-- Fix: Drop ALL foreign key constraints from licenciamentos tables
-- This fixes the "could not find a relationship between 'licenciamentos' and 'clientes'" error
-- Run this in Supabase SQL Editor

-- 1. Drop all FK constraints on licenciamentos
DO $$ DECLARE
  r RECORD;
BEGIN
  FOR r IN (
    SELECT conname
    FROM pg_constraint
    WHERE conrelid = 'public.licenciamentos'::regclass
    AND contype = 'f'
  ) LOOP
    EXECUTE 'ALTER TABLE public.licenciamentos DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
    RAISE NOTICE 'Dropped FK: %', r.conname;
  END LOOP;
END $$;

-- 2. Drop all FK constraints on licenciamento_historico
DO $$ DECLARE
  r RECORD;
BEGIN
  FOR r IN (
    SELECT conname
    FROM pg_constraint
    WHERE conrelid = 'public.licenciamento_historico'::regclass
    AND contype = 'f'
  ) LOOP
    EXECUTE 'ALTER TABLE public.licenciamento_historico DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
    RAISE NOTICE 'Dropped FK: %', r.conname;
  END LOOP;
END $$;

-- 3. Drop all FK constraints on licenciamento_estados_historico
DO $$ DECLARE
  r RECORD;
BEGIN
  FOR r IN (
    SELECT conname
    FROM pg_constraint
    WHERE conrelid = 'public.licenciamento_estados_historico'::regclass
    AND contype = 'f'
  ) LOOP
    EXECUTE 'ALTER TABLE public.licenciamento_estados_historico DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
    RAISE NOTICE 'Dropped FK: %', r.conname;
  END LOOP;
END $$;

-- 4. Also ensure RLS is disabled
ALTER TABLE public.licenciamentos DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_historico DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_estados_historico DISABLE ROW LEVEL SECURITY;

-- 5. Reload PostgREST schema cache
NOTIFY pgrst, 'reload schema';
