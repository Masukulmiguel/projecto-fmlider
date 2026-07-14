-- ============================================================
-- FIX: RLS policies for site_images table
-- Allow public (anonymous) read access for auth background images
-- Execute in Supabase SQL Editor
-- ============================================================

-- 1. Enable RLS (should already be enabled)
ALTER TABLE site_images ENABLE ROW LEVEL SECURITY;

-- 2. Drop existing read policy if any
DROP POLICY IF EXISTS "public_read_site_images" ON site_images;

-- 3. Allow EVERYONE (including anonymous) to read site_images
CREATE POLICY "public_read_site_images" ON site_images
  FOR SELECT
  USING (status = 1);

-- 4. Allow authenticated users to insert/update
DROP POLICY IF EXISTS "authenticated_write_site_images" ON site_images;
CREATE POLICY "authenticated_write_site_images" ON site_images
  FOR ALL
  USING (auth.role() = 'authenticated')
  WITH CHECK (auth.role() = 'authenticated');

-- 5. Allow service_role full access
DROP POLICY IF EXISTS "service_role_all_site_images" ON site_images;
CREATE POLICY "service_role_all_site_images" ON site_images
  FOR ALL
  USING (auth.role() = 'service_role');
