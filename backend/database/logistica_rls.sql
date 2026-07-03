-- RLS Policies for Logistics Tables (Supabase / PostgreSQL)

-- ============================================
-- MOTORISTAS
-- ============================================
ALTER TABLE IF EXISTS public.motoristas ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_motoristas" ON public.motoristas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_motoristas" ON public.motoristas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_read_motoristas" ON public.motoristas; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_motoristas" ON public.motoristas
  FOR ALL USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "funcionario_all_motoristas" ON public.motoristas
  FOR ALL USING (auth.jwt() ->> 'role' = 'funcionario');

CREATE POLICY "client_read_motoristas" ON public.motoristas
  FOR SELECT USING (auth.role() = 'authenticated');

-- ============================================
-- CAMIOES
-- ============================================
ALTER TABLE IF EXISTS public.camioes ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_camioes" ON public.camioes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_camioes" ON public.camioes; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_read_camioes" ON public.camioes; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_camioes" ON public.camioes
  FOR ALL USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "funcionario_all_camioes" ON public.camioes
  FOR ALL USING (auth.jwt() ->> 'role' = 'funcionario');

CREATE POLICY "client_read_camioes" ON public.camioes
  FOR SELECT USING (auth.role() = 'authenticated');

-- ============================================
-- ENTREGAS
-- ============================================
ALTER TABLE IF EXISTS public.entregas ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_entregas" ON public.entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_entregas" ON public.entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_entregas" ON public.entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_entregas" ON public.entregas
  FOR ALL USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "funcionario_all_entregas" ON public.entregas
  FOR ALL USING (auth.jwt() ->> 'role' = 'funcionario');

CREATE POLICY "client_select_own_entregas" ON public.entregas
  FOR SELECT USING (
    auth.jwt() ->> 'role' = 'cliente' AND user_id = auth.uid()
  );

-- ============================================
-- CONTENTORES
-- ============================================
ALTER TABLE IF EXISTS public.contentores ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_contentores" ON public.contentores; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_contentores" ON public.contentores; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_contentores" ON public.contentores; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_contentores" ON public.contentores
  FOR ALL USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "funcionario_all_contentores" ON public.contentores
  FOR ALL USING (auth.jwt() ->> 'role' = 'funcionario');

CREATE POLICY "client_select_own_contentores" ON public.contentores
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM public.entregas e
      WHERE e.id = contentores.entrega_id
      AND e.user_id = auth.uid()
    )
  );

-- ============================================
-- HISTORICO ENTREGAS
-- ============================================
ALTER TABLE IF EXISTS public.historico_entregas ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN DROP POLICY IF EXISTS "admin_all_historico" ON public.historico_entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "funcionario_all_historico" ON public.historico_entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;
DO $$ BEGIN DROP POLICY IF EXISTS "client_select_own_historico" ON public.historico_entregas; EXCEPTION WHEN OTHERS THEN NULL; END $$;

CREATE POLICY "admin_all_historico" ON public.historico_entregas
  FOR ALL USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "funcionario_all_historico" ON public.historico_entregas
  FOR ALL USING (auth.jwt() ->> 'role' = 'funcionario');

CREATE POLICY "client_select_own_historico" ON public.historico_entregas
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM public.entregas e
      WHERE e.id = historico_entregas.entrega_id
      AND e.user_id = auth.uid()
    )
  );
