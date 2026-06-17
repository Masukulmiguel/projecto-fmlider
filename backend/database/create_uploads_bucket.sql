-- ============================================================
-- CREATE 'uploads' STORAGE BUCKET + POLICIES
-- Execute this in Supabase SQL Editor
-- ============================================================

-- 1. Create the 'uploads' bucket (public)
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES ('uploads', 'uploads', true, 10485760, ARRAY['image/jpeg','image/png','image/gif','image/webp','image/svg+xml','application/pdf'])
ON CONFLICT (id) DO NOTHING;

-- 2. Public read for uploads bucket
DO $$ BEGIN DROP POLICY IF EXISTS "public_read_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "public_read_uploads" ON storage.objects
  FOR SELECT USING (bucket_id = 'uploads');

-- 3. Authenticated users can upload to uploads bucket
DO $$ BEGIN DROP POLICY IF EXISTS "authenticated_upload_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "authenticated_upload_uploads" ON storage.objects
  FOR INSERT WITH CHECK (
    bucket_id = 'uploads'
    AND auth.role() = 'authenticated'
  );

-- 4. Admins can delete from uploads bucket
DO $$ BEGIN DROP POLICY IF EXISTS "admin_delete_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "admin_delete_uploads" ON storage.objects
  FOR DELETE USING (
    bucket_id = 'uploads'
    AND public.get_user_role() = 'admin'
  );

-- 5. Users can update own files
DO $$ BEGIN DROP POLICY IF EXISTS "user_update_own_uploads" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "user_update_own_uploads" ON storage.objects
  FOR UPDATE USING (
    bucket_id = 'uploads'
    AND auth.uid()::text = (storage.foldername(name))[1]
  );
