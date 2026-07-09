-- Migration: Rebuild contentores table (PostgreSQL / Supabase)
-- Run this ENTIRE script at once in the Supabase SQL Editor

-- Step 1: Create the new table
CREATE TABLE IF NOT EXISTS contentores_v2 (
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

-- Step 2: Migrate data from old table (skip if old table is empty or missing)
DO $$
BEGIN
  IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'contentores') THEN
    INSERT INTO contentores_v2 (numero, tipologia, estado, observacoes, created_at, updated_at)
    SELECT
      numero,
      tipo,
      CASE
        WHEN estado = 'pendente' THEN 'aguardando_chegada'
        WHEN estado = 'em_preparacao' THEN 'em_terminal'
        WHEN estado = 'carregado' THEN 'na_base'
        WHEN estado = 'em_transporte' THEN 'em_transporte'
        WHEN estado = 'entregue' THEN 'entregue'
        WHEN estado = 'devolvido' THEN 'devolvido'
        ELSE 'aguardando_chegada'
      END,
      observacoes,
      created_at,
      updated_at
    FROM contentores;
  END IF;
END $$;

-- Step 3: Add contentor_id to entregas
ALTER TABLE entregas ADD COLUMN IF NOT EXISTS contentor_id INTEGER;

-- Step 4: Create indexes
CREATE INDEX IF NOT EXISTS idx_contentores_v2_estado ON contentores_v2(estado);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_cliente ON contentores_v2(cliente_id);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_numero ON contentores_v2(numero);
CREATE INDEX IF NOT EXISTS idx_contentores_v2_terminal ON contentores_v2(terminal);

-- Step 5: Drop old table and rename
DROP TABLE IF EXISTS contentores CASCADE;
ALTER TABLE contentores_v2 RENAME TO contentores;

-- Step 6: Updated_at trigger
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
