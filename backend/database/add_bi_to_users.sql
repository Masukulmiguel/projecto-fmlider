-- Add bi column to users table for funcionarios
ALTER TABLE IF EXISTS public.users ADD COLUMN IF NOT EXISTS bi VARCHAR(20);
CREATE INDEX IF NOT EXISTS idx_users_bi ON public.users(bi) WHERE bi IS NOT NULL;
