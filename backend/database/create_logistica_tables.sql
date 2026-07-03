-- Migration: Create logistica tables for FMLider (PostgreSQL / Supabase)

CREATE TABLE IF NOT EXISTS motoristas (
  id SERIAL PRIMARY KEY,
  nome_completo VARCHAR(255) NOT NULL,
  bilhete_identidade VARCHAR(50),
  telefone VARCHAR(30),
  carta_conducao VARCHAR(50),
  validade_carta DATE,
  estado VARCHAR(20) DEFAULT 'ativo' CHECK (estado IN ('ativo','inativo')),
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS camioes (
  id SERIAL PRIMARY KEY,
  codigo_interno VARCHAR(50),
  matricula VARCHAR(20) NOT NULL,
  marca VARCHAR(100),
  modelo VARCHAR(100),
  capacidade VARCHAR(100),
  estado VARCHAR(20) DEFAULT 'disponivel' CHECK (estado IN ('disponivel','em_servico','em_manutencao','inativo')),
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS entregas (
  id SERIAL PRIMARY KEY,
  referencia_fmlider VARCHAR(50) UNIQUE,
  referencia_cliente VARCHAR(50),
  numero_processo VARCHAR(50),
  tipologia VARCHAR(100),
  origem VARCHAR(255),
  destino VARCHAR(255),
  cliente_id UUID,
  cliente_nome VARCHAR(255),
  motorista_id INTEGER,
  camiao_id INTEGER,
  matricula VARCHAR(20),
  estado VARCHAR(30) DEFAULT 'pendente' CHECK (estado IN ('pendente','em_preparacao','saiu_da_base','em_transporte','chegou_cliente','entregue','cancelado')),
  data_saida TIMESTAMP,
  data_prevista DATE,
  data_entrega TIMESTAMP,
  observacoes TEXT,
  user_id UUID,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE SET NULL,
  FOREIGN KEY (camiao_id) REFERENCES camioes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS contentores (
  id SERIAL PRIMARY KEY,
  entrega_id INTEGER NOT NULL,
  numero VARCHAR(50),
  tipo VARCHAR(50),
  estado VARCHAR(100),
  data_entrega TIMESTAMP,
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS historico_entregas (
  id SERIAL PRIMARY KEY,
  entrega_id INTEGER NOT NULL,
  estado_anterior VARCHAR(50),
  estado_novo VARCHAR(50),
  utilizador_id UUID,
  utilizador_nome VARCHAR(255),
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
);

-- Performance indexes
CREATE INDEX IF NOT EXISTS idx_entregas_cliente ON entregas(cliente_id);
CREATE INDEX IF NOT EXISTS idx_entregas_motorista ON entregas(motorista_id);
CREATE INDEX IF NOT EXISTS idx_entregas_camiao ON entregas(camiao_id);
CREATE INDEX IF NOT EXISTS idx_entregas_estado ON entregas(estado);
CREATE INDEX IF NOT EXISTS idx_entregas_referencia ON entregas(referencia_fmlider);
CREATE INDEX IF NOT EXISTS idx_contentores_entrega ON contentores(entrega_id);
CREATE INDEX IF NOT EXISTS idx_historico_entrega ON historico_entregas(entrega_id);
