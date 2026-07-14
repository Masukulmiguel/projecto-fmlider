-- Tabela user_presence para rastrear status online/offline
-- Executar no Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

CREATE TABLE IF NOT EXISTS user_presence (
    user_id bigint PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    last_seen timestamptz DEFAULT now(),
    is_online boolean DEFAULT false
);

-- RLS
ALTER TABLE public.user_presence ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'presence_select_all' AND tablename = 'user_presence') THEN
    CREATE POLICY "presence_select_all" ON public.user_presence
      FOR SELECT USING (true);
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'presence_upsert_own' AND tablename = 'user_presence') THEN
    CREATE POLICY "presence_upsert_own" ON public.user_presence
      FOR INSERT WITH CHECK (user_id = public.get_user_id());
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'presence_update_own' AND tablename = 'user_presence') THEN
    CREATE POLICY "presence_update_own" ON public.user_presence
      FOR UPDATE USING (user_id = public.get_user_id());
  END IF;
END $$;

-- Index para queries rápidas
CREATE INDEX IF NOT EXISTS idx_user_presence_online ON user_presence(is_online, last_seen);
