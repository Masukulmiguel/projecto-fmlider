-- Tabela fleet_items para gestão da frota (página /frota)
-- Executar no Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

CREATE TABLE IF NOT EXISTS fleet_items (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title varchar(255) NOT NULL,
    category varchar(50) NOT NULL DEFAULT 'trucks',
    description text,
    image text,
    specs jsonb DEFAULT '[]'::jsonb,
    category_label varchar(100),
    order_by integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now()
);

-- RLS
ALTER TABLE public.fleet_items ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'fleet_items_public_select' AND tablename = 'fleet_items') THEN
    CREATE POLICY "fleet_items_public_select" ON public.fleet_items
      FOR SELECT USING (true);
  END IF;
END $$;

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE policyname = 'fleet_items_admin_all' AND tablename = 'fleet_items') THEN
    CREATE POLICY "fleet_items_admin_all" ON public.fleet_items
      FOR ALL USING (public.get_user_role() = 'admin');
  END IF;
END $$;

-- Index
CREATE INDEX IF NOT EXISTS idx_fleet_items_category ON fleet_items(category);
CREATE INDEX IF NOT EXISTS idx_fleet_items_active ON fleet_items(is_active);

-- Inserir dados iniciais
INSERT INTO fleet_items (title, category, description, image, specs, category_label, order_by) VALUES
('Camião Tanque', 'trucks', 'Camião tanque para transporte de líquidos com capacidade de 30.000L, certificação ATEX.', '/assets/img/resachstacker/resachstacker3.jpeg', '[{"label":"Capacidade","value":"30.000L"},{"label":"Tipo","value":"Tanque"},{"label":"Certificação","value":"ATEX"}]', 'Camião', 1),
('Camião Frigorífico', 'trucks', 'Camião frigorífico com temperatura controlada de -20°C a +30°C, capacidade 20t.', '/assets/img/resachstacker/resachstacker4.jpeg', '[{"label":"Temperatura","value":"-20°C a +30°C"},{"label":"Capacidade","value":"20t"},{"label":"Tipo","value":"2 independentes"}]', 'Camião', 2),
('Camião Plataforma', 'trucks', 'Camião plataforma baixa para carga pesada, capacidade 40t e comprimento 13.6m.', '/assets/img/resachstacker/resachstacker5.jpeg', '[{"label":"Capacidade","value":"40t"},{"label":"Comprimento","value":"13.6m"},{"label":"Tipo","value":"Plataforma baixa"}]', 'Camião', 3),
('Contentor 20 pés', 'containers', 'Contentor padrão de 20 pés para transporte marítimo e terrestre.', '/assets/img/resachstacker/resachstacker6.jpeg', '[{"label":"Tamanho","value":"20 pés"},{"label":"Capacidade","value":"28t"},{"label":"Volume","value":"33m³"}]', 'Contentor', 4),
('Contentor 40 pés', 'containers', 'Contentor padrão de 40 pés para transporte marítimo e terrestre.', '/assets/img/resachstacker/resachstacker7.jpeg', '[{"label":"Tamanho","value":"40 pés"},{"label":"Capacidade","value":"28t"},{"label":"Volume","value":"67m³"}]', 'Contentor', 5),
('Reachstacker Kalmar', 'equipment', 'Equipamento Kalmar para manuseio de contentores, ferro e pipes com capacidade de 45t.', '/assets/img/resachstacker/resachstacker8.jpeg', '[{"label":"Capacidade","value":"45t"},{"label":"Marca","value":"Kalmar"},{"label":"Pilhagem","value":"4 contentores"}]', 'Equipamento', 6)
ON CONFLICT DO NOTHING;
