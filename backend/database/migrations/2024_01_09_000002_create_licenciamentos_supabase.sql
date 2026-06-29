-- Migration: licenciamentos tables for Supabase (PostgreSQL)

CREATE TABLE IF NOT EXISTS public.licenciamentos (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    funcionario_id BIGINT NULL,
    numero_processo VARCHAR(100) UNIQUE,
    referencia VARCHAR(50) UNIQUE NOT NULL,
    tipo_licenciamento VARCHAR(100) NOT NULL,
    descricao TEXT,
    empresa VARCHAR(255),
    nif_empresa VARCHAR(50),
    estado VARCHAR(50) DEFAULT 'rascunho',
    data_submissao DATE NULL,
    data_aprovacao DATE NULL,
    data_indeferimento DATE NULL,
    data_validade DATE NULL,
    data_expiracao DATE NULL,
    observacoes TEXT,
    fonte VARCHAR(50) DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_lic_user ON public.licenciamentos(user_id);
CREATE INDEX IF NOT EXISTS idx_lic_func ON public.licenciamentos(funcionario_id);
CREATE INDEX IF NOT EXISTS idx_lic_estado ON public.licenciamentos(estado);
CREATE INDEX IF NOT EXISTS idx_lic_ref ON public.licenciamentos(referencia);
CREATE INDEX IF NOT EXISTS idx_lic_numero ON public.licenciamentos(numero_processo);

CREATE TABLE IF NOT EXISTS public.licenciamento_historico (
    id BIGSERIAL PRIMARY KEY,
    licenciamento_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    campo VARCHAR(100) NOT NULL,
    valor_antigo TEXT,
    valor_novo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_lic_hist ON public.licenciamento_historico(licenciamento_id);

CREATE TABLE IF NOT EXISTS public.licenciamento_estados_historico (
    id BIGSERIAL PRIMARY KEY,
    licenciamento_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    estado_anterior VARCHAR(50),
    estado_novo VARCHAR(50) NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_lic_est_hist ON public.licenciamento_estados_historico(licenciamento_id);

-- Desactivar RLS (padrão do projecto)
ALTER TABLE public.licenciamentos DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_historico DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_estados_historico DISABLE ROW LEVEL SECURITY;
