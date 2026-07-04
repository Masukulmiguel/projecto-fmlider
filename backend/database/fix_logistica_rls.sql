-- Fix: Disable RLS on logistics tables (app has its own auth)
-- The RLS policies using auth.jwt() ->> 'role' don't work because
-- the admin JWT doesn't contain a 'role' claim in Supabase.

ALTER TABLE IF EXISTS public.motoristas DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.camioes DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.entregas DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.contentores DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.historico_entregas DISABLE ROW LEVEL SECURITY;
