-- ============================================================
-- RLS POLICIES PARA FMLIDER (Supabase) - VERSAO SEGURO
-- Execute no Supabase SQL Editor:
-- https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new
-- Esta versao NAO da erro se ja existirem policies
-- ============================================================

-- ============================================================
-- 1. HABILITAR RLS EM TODAS AS TABELAS
-- ============================================================
DO $$ BEGIN
  ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.companies ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.visitors ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.chat_messages ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.embarques ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.cotacoes ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.documentos ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.contacts ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.contactos ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.services ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.news ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.gallery ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.partners ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.testimonials ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.faqs ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.banners ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.settings ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.activity_logs ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;
DO $$ BEGIN
  ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;
EXCEPTION WHEN OTHERS THEN NULL;
END $$;

-- ============================================================
-- 2. FUNCOES AUXILIARES
-- ============================================================
CREATE OR REPLACE FUNCTION public.get_user_role()
RETURNS TEXT AS $$
  SELECT COALESCE(
    (SELECT role FROM public.users WHERE auth_id = auth.uid() LIMIT 1),
    'anonymous'
  );
$$ LANGUAGE sql SECURITY DEFINER STABLE;

CREATE OR REPLACE FUNCTION public.get_user_id()
RETURNS BIGINT AS $$
  SELECT id FROM public.users WHERE auth_id = auth.uid() LIMIT 1;
$$ LANGUAGE sql SECURITY DEFINER STABLE;

-- ============================================================
-- 3. POLICIES: users (apagar e recriar)
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_select_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_select_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_select_own_profile" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_insert_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_update_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_delete_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_update_users" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_update_own_profile" ON public.users; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_select_users" ON public.users
  FOR SELECT USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_select_users" ON public.users
  FOR SELECT USING (public.get_user_role() = 'funcionario');

CREATE POLICY "user_select_own_profile" ON public.users
  FOR SELECT USING (auth_id = auth.uid());

CREATE POLICY "admin_insert_users" ON public.users
  FOR INSERT WITH CHECK (public.get_user_role() = 'admin');

CREATE POLICY "admin_update_users" ON public.users
  FOR UPDATE USING (public.get_user_role() = 'admin');

CREATE POLICY "admin_delete_users" ON public.users
  FOR DELETE USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_update_users" ON public.users
  FOR UPDATE USING (
    public.get_user_role() = 'funcionario'
    AND role = 'cliente'
  );

CREATE POLICY "user_update_own_profile" ON public.users
  FOR UPDATE USING (auth_id = auth.uid());

-- ============================================================
-- 4. POLICIES: companies
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_companies" ON public.companies; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_companies" ON public.companies; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_company" ON public.companies; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_own_company" ON public.companies; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_update_own_company" ON public.companies; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_companies" ON public.companies
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_companies" ON public.companies
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_select_own_company" ON public.companies
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "client_insert_own_company" ON public.companies
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

CREATE POLICY "client_update_own_company" ON public.companies
  FOR UPDATE USING (user_id = public.get_user_id());

-- ============================================================
-- 5. POLICIES: visitors
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "anon_insert_visitors" ON public.visitors; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_select_visitors" ON public.visitors; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_select_visitors" ON public.visitors; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_delete_visitors" ON public.visitors; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "anon_insert_visitors" ON public.visitors
  FOR INSERT WITH CHECK (true);

CREATE POLICY "admin_select_visitors" ON public.visitors
  FOR SELECT USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_select_visitors" ON public.visitors
  FOR SELECT USING (
    public.get_user_role() = 'funcionario'
    AND EXISTS (
      SELECT 1 FROM public.users
      WHERE auth_id = auth.uid()
      AND permissions::text LIKE '%visitors.view%'
    )
  );

CREATE POLICY "admin_delete_visitors" ON public.visitors
  FOR DELETE USING (public.get_user_role() = 'admin');

