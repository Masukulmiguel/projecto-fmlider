-- Migration: Create logistica tables for FMLider
-- Tables: motoristas, camioes, entregas, contentores, historico_entregas

CREATE TABLE IF NOT EXISTS motoristas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_completo VARCHAR(255) NOT NULL,
  bilhete_identidade VARCHAR(50),
  telefone VARCHAR(30),
  carta_conducao VARCHAR(50),
  validade_carta DATE,
  estado ENUM('ativo','inativo') DEFAULT 'ativo',
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS camioes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo_interno VARCHAR(50),
  matricula VARCHAR(20) NOT NULL,
  marca VARCHAR(100),
  modelo VARCHAR(100),
  capacidade VARCHAR(100),
  estado ENUM('disponivel','em_serviço','em_manutencao','inativo') DEFAULT 'disponivel',
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS entregas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referencia_fmlider VARCHAR(50) UNIQUE,
  referencia_cliente VARCHAR(50),
  numero_processo VARCHAR(50),
  tipologia VARCHAR(100),
  origem VARCHAR(255),
  destino VARCHAR(255),
  cliente_id INT,
  cliente_nome VARCHAR(255),
  motorista_id INT,
  camiao_id INT,
  matricula VARCHAR(20),
  estado ENUM('pendente','em_preparacao','saiu_da_base','em_transporte','chegou_cliente','entregue','cancelado') DEFAULT 'pendente',
  data_saida DATETIME,
  data_prevista DATE,
  data_entrega DATETIME,
  observacoes TEXT,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE SET NULL,
  FOREIGN KEY (camiao_id) REFERENCES camioes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contentores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entrega_id INT NOT NULL,
  numero VARCHAR(50),
  tipo VARCHAR(50),
  estado VARCHAR(100),
  data_entrega DATETIME,
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historico_entregas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entrega_id INT NOT NULL,
  estado_anterior VARCHAR(50),
  estado_novo VARCHAR(50),
  utilizador_id INT,
  utilizador_nome VARCHAR(255),
  observacoes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Performance indexes
CREATE INDEX idx_entregas_cliente ON entregas(cliente_id);
CREATE INDEX idx_entregas_motorista ON entregas(motorista_id);
CREATE INDEX idx_entregas_camiao ON entregas(camiao_id);
CREATE INDEX idx_entregas_estado ON entregas(estado);
CREATE INDEX idx_entregas_referencia ON entregas(referencia_fmlider);
CREATE INDEX idx_contentores_entrega ON contentores(entrega_id);
CREATE INDEX idx_historico_entrega ON historico_entregas(entrega_id);