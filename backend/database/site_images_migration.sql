-- ============================================================
-- FMLider - site_images table
-- Run this SQL in Supabase SQL Editor to create the table
-- ============================================================

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

-- Indexes
CREATE INDEX IF NOT EXISTS idx_site_images_section ON site_images(section);
CREATE INDEX IF NOT EXISTS idx_site_images_section_key ON site_images(section, key);

-- Trigger for updated_at
DROP TRIGGER IF EXISTS update_site_images_updated_at ON site_images;
CREATE TRIGGER update_site_images_updated_at BEFORE UPDATE ON site_images FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================
-- Seed data: default images (matching current hardcoded paths)
-- ============================================================
INSERT INTO site_images (section, key, image_url, alt_text) VALUES
('header', 'logo', '/assets/img/logo.png', 'FMLider Logo'),
('footer', 'logo', '/assets/img/logo.png', 'FMLider Logo'),
('sidebar', 'admin_logo', '/assets/img/logo.png', 'FMLider Logo'),
('sidebar', 'funcionario_logo', '/assets/img/logo.png', 'FMLider Logo'),
('sidebar', 'cliente_logo', '/assets/img/logo.png', 'FMLider Logo'),
('home', 'hero_bg', '/assets/img/construcao2020/image1.jpeg', 'FMLider Hero Background'),
('home', 'fleet_image', '/assets/img/resachstacker/resachstacker1.jpeg', 'Reachstacker FMLider'),
('about', 'hero_bg', '/assets/img/construcao2020/image1.jpeg', 'Sobre FMLider'),
('about', 'history_image', '/assets/img/construcao2020/image2.jpeg', 'FMLider Base'),
('about', 'infra_image', '/assets/img/construcao2020/image3.jpeg', 'Armazém FMLider'),
('about', 'fleet_image', '/assets/img/resachstacker/resachstacker2.jpeg', 'Frota FMLider'),
('fleet', 'hero_bg', '/assets/img/resachstacker/resachstacker1.jpeg', 'Frota FMLider'),
('fleet', 'highlight_image', '/assets/img/resachstacker/resachstacker1.jpeg', 'Reachstacker Kalmar'),
('services', 'hero_bg', '/assets/img/construcao2020/image4.jpeg', 'Serviços FMLider'),
('services', 'why_image', '/assets/img/pessoal/partner1.webp', 'FMLider Equipa'),
('news', 'hero_bg', '/assets/img/construcao2020/image3.jpeg', 'Notícias FMLider'),
('gallery', 'hero_bg', '/assets/img/construcao2020/image1.jpeg', 'Galeria FMLider')
ON CONFLICT (section, key) DO NOTHING;

-- ============================================================
-- END
-- ============================================================
