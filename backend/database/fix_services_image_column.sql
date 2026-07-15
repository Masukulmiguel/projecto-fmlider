-- ============================================================
-- FIX: Alterar services.image de varchar(255) para text
-- O armazenamento de imagens em base64 exige mais de 255 caracteres
-- ============================================================

ALTER TABLE services ALTER COLUMN image TYPE text;

-- Verificar se a alteração foi aplicada
SELECT column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'services' AND column_name = 'image';
