-- ============================================================
-- FMLider - Supabase PostgreSQL Migration
-- Generated: 2026-06-13
-- Tables: 20
-- ============================================================

-- ============================================================
-- 1. users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username varchar(80) UNIQUE NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) UNIQUE NOT NULL,
    phone varchar(20),
    role text DEFAULT 'cliente' CHECK (role IN ('admin','cliente','funcionario')),
    permissions jsonb,
    position varchar(100),
    password varchar(255) NOT NULL,
    password_must_change boolean DEFAULT false,
    password_changed_at timestamptz,
    password_expires_at timestamptz,
    locked_at timestamptz,
    locked_reason varchar(255),
    photo varchar(255),
    status smallint DEFAULT 1,
    approval_status text DEFAULT 'pending' CHECK (approval_status IN ('pending','approved','rejected')),
    approved_at timestamptz,
    approved_by bigint REFERENCES users(id),
    rejection_reason varchar(255),
    last_login timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 2. companies
-- ============================================================
CREATE TABLE IF NOT EXISTS companies (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    company_name varchar(255) NOT NULL,
    nif varchar(50),
    logo varchar(255),
    is_published boolean DEFAULT false,
    address varchar(255),
    phone varchar(20),
    email varchar(255),
    service varchar(150),
    case_description text,
    is_completed boolean DEFAULT false,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 3. services
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) UNIQUE NOT NULL,
    description text,
    image varchar(255),
    content text,
    status smallint DEFAULT 1,
    order_by int DEFAULT 0,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 4. news
