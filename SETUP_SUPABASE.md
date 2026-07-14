# 🗃️ Configuração Supabase - Guia Completo

## 📋 Informações do Projecto

| Campo | Valor |
|-------|-------|
| 👨‍💻 **Desenvolvedor** | Masukulu Miguel |
| 🏠 **Organização** | CodingLifeDev |
| 📦 **Projecto Pai** | seefast-project |
| 📅 **Início** | Abril 2025 |
| 🏷️ **Versão Actual** | `2.2.1` |
| 🌍 **Idioma** | Português de Portugal (PT-PT) |

---

## Passo 1: Executar SQL de RLS Policies

1. 🌐 Abre o Supabase Dashboard: https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn
2. 📂 Vai a **SQL Editor** (menu lateral)
3. 📋 Copia e cola todo o conteúdo de `backend/database/supabase_rls_policies.sql`
4. 🖱️ Clica em **Run** para executar

---

## Passo 2: Configurar Auth Settings

No Supabase Dashboard, vai a **Authentication > URL Configuration**:

- 🔗 **Site URL**: `https://projecto-fmlider.vercel.app`
- 🔗 **Redirect URLs**: Adiciona estes:
  - `https://projecto-fmlider.vercel.app/**`
  - `http://localhost:5173/**` (para desenvolvimento local)

### Auth > Providers:
- 📧 **Email**: Ativado (email/password)
- 📧 **Email confirmations**: Desativado (para poder criar users sem confirmar email)
  - Ou ativar se quiseres que confirmem o email

---

## Passo 3: Criar Conta de Admin

Se ainda não tens conta de admin:

1. 👥 Vai a **Authentication > Users** no Supabase Dashboard
2. ➕ Clica em **Add user** > **Create new user**
3. 📧 Email: `admin@fmlider.co.ao` (ou o teu email)
4. 🔑 Password: escolhe uma senha forte
5. ✅ **Email Confirm**: marca como confirmado
6. 🖱️ Clica em **Create User**

Depois de criar a conta auth, precisas inserir o registo em `public.users`:

1. 📝 Vai a **SQL Editor**
2. ▶️ Executa:

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

---

## Passo 4: Configurar Storage Bucket

1. 📦 Vai a **Storage** no Supabase Dashboard
2. 🔍 Verifica se o bucket `uploads` existe
3. ➕ Se não existir, cria-o:
   - 📝 Nome: `uploads`
   - 🌐 **Public bucket**: MARCADO (para fotos serem acessíveis)
4. 📋 As storage policies já foram criadas pelo SQL

---

## Passo 5: Configurar Realtime (Opcional mas recomendado)

Para o admin ver actualizações em tempo real:

1. 📡 Vai a **Database > Replication**
2. ✅ Ativa a replicação para as tabelas:
   - `users`
   - `visitors`
   - `chat_messages`

---

## Passo 6: Verificar /oauth/consent (Se necessário)

O path `/oauth/consent` é para o **OAuth Provider** do Supabase (quando terceiros apps querem autenticar via teu Supabase). Se NÃO precisas disso, podes ignorar esta configuração.

Se precisares, o frontend precisa de uma página nesse path. O Supabase redireciona para lá quando um app externo pede autorização.

---

## Passo 7: Testar

1. 🌐 Abre `https://projecto-fmlider.vercel.app/login`
2. 🔐 Faz login com as credenciais do admin
3. ✅ Deveria redirecionar para `/admin`
4. 📊 No dashboard, deves ver os dados (clientes, visitantes, etc.)

---

## 🔧 Troubleshooting

### ❌ "Acesso negado" ou dados vazios no dashboard:
- 🔍 Verifica se o SQL de RLS foi executado correctamente
- 👤 Verifica se o admin user existe em `public.users` com `auth_id` correcto
- 🔍 Abre a consola do browser (F12) e verifica os erros na aba Console/Network

### ❌ "new row violates row-level security policy":
- ⚠️ Significa que as RLS policies não estão configuradas
- ▶️ Executa o SQL novamente

### ❌ Login funciona mas não vê dados:
- 👤 O admin precisa existir em `public.users` com `role = 'admin'`
- 🔍 Verifica com: `SELECT * FROM public.users WHERE role = 'admin';`

### ❌ Erro de conexão com Supabase:
- ⚙️ Verifica as env vars em `frontend/.env`:
  - `VITE_SUPABASE_URL=https://vsupwqxtnzdnxklgbynn.supabase.co`
  - `VITE_SUPABASE_ANON_KEY=sb_publishable_8sh450CNxnrBDINEQGL3wQ_n5F-AFXN`

---

## 🔑 Variáveis de Ambiente

```env
# 🗃️ Supabase
VITE_SUPABASE_URL=https://vsupwqxtnzdnxklgbynn.supabase.co
VITE_SUPABASE_ANON_KEY=sb_publishable_8sh450CNxnrBDINEQGL3wQ_n5F-AFXN

# 🤖 Chatbot (Groq)
GROQ_API_KEY=gsk_...
```

---

## 📊 Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| `users` | Utilizadores do sistema |
| `site_images` | Imagens do site (fundos auth, serviços) |
| `fleet_items` | Itens da frota |
| `user_presence` | Presença online/offline |
| `notifications` | Notificações do sistema |
| `chat_messages` | Mensagens do chatbot |

---

**📅 Última Actualização**: Julho 2025  
**🏷️ Versão**: `2.2.1`  
**👨‍💻 Desenvolvido por**: Masukulu Miguel (CodingLifeDev) para o projecto **seefast-project**
