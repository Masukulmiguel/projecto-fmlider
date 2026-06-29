-- Migration: 2024_01_09_000001_create_licenciamentos_tables.sql
-- Adds: licenciamentos, licenciamento_historico, licenciamento_estados_historico

CREATE TABLE IF NOT EXISTS licenciamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    funcionario_id BIGINT UNSIGNED NULL,
    numero_processo VARCHAR(100) UNIQUE,
    referencia VARCHAR(50) UNIQUE NOT NULL,
    tipo_licenciamento VARCHAR(100) NOT NULL,
    descricao TEXT,
    empresa VARCHAR(255),
    nif_empresa VARCHAR(50),
    estado ENUM('rascunho','pendente_cliente','submetido','em_analise','aprovado','indeferido','expira_brevemente','expirado') DEFAULT 'rascunho',
    data_submissao DATE NULL,
    data_aprovacao DATE NULL,
    data_indeferimento DATE NULL,
    data_validade DATE NULL,
    data_expiracao DATE NULL,
    observacoes TEXT,
    fonte VARCHAR(50) DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_funcionario (funcionario_id),
    INDEX idx_estado (estado),
    INDEX idx_referencia (referencia),
    INDEX idx_numero (numero_processo),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (funcionario_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS licenciamento_historico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    licenciamento_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    campo VARCHAR(100) NOT NULL,
    valor_antigo TEXT,
    valor_novo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_licenciamento (licenciamento_id),
    FOREIGN KEY (licenciamento_id) REFERENCES licenciamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS licenciamento_estados_historico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    licenciamento_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    estado_anterior VARCHAR(50),
    estado_novo VARCHAR(50) NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_licenciamento (licenciamento_id),
    FOREIGN KEY (licenciamento_id) REFERENCES licenciamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
