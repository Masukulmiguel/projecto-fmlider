-- Migration: Rebuild contentores table with full schema (PostgreSQL / Supabase)
-- Execute this AFTER backing up existing data

-- 1. Create new contentores table
CREATE TABLE IF NOT EXISTS contentores_v2 (
  id SERIAL PRIMARY KEY,
  ns VARCHAR(50),
  numero VARCHAR(50) NOT NULL,
  selo VARCHAR(50),
  tipologia VARCHAR(50),
  capacidade VARCHAR(50),
  peso DECIMAL(10,2),
  eta DATE,
  ata DATE,
  data_descarga DATE,
  terminal VARCHAR(100),
  numero_t1 VARCHAR(50),
  data_t1 DATE,
  garantia VARCHAR(50),
  passagem VARCHAR(50),
  previsao_saida DATE,
  taxas DECIMAL(10,2),
  cliente_id UUID REFERENCES users(id),
  numero_processo VARCHAR(50),
  referencia_fmlider VARCHAR(50),
  referencia_cliente VARCHAR(50),
  estado VARCHAR(30) DEFAULT 'aguardando_chegada' CHECK (estado IN (
    'aguardando_chegada','chegou_ao_porto','em_terminal','na_base',
    'agendado_para_entrega','em_transporte','entregue','devolvido','cancelado'
  )),
  observacoes TEXT,
  user_id UUID,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Migrate data from old table
INSERT INTO contentores_v2 (numero, tipo, estado, data_entrega, observacoes, created_at, updated_at)
SELECT
  numero,
  tipo,
  CASE
    WHEN estado IN ('pendente','em_preparacao','carregado','em_transporte','entregue','devolvido') THEN
      CASE estado
        WHEN 'pendente' THEN 'aguardando_chegada'
        WHEN 'em_preparacao' THEN 'em_terminal'
        WHEN 'carregado' THEN 'na_base'
        WHEN 'em_transporte' THEN 'em_transporte'
        WHEN 'entregue' THEN 'entregue'
        WHEN 'devolvido' THEN 'devolvido'
        ELSE 'aguardando_chegada'
      END
    ELSE 'aguardando_chegada'
  END,
  data_entrega,
  observacoes,
  created_at,
  updated_at
FROM contentores;

-- 3. Add contentor_id to entregas
ALTER TABLE entregas ADD COLUMN IF NOT EXISTS contentor_id INTEGER REFERENCES contentores_v2(id);

-- 4. Create indexes
CREATE INDEX IF NOT EXISTS idx_contentores_v2_estado ON contentores_v2(estado);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_cliente ON contentores_v2(cliente_id);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_numero ON contentores_v2(numero);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_terminal ON contentores_v2(terminal);
CREATE INDEX IF NOT EXISTS idx_entregas_contentor ON entregas(contentor_id);

-- 5. Drop old table and rename
DROP TABLE IF EXISTS contentores CASCADE;
ALTER TABLE contentores_v2 RENAME TO contentores;

-- 6. Updated_at trigger
CREATE OR REPLACE FUNCTION update_contentores_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS contentores_updated_at ON contentores;
CREATE TRIGGER contentores_updated_at
  BEFORE UPDATE ON contentores
  FOR EACH ROW
  EXECUTE FUNCTION update_contentores_updated_at();

-- 7. RLS Policies
ALTER TABLE contentores ENABLE ROW LEVEL SECURITY;

-- Admin + Documentacao: full access
CREATE POLICY doc_admin_all ON contentores FOR ALL
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE role = 'admin'
      OR departamento = 'documentacao'
    )
  );

-- Logistica: read only + update estado
CREATE POLICY logistica_select ON contentores FOR SELECT
  USING (true);

CREATE POLICY logistica_update ON contentores FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE departamento = 'logistica'
    )
  )
  WITH CHECK (
    estado IN ('agendado_para_entrega','em_transporte','entregue')
  );

-- Clients: see only their own
CREATE POLICY client_select ON contentores FOR SELECT
  USING (
    auth.uid() = cliente_id
  );
