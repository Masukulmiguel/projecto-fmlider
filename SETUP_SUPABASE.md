# Configuracao Supabase - Guia Completo

## Passo 1: Executar SQL de RLS Policies

1. Abre o Supabase Dashboard: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn
2. Vai a **SQL Editor** (menu lateral)
3. Copia e cola todo o conteudo de `backend/database/supabase_rls_policies.sql`
4. Clica em **Run** para executar

## Passo 2: Configurar Auth Settings

No Supabase Dashboard, vai a **Authentication > URL Configuration**:

- **Site URL**: `https://fmlider-66.vercel.app`
- **Redirect URLs**: Adiciona estes:
  - `https://fmlider-66.vercel.app/**`
  - `http://localhost:5173/**` (para desenvolvimento local)

### Auth > Providers:
- **Email**: Ativado (email/password)
- **Email confirmations**: Desativado (para poder criar users sem confirmar email)
  - Ou ativar se quiseres que confirmem o email

## Passo 3: Criar Conta de Admin

Se ainda nao tens conta de admin:

1. Vai a **Authentication > Users** no Supabase Dashboard
2. Clica em **Add user** > **Create new user**
3. Email: `admin@fmlider.co.ao` (ou o teu email)
4. Password: escolhe uma senha forte
5. **Email Confirm**: marca como confirmado
6. Clica em **Create User**

Depois de criar a conta auth, precisas inserir o registo em `public.users`:

1. Vai a **SQL Editor**
2. Executa:

```sql
INSERT INTO public.users (
  auth_id, username, name, email, phone, role,
  approval_status, status, permissions, password,
  company_completed, created_at
) VALUES (
  (SELECT id FROM auth.users WHERE email = 'admin@fmlider.co.ao' LIMIT 1),
  'admin',
  'Administrador',
  'admin@fmlider.co.ao',
  '+244 935141747',
  'admin',
  'approved',
  1,
  '["admin"]',
  'supabase_auth_managed',
  true,
  NOW()
) ON CONFLICT (auth_id) DO NOTHING;
```

## Passo 4: Configurar Storage Bucket

1. Vai a **Storage** no Supabase Dashboard
2. Verifica se o bucket `photos` existe
3. Se nao existir, cria-o:
   - Nome: `photos`
   - **Public bucket**: MARCADO (para fotos serem acessiveis)
4. As storage policies ja foram criadas pelo SQL

## Passo 5: Configurar Realtime (Opcional mas recomendado)

Para o admin ver atualizacoes em tempo real:

1. Vai a **Database > Replication**
2. Ativa a replicacao para as tabelas:
   - `users`
   - `visitors`
   - `chat_messages`

## Passo 6: Verificar /oauth/consent (Se necessario)

O path `/oauth/consent` e para o **OAuth Provider** do Supabase (quando terceiros apps querem autenticar via teu Supabase). Se NAO precisas disso, podes ignorar esta configuracao.

Se precisares, o frontend precisa de uma pagina nesse path. O Supabase redireciona para la quando um app externo pede autorizacao.

## Passo 7: Testar

1. Abre `https://fmlider-66.vercel.app/login`
2. Faz login com as credenciais do admin
3. Deveria redirecionar para `/admin`
4. No dashboard, deves ver os dados (clientes, visitantes, etc.)

## Troubleshooting

### "Acesso negado" ou dados vazios no dashboard:
- Verifica se o SQL de RLS foi executado corretamente
- Verifica se o admin user existe em `public.users` com `auth_id` correto
- Abre a consola do browser (F12) e verifica os erros na aba Console/Network

### "new row violates row-level security policy":
- Significa que as RLS policies nao estao configuradas
- Executa o SQL novamente

### Login funciona mas nao ve dados:
- O admin precisa existir em `public.users` com `role = 'admin'`
- Verifica com: `SELECT * FROM public.users WHERE role = 'admin';`

### Erro de conexao com Supabase:
- Verifica as env vars em `frontend/.env`:
  - `VITE_SUPABASE_URL=https://vsupwqxtnzdnxklgbynn.supabase.co`
  - `VITE_SUPABASE_ANON_KEY=sb_publishable_8sh450CNxnrBDINEQGL3wQ_n5F-AFXN`
