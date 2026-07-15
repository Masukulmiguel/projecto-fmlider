DO $$
BEGIN
  DROP TABLE IF EXISTS processos CASCADE;
END $$;

CREATE TABLE processos (
  id SERIAL PRIMARY KEY,
  file_number VARCHAR(50),
  ref_cliente VARCHAR(100),
  tipo VARCHAR(20),
  importador VARCHAR(100),
  agencia VARCHAR(100),
  bl VARCHAR(100),
  bl_draft VARCHAR(100),
  typo VARCHAR(100),
  eta DATE,
  ata DATE,
  estado_legalizacao TEXT,
  estado VARCHAR(50) DEFAULT 'pedir_proforma',
  observacoes TEXT,
  cliente_id UUID,
  assigned_to UUID,
  n_dias INTEGER,
  data_saida DATE,
  limite_retorno DATE,
  caminho_arquivo VARCHAR(255),
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_processos_estado ON processos(estado);
CREATE INDEX IF NOT EXISTS idx_processos_cliente ON processos(cliente_id);
CREATE INDEX IF NOT EXISTS idx_processos_file ON processos(file_number);
CREATE INDEX IF NOT EXISTS idx_processos_ref ON processos(ref_cliente);
CREATE INDEX IF NOT EXISTS idx_processos_assigned ON processos(assigned_to);

CREATE OR REPLACE FUNCTION update_processos_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS processos_updated_at ON processos;
CREATE TRIGGER processos_updated_at
  BEFORE UPDATE ON processos
  FOR EACH ROW
  EXECUTE FUNCTION update_processos_updated_at();

ALTER TABLE processos ENABLE ROW LEVEL SECURITY;

CREATE POLICY admin_all ON processos FOR ALL
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE role = 'admin'
    )
  );

CREATE POLICY doc_manage ON processos FOR ALL
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE departamento = 'documentacao'
    )
  );

CREATE POLICY logistica_select ON processos FOR SELECT
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE departamento = 'logistica'
    )
  );

CREATE POLICY logistica_update ON processos FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT auth_id FROM users WHERE departamento = 'logistica'
    )
  )
  WITH CHECK (true);

CREATE POLICY client_select ON processos FOR SELECT
  USING (auth.uid() = cliente_id);
