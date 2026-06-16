-- Execute ESTE SQL no Supabase SQL Editor (adicional ao anterior)
-- https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

-- Habilitar RLS na tabela notifications (se ainda nao estiver)
ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;

-- Policies para notifications
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'admin_all_notifications' AND tablename = 'notifications') THEN
    CREATE POLICY "admin_all_notifications" ON public.notifications
      FOR ALL USING (public.get_user_role() = 'admin');
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'user_select_own_notifications' AND tablename = 'notifications') THEN
    CREATE POLICY "user_select_own_notifications" ON public.notifications
      FOR SELECT USING (user_id = public.get_user_id());
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'user_insert_own_notifications' AND tablename = 'notifications') THEN
    CREATE POLICY "user_insert_own_notifications" ON public.notifications
      FOR INSERT WITH CHECK (user_id = public.get_user_id());
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'user_update_own_notifications' AND tablename = 'notifications') THEN
    CREATE POLICY "user_update_own_notifications" ON public.notifications
      FOR UPDATE USING (user_id = public.get_user_id());
  END IF;
END $$;
