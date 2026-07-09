-- Complete setup: table + RLS (run everything at once)

-- Step 1: Create table
CREATE TABLE IF NOT EXISTS contentores (
  id SERIAL PRIMARY KEY,
  ns VARCHAR(50),
  numero VARCHAR(50),
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
  cliente_id UUID,
  numero_processo VARCHAR(50),
  referencia_fmlider VARCHAR(50),
  referencia_cliente VARCHAR(50),
  estado VARCHAR(30) DEFAULT 'aguardando_chegada',
  observacoes TEXT,
  user_id UUID,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Step 2: Add contentor_id to entregas
ALTER TABLE entregas ADD COLUMN IF NOT EXISTS contentor_id INTEGER;

-- Step 3: Indexes
CREATE INDEX IF NOT EXISTS idx_contentores_estado ON contentores(estado);
CREATE INDEX IF NOT EXISTS idx_contentores_cliente ON contentores(cliente_id);
CREATE INDEX IF NOT EXISTS idx_contentores_numero ON contentores(numero);

-- Step 4: Updated_at trigger
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

-- Step 5: RLS
ALTER TABLE contentores ENABLE ROW LEVEL SECURITY;

CREATE POLICY doc_admin_all ON contentores FOR ALL
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE role = 'admin'
      OR departamento = 'documentacao'
    )
  );

CREATE POLICY logistica_select ON contentores FOR SELECT USING (true);

CREATE POLICY logistica_update ON contentores FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE departamento = 'logistica'
    )
  )
  WITH CHECK (
    estado IN ('agendado_para_entrega','em_transporte','entregue')
  );

CREATE POLICY client_select ON contentores FOR SELECT
  USING (auth.uid() = cliente_id);
