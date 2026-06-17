-- ============================================================
-- FIX ALL: Banners RLS + Storage RLS
-- Execute in Supabase SQL Editor
-- ============================================================

-- 1. Fix banners table RLS
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_banners" ON public.banners; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "authenticated_all_banners" ON public.banners; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_banners" ON public.banners; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_banners" ON public.banners
  FOR SELECT USING (true);

CREATE POLICY "authenticated_all_banners" ON public.banners
  FOR ALL USING (auth.uid() IS NOT NULL);

-- 2. Fix uploads storage RLS
DO $$ BEGIN DROP POLICY IF EXISTS "public_read_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "authenticated_upload_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_delete_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_update_own_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_read_uploads" ON storage.objects
  FOR SELECT USING (bucket_id = 'uploads');

CREATE POLICY "authenticated_upload_uploads" ON storage.objects
  FOR INSERT WITH CHECK (
    bucket_id = 'uploads'
    AND auth.uid() IS NOT NULL
  );

CREATE POLICY "authenticated_update_uploads" ON storage.objects
  FOR UPDATE USING (
    bucket_id = 'uploads'
    AND auth.uid() IS NOT NULL
  );

CREATE POLICY "authenticated_delete_uploads" ON storage.objects
  FOR DELETE USING (
    bucket_id = 'uploads'
    AND auth.uid() IS NOT NULL
  );