-- ============================================================
CREATE TABLE IF NOT EXISTS news (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title varchar(255) NOT NULL,
    slug varchar(255) UNIQUE NOT NULL,
    image varchar(255),
    description text,
    content text,
    category varchar(100),
    status text DEFAULT 'draft' CHECK (status IN ('published','draft')),
    user_id bigint REFERENCES users(id) ON DELETE SET NULL,
    published_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 5. gallery
-- ============================================================
CREATE TABLE IF NOT EXISTS gallery (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title varchar(255),
    image varchar(255) NOT NULL,
    category varchar(100),
    description text,
    alt_text varchar(255),
    order_by int DEFAULT 0,
    user_id bigint REFERENCES users(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 6. partners
-- ============================================================
CREATE TABLE IF NOT EXISTS partners (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name varchar(255) NOT NULL,
    logo varchar(255),
    website varchar(255),
    description text,
    order_by int DEFAULT 0,
    status smallint DEFAULT 1,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 7. contacts
-- ============================================================
CREATE TABLE IF NOT EXISTS contacts (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name varchar(255) NOT NULL,
    company varchar(255),
    phone varchar(20),
    email varchar(255) NOT NULL,
    subject varchar(255),
    message text NOT NULL,
    is_read boolean DEFAULT false,
    replied_at timestamptz,
    reply_message text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 8. testimonials
-- ============================================================
CREATE TABLE IF NOT EXISTS testimonials (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name varchar(255) NOT NULL,
    position varchar(255),
    company varchar(255),
    message text NOT NULL,
    photo varchar(255),
    rating int DEFAULT 5,
    status smallint DEFAULT 1,
    order_by int DEFAULT 0,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 9. faqs
-- ============================================================
CREATE TABLE IF NOT EXISTS faqs (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    question varchar(255) NOT NULL,
    answer text NOT NULL,
    category varchar(100),
    order_by int DEFAULT 0,
    status smallint DEFAULT 1,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 10. banners
-- ============================================================
CREATE TABLE IF NOT EXISTS banners (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title varchar(255) NOT NULL,
    description text,
    image varchar(255),
    link varchar(255),
    status smallint DEFAULT 1,
    order_by int DEFAULT 0,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 11. settings
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    key varchar(255) UNIQUE NOT NULL,
    value text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 12. embarques
-- ============================================================
CREATE TABLE IF NOT EXISTS embarques (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tracking_number varchar(50) UNIQUE NOT NULL,
    origin varchar(150) NOT NULL,
    destination varchar(150) NOT NULL,
    type text CHECK (type IN ('maritimo','aereo','terrestre','ferroviario','multimodal')),
    status text DEFAULT 'pendente' CHECK (status IN ('pendente','em_transito','entregue','cancelado')),
    weight numeric(12,2),
    volume numeric(12,4),
    declared_value numeric(14,2),
    currency varchar(10) DEFAULT 'AOA',
    ship_date date,
    delivery_date date,
    description text,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 13. documentos
-- ============================================================
CREATE TABLE IF NOT EXISTS documentos (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    embarque_id bigint REFERENCES embarques(id) ON DELETE SET NULL,
    name varchar(255) NOT NULL,
    type text CHECK (type IN ('fatura','conhecimento_carga','certificado','contrato','outro')),
    file_path varchar(255) NOT NULL,
    file_size int,
    mime_type varchar(100),
    description text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 14. contactos (client CRM)
-- ============================================================
CREATE TABLE IF NOT EXISTS contactos (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name varchar(150) NOT NULL,
    company varchar(150),
    email varchar(150),
    phone varchar(30),
    position varchar(100),
    address varchar(255),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 15. cotacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS cotacoes (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reference varchar(50) NOT NULL,
    origin varchar(150) NOT NULL,
    destination varchar(150) NOT NULL,
    type text CHECK (type IN ('maritimo','aereo','terrestre','ferroviario','multimodal')),
    weight numeric(12,2),
    description text,
    status text DEFAULT 'pendente' CHECK (status IN ('pendente','aprovada','rejeitada','expirada','convertida')),
    estimated_value numeric(14,2),
    currency varchar(10) DEFAULT 'AOA',
    valid_until date,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- ============================================================
-- 16. chat_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS chat_messages (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    sender_id bigint REFERENCES users(id) ON DELETE SET NULL,
    receiver_id bigint REFERENCES users(id) ON DELETE SET NULL,
    message text NOT NULL,
    is_read boolean DEFAULT false,
    created_at timestamptz DEFAULT now()
);

-- ============================================================
-- 17. notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type varchar(50) NOT NULL,
    title varchar(255) NOT NULL,
    body text,
    link varchar(255),
    icon varchar(50),
    is_read boolean DEFAULT false,
    created_at timestamptz DEFAULT now()
);

-- ============================================================
-- 18. visitors
-- ============================================================
CREATE TABLE IF NOT EXISTS visitors (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    session_id varchar(64),
    user_id bigint REFERENCES users(id) ON DELETE SET NULL,
    ip_address varchar(45),
    country varchar(100),
    city varchar(100),
    region varchar(100),
    browser varchar(50),
    browser_version varchar(20),
    os varchar(50),
    device_type varchar(20),
    referrer varchar(500),
    page_url varchar(500),
    user_agent text,
    visited_at timestamptz DEFAULT now()
);

-- ============================================================
-- 19. activity_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint REFERENCES users(id) ON DELETE SET NULL,
    action varchar(255) NOT NULL,
    description text,
    ip_address varchar(45),
    user_agent text,
    created_at timestamptz DEFAULT now()
);

-- ============================================================
-- 20. user_photos
-- ============================================================
CREATE TABLE IF NOT EXISTS user_photos (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id bigint NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    photo_path varchar(255) NOT NULL,
    is_current boolean DEFAULT false,
    created_at timestamptz DEFAULT now()
);

-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_approval_status ON users(approval_status);

CREATE INDEX IF NOT EXISTS idx_companies_user_id ON companies(user_id);

CREATE INDEX IF NOT EXISTS idx_services_slug ON services(slug);
CREATE INDEX IF NOT EXISTS idx_services_status ON services(status);

CREATE INDEX IF NOT EXISTS idx_news_slug ON news(slug);
CREATE INDEX IF NOT EXISTS idx_news_status ON news(status);
CREATE INDEX IF NOT EXISTS idx_news_user_id ON news(user_id);

CREATE INDEX IF NOT EXISTS idx_gallery_category ON gallery(category);
CREATE INDEX IF NOT EXISTS idx_gallery_user_id ON gallery(user_id);

CREATE INDEX IF NOT EXISTS idx_partners_status ON partners(status);

CREATE INDEX IF NOT EXISTS idx_contacts_is_read ON contacts(is_read);

CREATE INDEX IF NOT EXISTS idx_testimonials_status ON testimonials(status);

CREATE INDEX IF NOT EXISTS idx_faqs_category ON faqs(category);
CREATE INDEX IF NOT EXISTS idx_faqs_status ON faqs(status);

CREATE INDEX IF NOT EXISTS idx_banners_status ON banners(status);

CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(key);

CREATE INDEX IF NOT EXISTS idx_embarques_user_id ON embarques(user_id);
CREATE INDEX IF NOT EXISTS idx_embarques_tracking_number ON embarques(tracking_number);
CREATE INDEX IF NOT EXISTS idx_embarques_status ON embarques(status);

CREATE INDEX IF NOT EXISTS idx_documentos_user_id ON documentos(user_id);
CREATE INDEX IF NOT EXISTS idx_documentos_embarque_id ON documentos(embarque_id);

CREATE INDEX IF NOT EXISTS idx_contactos_user_id ON contactos(user_id);

CREATE INDEX IF NOT EXISTS idx_cotacoes_user_id ON cotacoes(user_id);
CREATE INDEX IF NOT EXISTS idx_cotacoes_status ON cotacoes(status);

CREATE INDEX IF NOT EXISTS idx_chat_messages_sender_id ON chat_messages(sender_id);
CREATE INDEX IF NOT EXISTS idx_chat_messages_receiver_id ON chat_messages(receiver_id);

CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);

CREATE INDEX IF NOT EXISTS idx_visitors_user_id ON visitors(user_id);
CREATE INDEX IF NOT EXISTS idx_visitors_session_id ON visitors(session_id);
CREATE INDEX IF NOT EXISTS idx_visitors_visited_at ON visitors(visited_at);

CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_action ON activity_logs(action);
CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at);

CREATE INDEX IF NOT EXISTS idx_user_photos_user_id ON user_photos(user_id);

-- ============================================================
-- ROW LEVEL SECURITY (RLS)
-- ============================================================

-- Enable RLS on all tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE companies ENABLE ROW LEVEL SECURITY;
ALTER TABLE services ENABLE ROW LEVEL SECURITY;
ALTER TABLE news ENABLE ROW LEVEL SECURITY;
ALTER TABLE gallery ENABLE ROW LEVEL SECURITY;
ALTER TABLE partners ENABLE ROW LEVEL SECURITY;
ALTER TABLE contacts ENABLE ROW LEVEL SECURITY;
ALTER TABLE testimonials ENABLE ROW LEVEL SECURITY;
ALTER TABLE faqs ENABLE ROW LEVEL SECURITY;
ALTER TABLE banners ENABLE ROW LEVEL SECURITY;
ALTER TABLE settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE embarques ENABLE ROW LEVEL SECURITY;
ALTER TABLE documentos ENABLE ROW LEVEL SECURITY;
ALTER TABLE contactos ENABLE ROW LEVEL SECURITY;
ALTER TABLE cotacoes ENABLE ROW LEVEL SECURITY;
ALTER TABLE chat_messages ENABLE ROW LEVEL SECURITY;
ALTER TABLE notifications ENABLE ROW LEVEL SECURITY;
ALTER TABLE visitors ENABLE ROW LEVEL SECURITY;
ALTER TABLE activity_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_photos ENABLE ROW LEVEL SECURITY;

-- ============================================================
-- RLS POLICIES
-- ============================================================

-- ------------------------------------------------------------
-- service_role: full access on ALL tables
-- ------------------------------------------------------------
CREATE POLICY "Service role full access" ON users FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON companies FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON services FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON news FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON gallery FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON partners FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON contacts FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON testimonials FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON faqs FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON banners FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON settings FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON embarques FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON documentos FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON contactos FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON cotacoes FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON chat_messages FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON notifications FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON visitors FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON activity_logs FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Service role full access" ON user_photos FOR ALL USING (auth.role() = 'service_role');

-- ------------------------------------------------------------
-- Public read: anonymous/anyone can read public-facing tables
-- ------------------------------------------------------------
CREATE POLICY "Public read" ON services FOR SELECT USING (true);
CREATE POLICY "Public read" ON news FOR SELECT USING (true);
CREATE POLICY "Public read" ON gallery FOR SELECT USING (true);
CREATE POLICY "Public read" ON partners FOR SELECT USING (true);
CREATE POLICY "Public read" ON testimonials FOR SELECT USING (true);
CREATE POLICY "Public read" ON faqs FOR SELECT USING (true);
CREATE POLICY "Public read" ON banners FOR SELECT USING (true);
CREATE POLICY "Public read" ON settings FOR SELECT USING (true);

-- ------------------------------------------------------------
-- contacts: allow anonymous inserts (contact form)
-- ------------------------------------------------------------
CREATE POLICY "Anonymous insert" ON contacts FOR INSERT WITH CHECK (true);
CREATE POLICY "Authenticated read own" ON contacts FOR SELECT USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- users: authenticated users can read/update own profile
-- ------------------------------------------------------------
CREATE POLICY "Users read own profile" ON users FOR SELECT USING (auth.uid() = id::text);
CREATE POLICY "Users update own profile" ON users FOR UPDATE USING (auth.uid() = id::text);

-- ------------------------------------------------------------
-- companies: authenticated users manage own company
-- ------------------------------------------------------------
CREATE POLICY "Users own company" ON companies FOR ALL USING (auth.uid() = user_id::text);
CREATE POLICY "Public read published" ON companies FOR SELECT USING (is_published = true);

-- ------------------------------------------------------------
-- embarques: authenticated users manage own shipments
-- ------------------------------------------------------------
CREATE POLICY "Users own embarques" ON embarques FOR ALL USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- documentos: authenticated users manage own documents
-- ------------------------------------------------------------
CREATE POLICY "Users own documentos" ON documentos FOR ALL USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- contactos: authenticated users manage own CRM contacts
-- ------------------------------------------------------------
CREATE POLICY "Users own contactos" ON contactos FOR ALL USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- cotacoes: authenticated users manage own quotes
-- ------------------------------------------------------------
CREATE POLICY "Users own cotacoes" ON cotacoes FOR ALL USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- chat_messages: authenticated users read/write own conversations
-- ------------------------------------------------------------
CREATE POLICY "Users own chat messages" ON chat_messages FOR SELECT
    USING (auth.uid() = sender_id::text OR auth.uid() = receiver_id::text);
CREATE POLICY "Users insert chat messages" ON chat_messages FOR INSERT
    WITH CHECK (auth.uid() = sender_id::text);

-- ------------------------------------------------------------
-- notifications: authenticated users read own notifications
-- ------------------------------------------------------------
CREATE POLICY "Users own notifications" ON notifications FOR SELECT USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- visitors: allow anonymous inserts (page tracking)
-- ------------------------------------------------------------
CREATE POLICY "Anonymous insert visitors" ON visitors FOR INSERT WITH CHECK (true);
CREATE POLICY "Authenticated read visitors" ON visitors FOR SELECT USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- activity_logs: allow anonymous inserts, authenticated read own
-- ------------------------------------------------------------
CREATE POLICY "Anonymous insert activity" ON activity_logs FOR INSERT WITH CHECK (true);
CREATE POLICY "Authenticated read activity" ON activity_logs FOR SELECT USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- user_photos: authenticated users manage own photos
-- ------------------------------------------------------------
CREATE POLICY "Users own photos" ON user_photos FOR ALL USING (auth.uid() = user_id::text);

-- ------------------------------------------------------------
-- news: authenticated users can manage (admin/funcionario)
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage news" ON news FOR ALL USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- gallery: authenticated users can manage
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage gallery" ON gallery FOR ALL USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- partners: authenticated users can manage
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage partners" ON partners FOR ALL USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- testimonials: authenticated users can manage
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage testimonials" ON testimonials FOR ALL USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- faqs: authenticated users can manage
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage faqs" ON faqs FOR ALL USING (auth.role() = 'authenticated');

-- ------------------------------------------------------------
-- banners: authenticated users can manage
-- ------------------------------------------------------------
CREATE POLICY "Authenticated manage banners" ON banners FOR ALL USING (auth.role() = 'authenticated');

-- ============================================================
-- TRIGGER: auto-update updated_at on row modification
-- ============================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_companies_updated_at BEFORE UPDATE ON companies FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_services_updated_at BEFORE UPDATE ON services FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_news_updated_at BEFORE UPDATE ON news FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_gallery_updated_at BEFORE UPDATE ON gallery FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_partners_updated_at BEFORE UPDATE ON partners FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_contacts_updated_at BEFORE UPDATE ON contacts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_testimonials_updated_at BEFORE UPDATE ON testimonials FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_faqs_updated_at BEFORE UPDATE ON faqs FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_banners_updated_at BEFORE UPDATE ON banners FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_settings_updated_at BEFORE UPDATE ON settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_embarques_updated_at BEFORE UPDATE ON embarques FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_documentos_updated_at BEFORE UPDATE ON documentos FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_contactos_updated_at BEFORE UPDATE ON contactos FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_cotacoes_updated_at BEFORE UPDATE ON cotacoes FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_user_photos_updated_at BEFORE UPDATE ON user_photos FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================
-- END OF MIGRATION
-- ============================================================
