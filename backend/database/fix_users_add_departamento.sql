-- Adicionar coluna departamento à tabela users
-- Executar no Supabase SQL Editor: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new

ALTER TABLE public.users ADD COLUMN IF NOT EXISTS departamento VARCHAR(50) DEFAULT '';

-- Verificar se a coluna foi criada
SELECT column_name, data_type, column_default
FROM information_schema.columns
WHERE table_name = 'users' AND column_name = 'departamento';
