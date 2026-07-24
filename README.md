# 🚀 FMLider Transitário & Logística - Website Corporativo

Um website corporativo premium para a **FMLider Transitário & Logística** desenvolvido com Vue.js 3, PHP puro e MySQL.

---

## 📋 Informações do Projecto

| Campo | Valor |
|-------|-------|
| 🏢 **Empresa** | FMLider Transitário & Logística |
| 👨‍💻 **Desenvolvedor** | Masukulu Miguel |
| 🏠 **Organização** | [CodingLifeDev](https://github.com/codinglifedev) |
| 📦 **Projecto Pai** | seefast-project |
| 📅 **Início** | Abril 2025 |
| 🏷️ **Versão Actual** | `2.2.1` |
| 🌍 **Idioma** | Português de Portugal (PT-PT) |
| 🌐 **Site ao Vivo** | [https://fmlider.co.ao](https://fmlider.co.ao) |
| 📂 **Repositório** | [GitHub](https://github.com/Masukulmiguel/projecto-fmlider) |
| 🚀 **Deploy** | Vercel (frontend) |

---

## ✨ Funcionalidades Principais

### 🖥️ Frontend Moderno
- ⚡ Vue.js 3 + Vite + Bootstrap 5
- 📱 Design responsivo mobile-first
- 🎨 UI/UX profissional inspirado em DHL, Maersk, MSC
- 🌐 Internacionalização PT/EN/FR
- 🔒 Autenticação via Supabase
- 🤖 Chatbot com IA (Groq API)
- 📍 Rastreamento de contentores e BLs
- 🔍 Lookup de BI/NIF

### 🛡️ Backend Robusto
- 🐘 PHP puro (Laravel-like API REST)
- 🗃️ MySQL 8 com migrations completas
- 🔑 Autenticação Bearer Token
- 🛡️ Protecção CSRF, XSS, SQL Injection
- 📝 Activity Logging completo
- 📤 Upload de imagens (base64 + Supabase Storage)

### 📊 Painel Administrativo
- 📈 Dashboard com estatísticas
- 👥 Gestão de utilizadores
- 📰 Gestão de notícias
- 🖼️ Gestão de galeria
- 🤝 Gestão de parceiros
- 📞 Gestão de contactos
- ⭐ Gestão de testemunhos
- ❓ Gestão de FAQs
- 📢 Gestão de banners
- 🚛 Gestão de frota
- 📦 Gestão de contentores
- 🔔 Sistema de notificações

---

## 📋 Requisitos

- 🐘 PHP 7.4+
- 🗃️ MySQL 8.0+
- 📦 Node.js 14+
- 📦 npm 6+

---

## 📦 Instalação Rápida

### 1️⃣ Preparar a Base de Dados

```bash
mysql -u root -p < backend/database/migrations/schema.sql
```

### 2️⃣ Frontend

```bash
cd frontend

# Instalar dependências
npm install

# Desenvolvimento
npm run dev

# Build para produção
npm run build
```

### 3️⃣ Backend

```bash
# O backend está pronto para usar em:
# http://localhost:8000/api
```

---

## 🏗️ Estrutura do Projecto

```
fmlider.co.ao/
├── 📄 README.md                    # Este ficheiro
├── 📄 CONTEXT.md                   # Contexto do projecto
├── 📄 AGENTS.md                    # Regras do projecto
├── 📄 FEATURES.md                  # Lista de funcionalidades
├── 📄 API_DOCUMENTATION.md         # Documentação da API
├── 📄 SETUP_GUIDE.md              # Guia de instalação
├── 📄 SETUP_SUPABASE.md           # Configuração Supabase
├── 📄 DELIVERY_CHECKLIST.md        # Checklist de entrega
├── 📄 FINAL_STATUS_REPORT.md       # Relatório final
├── 📄 PROJECT_COMPLETION_SUMMARY.md # Resumo do projecto
├── 📄 prd.md                       # Requisitos do produto
├── 🖼️ assets/                      # Imagens e recursos
├── 🗄️ backend/                     # API REST em PHP
│   ├── 📂 app/
│   │   ├── 📂 Models/              # Modelos de dados
│   │   ├── 📂 Controllers/         # Controladores da API
│   │   └── 📂 Middleware/          # Middleware de auth
│   ├── 📂 database/                # Migrations SQL
│   ├── 📂 routes/                  # Definição de rotas
│   ├── 📄 index.php                # Entry point da API
│   └── 📄 .htaccess               # Configuração Apache
├── 💻 frontend/                    # Aplicação Vue.js 3
│   ├── 📂 src/
│   │   ├── 📂 components/          # Componentes reutilizáveis
│   │   ├── 📂 pages/               # Páginas públicas
│   │   ├── 📂 admin/               # Painel administrativo
│   │   ├── 📂 stores/              # Estado global (Pinia)
│   │   ├── 📂 composable/          # Composables Vue
│   │   ├── 📂 locales/             # Internacionalização
│   │   └── 📄 main.js              # Entry point
│   ├── 📄 vite.config.js           # Configuração Vite
│   └── 📄 package.json             # Dependências Node
└── 📄 prd.md                       # Requisitos do produto
```

---

## 🌐 Endpoints da API

### 🌍 Públicos

```
GET    /api/services              - 📋 Lista de serviços
GET    /api/news                  - 📰 Notícias
GET    /api/gallery               - 🖼️ Galeria
GET    /api/partners              - 🤝 Parceiros
GET    /api/testimonials          - ⭐ Testemunhos
GET    /api/faqs                  - ❓ FAQs
POST   /api/contacts              - 📞 Enviar contacto
```

### 🔐 Autenticação

```
POST   /api/auth/login            - 🔑 Login
POST   /api/auth/register         - 📝 Registo
POST   /api/auth/forgot-password  - 🔄 Recuperar senha
GET    /api/auth/profile          - 👤 Perfil do utilizador
```

### 🛡️ Admin (Requer autenticação)

```
CRUD completo para:
- /api/admin/users               - 👥 Utilizadores
- /api/admin/services            - 🔧 Serviços
- /api/admin/news                - 📰 Notícias
- /api/admin/gallery             - 🖼️ Galeria
- /api/admin/partners            - 🤝 Parceiros
- /api/admin/contacts            - 📞 Contactos
- /api/admin/testimonials        - ⭐ Testemunhos
- /api/admin/faqs                - ❓ FAQs
- /api/admin/banners             - 📢 Banners
```

---

## 🎨 Identidade Visual

- 🎨 **Cor Primária**: Azul #1a365d
- ✨ **Cor Secundária**: Dourado #d4af37
- ⚪ **Fundo**: Branco #ffffff
- 🔤 **Tipografia**: Bootstrap defaults
- 🎯 **Inspiração**: DHL, Maersk, MSC

---

## 📱 Páginas Principais

### 🌍 Público
- 🏠 **Início**: Hero carousel, serviços, números, frota, notícias
- 📖 **Sobre Nós**: História, missão, visão, valores
- 🔧 **Serviços**: Listagem de serviços principais
- 🚛 **Frota**: Galeria de camiões, contentores, equipamentos
- 📰 **Notícias**: Blog de notícias e actualizações
- 🖼️ **Galeria**: Galeria de imagens da empresa
- 📞 **Contactos**: Formulário de contacto com mapa
- 🔑 **Login/Registo**: Sistema de autenticação

### 🛡️ Admin
- 📊 **Dashboard**: Estatísticas e actividades recentes
- 👥 **Utilizadores**: CRUD de utilizadores
- 🔧 **Serviços**: Gestão de serviços
- 📰 **Notícias**: Publicação de notícias
- 🖼️ **Galeria**: Upload e gestão de imagens
- 🤝 **Parceiros**: Gestão de parceiros
- 📞 **Contactos**: Visualização e resposta a mensagens
- ⭐ **Testemunhos**: Gestão de testemunhos
- ❓ **FAQs**: Gestão de perguntas frequentes
- 📢 **Banners**: Gestão de banners promocionais
- 🚛 **Frota**: Gestão de frota
- 📦 **Contentores**: Gestão de contentores
- 🔔 **Notificações**: Sistema de notificações
- 👤 **Perfil**: Edição de dados e alteração de senha

---

## 🔐 Segurança

- ✅ CSRF Protection
- ✅ XSS Protection
- ✅ SQL Injection Prevention
- ✅ Password Hashing (BCrypt)
- ✅ Bearer Token Authentication
- ✅ CORS Configured
- ✅ Rate Limiting
- ✅ Activity Logging
- ✅ RLS Policies (Supabase)

---

## ⚡ Performance

- ⚡ Lazy loading de imagens
- 📦 Compressão GZIP habilitada
- 💾 Caching de imagens
- 📦 Minificação de CSS/JS
- 🔄 Carregamento assíncrono
- 🎯 Otimização de bundle

---

## 📊 SEO

- ✅ Sitemap.xml
- ✅ Robots.txt
- ✅ Meta tags dinâmicas
- ✅ Open Graph support
- ✅ Schema.org structured data
- ✅ URLs amigáveis
- ✅ Breadcrumbs
- ✅ Mobile-friendly

---

## 🛠️ Comandos Úteis

```bash
# 🚀 Iniciar frontend em desenvolvimento
cd frontend && npm run dev

# 🏗️ Build para produção
cd frontend && npm run build

# 📦 Instalar dependências
cd frontend && npm install
```

---

## 📞 Contactos do Desenvolvedor

| Campo | Valor |
|-------|-------|
| 👨‍💻 **Nome** | Masukulu Miguel |
| 🏠 **Organização** | CodingLifeDev |
| 📧 **Email** | masukulum@gmail.com |
| 📱 **Telefone** | +244 935603163 |
| 🌐 **GitHub** | [Masukulmiguel](https://github.com/Masukulmiguel) |

---

## 📜 Licença

© 2025 FMLider Transitário & Logística. Todos os direitos reservados.

Desenvolvido por  **Masukulu Miguel** para o projecto **seefast-project**.

---

**🏷️ Versão Actual**: `2.2.1`  
**📅 Última Actualização**: Julho 2025  
**🌍 Idioma**: Português de Portugal (PT-PT)
