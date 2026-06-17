-- Fix: Disable RLS on all tables
-- The original migration (supabase_migration.sql) intentionally left RLS disabled
-- because the frontend handles auth via Supabase Auth + JWT.
-- The RLS policies created in supabase_rls_policies.sql cause type mismatches
-- between auth.uid() (UUID) and auth_id (bigint), blocking all queries.

-- Disable RLS on all tables
ALTER TABLE IF EXISTS public.users DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.chat_messages DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.embarques DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.cotacoes DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.documentos DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.visitors DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.contacts DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.news DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.services DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.gallery DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.partners DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.banners DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.testimonials DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.faqs DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.settings DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.companies DISABLE ROW LEVEL SECURITY;
ALTER TABLE IF EXISTS public.notifications DISABLE ROW LEVEL SECURITY;

-- Drop all RLS policies (clean up)
DO $$
DECLARE
  r RECORD;
BEGIN
  FOR r IN (
    SELECT schemaname, tablename, policyname
    FROM pg_policies
    WHERE schemaname = 'public'
  ) LOOP
    EXECUTE format('DROP POLICY IF EXISTS %I ON %I.%I', r.policyname, r.schemaname, r.tablename);
  END LOOP;
END $$;

-- Verify RLS is disabled
SELECT
  schemaname,
  tablename,
  rowsecurity
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY tablename;