-- ============================================================
-- 6. POLICIES: chat_messages
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_chat" ON public.chat_messages; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_chat" ON public.chat_messages; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_chat_own" ON public.chat_messages; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_chat" ON public.chat_messages; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_chat" ON public.chat_messages
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_chat" ON public.chat_messages
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_chat_own" ON public.chat_messages
  FOR SELECT USING (
    sender_id = public.get_user_id()
    OR receiver_id = public.get_user_id()
  );

CREATE POLICY "client_insert_chat" ON public.chat_messages
  FOR INSERT WITH CHECK (sender_id = public.get_user_id());

-- ============================================================
-- 7. POLICIES: embarques
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_embarques" ON public.embarques; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_embarques" ON public.embarques; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_embarques" ON public.embarques; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_own_embarques" ON public.embarques; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_update_own_embarques" ON public.embarques; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_embarques" ON public.embarques
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_embarques" ON public.embarques
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_select_own_embarques" ON public.embarques
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "client_insert_own_embarques" ON public.embarques
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

CREATE POLICY "client_update_own_embarques" ON public.embarques
  FOR UPDATE USING (user_id = public.get_user_id());

-- ============================================================
-- 8. POLICIES: cotacoes
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_cotacoes" ON public.cotacoes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_cotacoes" ON public.cotacoes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_cotacoes" ON public.cotacoes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_own_cotacoes" ON public.cotacoes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_update_own_cotacoes" ON public.cotacoes; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_cotacoes" ON public.cotacoes
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_cotacoes" ON public.cotacoes
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_select_own_cotacoes" ON public.cotacoes
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "client_insert_own_cotacoes" ON public.cotacoes
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

CREATE POLICY "client_update_own_cotacoes" ON public.cotacoes
  FOR UPDATE USING (user_id = public.get_user_id());

-- ============================================================
-- 9. POLICIES: documentos
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_documentos" ON public.documentos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_documentos" ON public.documentos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_documentos" ON public.documentos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_own_documentos" ON public.documentos; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_documentos" ON public.documentos
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_documentos" ON public.documentos
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_select_own_documentos" ON public.documentos
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "client_insert_own_documentos" ON public.documentos
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

-- ============================================================
-- 10. POLICIES: contacts (formulario publico, SEM user_id)
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "anon_insert_contacts" ON public.contacts; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_contacts" ON public.contacts; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_select_contacts" ON public.contacts; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "anon_insert_contacts" ON public.contacts
  FOR INSERT WITH CHECK (true);

CREATE POLICY "admin_all_contacts" ON public.contacts
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_select_contacts" ON public.contacts
  FOR SELECT USING (public.get_user_role() = 'funcionario');

-- ============================================================
-- 10b. POLICIES: contactos (CRM clientes, COM user_id)
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_contactos" ON public.contactos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_contactos" ON public.contactos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_contactos" ON public.contactos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_insert_own_contactos" ON public.contactos; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_update_own_contactos" ON public.contactos; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_contactos" ON public.contactos
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_all_contactos" ON public.contactos
  FOR ALL USING (public.get_user_role() = 'funcionario');

CREATE POLICY "client_select_own_contactos" ON public.contactos
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "client_insert_own_contactos" ON public.contactos
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

CREATE POLICY "client_update_own_contactos" ON public.contactos
  FOR UPDATE USING (user_id = public.get_user_id());

-- ============================================================
-- 11. POLICIES: services
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_services" ON public.services; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_services" ON public.services; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_manage_services" ON public.services; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_services" ON public.services
  FOR SELECT USING (true);

CREATE POLICY "admin_all_services" ON public.services
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_manage_services" ON public.services
  FOR ALL USING (public.get_user_role() = 'funcionario');

-- ============================================================
-- 12. POLICIES: news
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_news" ON public.news; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_news" ON public.news; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_manage_news" ON public.news; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_news" ON public.news
  FOR SELECT USING (true);

CREATE POLICY "admin_all_news" ON public.news
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_manage_news" ON public.news
  FOR ALL USING (public.get_user_role() = 'funcionario');

