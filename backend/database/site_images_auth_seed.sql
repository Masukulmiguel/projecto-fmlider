-- Seed para imagens de autenticação (login, register, etc.)
-- Executar no Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

INSERT INTO site_images (section, key, image_url, alt_text, status) VALUES
('auth', 'login_bg_1', '/assets/img/auth/bg1.jpg', 'Login Background 1', 1),
('auth', 'login_bg_2', '/assets/img/auth/bg2.jpg', 'Login Background 2', 1),
('auth', 'login_bg_3', '/assets/img/auth/bg3.jpg', 'Login Background 3', 1),
('auth', 'reset_bg', '/assets/img/auth/reset_bg.jpg', 'Reset Password Background', 1),
('auth', 'forgot_bg', '/assets/img/auth/bg3.jpg', 'Forgot Password Background', 1)
ON CONFLICT (section, key) DO NOTHING;
