-- Adiciona controlo de publicacao do logo da empresa no carrossel publico "Nossos Clientes"
ALTER TABLE companies
  ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 0
    AFTER logo;

CREATE INDEX idx_companies_published ON companies (is_published, logo);
