-- ============================================================
-- UPDATE uploads bucket to accept more file types for chat
-- Execute this in Supabase SQL Editor
-- ============================================================

-- Update bucket to accept all common file types
UPDATE storage.buckets
SET allowed_mime_types = ARRAY[
  'image/jpeg','image/png','image/gif','image/webp','image/svg+xml',
  'application/pdf',
  'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation',
  'text/plain','text/csv',
  'application/zip','application/x-rar-compressed',
  'audio/mpeg','audio/wav','audio/ogg',
  'video/mp4','video/webm',
  'application/octet-stream'
],
file_size_limit = 26214400
WHERE id = 'uploads';

-- Also create a 'chat-files' bucket as backup for chat-specific files
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES ('chat-files', 'chat-files', true, 26214400, ARRAY[
  'image/jpeg','image/png','image/gif','image/webp',
  'application/pdf',
  'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'text/plain','text/csv',
  'application/zip',
  'audio/mpeg','audio/wav',
  'video/mp4','video/webm',
  'application/octet-stream'
])
ON CONFLICT (id) DO NOTHING;

-- Policies for chat-files bucket
DO $$ BEGIN DROP POLICY IF EXISTS "public_read_chat_files" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "public_read_chat_files" ON storage.objects
  FOR SELECT USING (bucket_id = 'chat-files');

DO $$ BEGIN DROP POLICY IF EXISTS "authenticated_upload_chat_files" ON storage.objects; EXCEPTION WHEN OTHERS THEN NULL; END $$;
CREATE POLICY "authenticated_upload_chat_files" ON storage.objects
  FOR INSERT WITH CHECK (
    bucket_id = 'chat-files'
    AND auth.role() = 'authenticated'
  );
