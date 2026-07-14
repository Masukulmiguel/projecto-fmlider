-- ============================================================
-- COMPLETE FIX: site_images table + RLS + seed data
-- Execute ALL of this in Supabase SQL Editor
-- https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new
-- ============================================================

-- 1. Create table if not exists
CREATE TABLE IF NOT EXISTS site_images (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    section varchar(50) NOT NULL,
    key varchar(100) NOT NULL,
    image_url text,
    alt_text varchar(255),
    status smallint DEFAULT 1,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    UNIQUE(section, key)
);

CREATE INDEX IF NOT EXISTS idx_site_images_section ON site_images(section);
CREATE INDEX IF NOT EXISTS idx_site_images_section_key ON site_images(section, key);

-- 2. Enable RLS and create policies
ALTER TABLE site_images ENABLE ROW LEVEL SECURITY;

-- Public read (anyone can see images)
DROP POLICY IF EXISTS "public_read_site_images" ON site_images;
CREATE POLICY "public_read_site_images" ON site_images
  FOR SELECT USING (true);

-- Authenticated write
DROP POLICY IF EXISTS "authenticated_write_site_images" ON site_images;
CREATE POLICY "authenticated_write_site_images" ON site_images
  FOR ALL USING (auth.role() = 'authenticated') WITH CHECK (auth.role() = 'authenticated');

-- Service role full access
DROP POLICY IF EXISTS "service_role_all_site_images" ON site_images;
CREATE POLICY "service_role_all_site_images" ON site_images
  FOR ALL USING (auth.role() = 'service_role');

-- 3. Seed: Auth backgrounds
INSERT INTO site_images (section, key, image_url, alt_text, status) VALUES
('auth', 'login_bg_1', '/assets/img/auth/bg1.jpg', 'Login 1', 1),
('auth', 'login_bg_2', '/assets/img/auth/bg2.jpg', 'Login 2', 1),
('auth', 'login_bg_3', '/assets/img/auth/bg3.jpg', 'Login 3', 1),
('auth', 'reset_bg', '/assets/img/auth/reset_bg.jpg', 'Reset Password', 1),
('auth', 'forgot_bg', '/assets/img/auth/bg3.jpg', 'Forgot Password', 1)
ON CONFLICT (section, key) DO NOTHING;

-- 4. Seed: Service detail backgrounds
INSERT INTO site_images (section, key, image_url, alt_text, status) VALUES
('service_detail', 'desembaraco-aduaneiro', '/assets/img/servico/Desembaraço Aduaneiro.jpeg', 'Desembaraço Aduaneiro', 1),
('service_detail', 'transportes', '/assets/img/servico/Transportes.jpg', 'Transportes', 1),
('service_detail', 'armazenagem', '/assets/img/servico/service-storage.jpg', 'Armazenagem', 1),
('service_detail', 'door-to-door', '/assets/img/servico/service-door.jpg', 'Door To Door', 1),
('service_detail', 'logistica-maritima', '/assets/img/servico/Logística Marítima-1.jpg', 'Logística Marítima', 1)
ON CONFLICT (section, key) DO NOTHING;

-- ============================================================
-- DONE
-- ============================================================