-- ============================================================
-- 13. POLICIES: gallery
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_gallery" ON public.gallery; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_gallery" ON public.gallery; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_manage_gallery" ON public.gallery; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_gallery" ON public.gallery
  FOR SELECT USING (true);

CREATE POLICY "admin_all_gallery" ON public.gallery
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_manage_gallery" ON public.gallery
  FOR ALL USING (public.get_user_role() = 'funcionario');

-- ============================================================
-- 14. POLICIES: partners
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_partners" ON public.partners; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_partners" ON public.partners; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_partners" ON public.partners
  FOR SELECT USING (true);

CREATE POLICY "admin_all_partners" ON public.partners
  FOR ALL USING (public.get_user_role() = 'admin');

-- ============================================================
-- 15. POLICIES: testimonials
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_testimonials" ON public.testimonials; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_testimonials" ON public.testimonials; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_testimonials" ON public.testimonials
  FOR SELECT USING (true);

CREATE POLICY "admin_all_testimonials" ON public.testimonials
  FOR ALL USING (public.get_user_role() = 'admin');

-- ============================================================
-- 16. POLICIES: faqs
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_faqs" ON public.faqs; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_faqs" ON public.faqs; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_faqs" ON public.faqs
  FOR SELECT USING (true);

CREATE POLICY "admin_all_faqs" ON public.faqs
  FOR ALL USING (public.get_user_role() = 'admin');

-- ============================================================
-- 17. POLICIES: banners
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_banners" ON public.banners; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_banners" ON public.banners; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_banners" ON public.banners
  FOR SELECT USING (true);

CREATE POLICY "admin_all_banners" ON public.banners
  FOR ALL USING (public.get_user_role() = 'admin');

-- ============================================================
-- 18. POLICIES: settings
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_select_settings" ON public.settings; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_settings" ON public.settings; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_select_settings" ON public.settings
  FOR SELECT USING (true);

CREATE POLICY "admin_all_settings" ON public.settings
  FOR ALL USING (public.get_user_role() = 'admin');

-- ============================================================
-- 19. POLICIES: activity_logs
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_activity_logs" ON public.activity_logs; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_select_activity_logs" ON public.activity_logs; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_activity_logs" ON public.activity_logs
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "funcionario_select_activity_logs" ON public.activity_logs
  FOR SELECT USING (public.get_user_role() = 'funcionario');

-- ============================================================
-- 20. POLICIES: notifications
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_notifications" ON public.notifications; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_select_own_notifications" ON public.notifications; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_insert_own_notifications" ON public.notifications; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_update_own_notifications" ON public.notifications; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_notifications" ON public.notifications
  FOR ALL USING (public.get_user_role() = 'admin');

CREATE POLICY "user_select_own_notifications" ON public.notifications
  FOR SELECT USING (user_id = public.get_user_id());

CREATE POLICY "user_insert_own_notifications" ON public.notifications
  FOR INSERT WITH CHECK (user_id = public.get_user_id());

CREATE POLICY "user_update_own_notifications" ON public.notifications
  FOR UPDATE USING (user_id = public.get_user_id());

-- ============================================================
-- 21. STORAGE POLICIES: photos bucket
-- ============================================================
DO $$ BEGIN DROP POLICY IF EXISTS "public_read_photos" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "authenticated_upload_photos" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "admin_delete_photos" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "user_update_own_photo" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "public_read_photos" ON storage.objects
  FOR SELECT USING (bucket_id = 'photos');

CREATE POLICY "authenticated_upload_photos" ON storage.objects
  FOR INSERT WITH CHECK (
    bucket_id = 'photos'
    AND auth.role() = 'authenticated'
  );

CREATE POLICY "admin_delete_photos" ON storage.objects
  FOR DELETE USING (
    bucket_id = 'photos'
    AND public.get_user_role() = 'admin'
  );

CREATE POLICY "user_update_own_photo" ON storage.objects
  FOR UPDATE USING (
    bucket_id = 'photos'
    AND auth.uid()::text = (storage.foldername(name))[1]
  );
