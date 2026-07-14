-- Seed para imagens de autenticação (login, register, etc.)
-- Executar no Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

INSERT INTO site_images (section, key, image_url, alt_text, status) VALUES
('auth', 'login_bg_1', '/assets/img/logo.png', 'Login Background 1 - Logo', 1),
('auth', 'login_bg_2', '/assets/img/construcao2020/image1.jpeg', 'Login Background 2', 1),
('auth', 'login_bg_3', '/assets/img/construcao2020/image2.jpeg', 'Login Background 3', 1),
('auth', 'reset_bg', 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=1920&q=80', 'Reset Password Background', 1)
ON CONFLICT (section, key) DO NOTHING;
