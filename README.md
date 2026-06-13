# FMLider Transitário & Logística - Website

Um website corporativo premium para FMLider Transitário & Logística desenvolvido com Vue.js 3, PHP puro, e MySQL.

## 🌐 Site ao Vivo

**https://fmlider-66.vercel.app/**

## 🚀 Características

- **Frontend Moderno**: Vue.js 3 com Vite, Bootstrap 5, e design responsivo
- **Backend Robusto**: Laravel-like API REST em PHP puro
- **Banco de Dados**: MySQL 8 com migrations completas
- **Autenticação Segura**: Sistema de login, registro e recuperação de senha
- **Painel Administrativo**: Dashboard completo com CRUD para todos os módulos
- **SEO Otimizado**: Sitemap, robots.txt, meta tags, Schema.org
- **Segurança**: CSRF, XSS, SQL Injection protections
- **Responsivo**: Mobile-first, funciona em todos os dispositivos
- **Lazy Loading**: Carregamento otimizado de imagens
- **Animações Suaves**: Transições e efeitos CSS3

## 📋 Requisitos

- PHP 7.4+
- MySQL 8.0+
- Node.js 14+
- Composer (opcional)

## 📦 Instalação

### 1. Preparar o Banco de Dados

```bash
# Criar a base de dados
mysql -u root -p < backend/database/migrations/schema.sql
```

### 2. Frontend

```bash
cd frontend

# Instalar dependências
npm install

# Desenvolvimento
npm run dev

# Build para produção
npm run build
```

### 3. Backend

O backend está pronto para usar em `http://localhost:8000/api`

## 🏗️ Estrutura do Projeto

```
fmlider.co.ao/
├── assets/              # Imagens e recursos estáticos
│   └── img/            # Imagens da empresa
├── backend/            # API REST em PHP
│   ├── app/
│   │   ├── Models/     # Modelos de dados
│   │   ├── Controllers/ # Controladores da API
│   │   └── Middleware/ # Middleware de autenticação
│   ├── database/
│   │   ├── migrations/ # SQL migrations
│   │   └── seeders/    # Data seeders
│   ├── routes/         # Definição de rotas
│   ├── index.php       # Entry point da API
│   └── .htaccess       # Configuração Apache
├── frontend/           # Aplicação Vue.js 3
│   ├── src/
│   │   ├── components/ # Componentes reutilizáveis
│   │   ├── pages/      # Páginas públicas
│   │   ├── admin/      # Painel administrativo
│   │   ├── stores/     # Estado global (Pinia)
│   │   ├── router/     # Configuração de rotas
│   │   └── main.js     # Entry point
│   ├── vite.config.js  # Configuração Vite
│   └── package.json    # Dependências Node
└── prd.md             # Product Requirements Document
```

## 🔑 Credenciais Padrão

- **Email**: admin@fmlider.co.ao
- **Senha**: Admin@2026

## 🌐 Endpoints da API

### Públicos

```
GET    /api/services              - Lista de serviços
GET    /api/news                  - Notícias
GET    /api/gallery               - Galeria
GET    /api/partners              - Parceiros
GET    /api/testimonials          - Testemunhos
GET    /api/faqs                  - FAQs
POST   /api/contacts              - Enviar contacto
```

### Autenticação

```
POST   /api/auth/login            - Login
POST   /api/auth/register         - Registro
POST   /api/auth/forgot-password  - Recuperar senha
GET    /api/auth/profile          - Perfil do utilizador
```

### Admin (Requer autenticação)

```
CRUD completo para:
- /api/admin/users
- /api/admin/services
- /api/admin/news
- /api/admin/gallery
- /api/admin/partners
- /api/admin/contacts
- /api/admin/testimonials
- /api/admin/faqs
- /api/admin/banners
```

## 🎨 Identidade Visual

- **Cor Primária**: Azul #007bff
- **Cores Secundárias**: Branco, Preto
- **Tipografia**: Bootstrap defaults
- **Inspiração**: DHL, Maersk, MSC

## 📱 Páginas Principais

### Público

- **Início**: Hero carousel, serviços, números, frota, notícias
- **Sobre Nós**: História, missão, visão, valores
- **Serviços**: Listagem de 4 serviços principais
- **Frota**: Galeria de camiões, contentores, equipamentos
- **Notícias**: Blog de notícias e atualizações
- **Galeria**: Galeria de imagens da empresa
- **Contactos**: Formulário de contacto com mapa
- **Login/Registro**: Sistema de autenticação

### Admin

- **Dashboard**: Estatísticas e atividades recentes
- **Utilizadores**: CRUD de utilizadores
- **Serviços**: Gestão de serviços
- **Notícias**: Publicação de notícias
- **Galeria**: Upload e gestão de imagens
- **Parceiros**: Gestão de parceiros
- **Contactos**: Visualização e resposta a mensagens
- **Testemunhos**: Gestão de testemunhos
- **FAQs**: Gestão de perguntas frequentes
- **Banners**: Gestão de banners promocionais
- **Perfil**: Edição de dados e alteração de senha

## 🔐 Segurança

- ✅ CSRF Protection
- ✅ XSS Protection
- ✅ SQL Injection Prevention
- ✅ Password Hashing (BCrypt)
- ✅ Bearer Token Authentication
- ✅ CORS Configured
- ✅ Rate Limiting Ready
- ✅ Activity Logging

## ⚡ Performance

- Lazy loading de imagens
- Compressão GZIP habilitada
- Caching de imagens
- Minificação de CSS/JS
- Carregamento assíncrono
- Otimização de bundle

## 📊 SEO

- ✅ Sitemap.xml
- ✅ Robots.txt
- ✅ Meta tags dinâmicas
- ✅ Open Graph support
- ✅ Schema.org structured data
- ✅ URLs amigáveis
- ✅ Breadcrumbs
- ✅ Mobile-friendly

## 🚀 Deployment

### Produção

```bash
# Frontend
cd frontend
npm run build
# Fazer upload de 'dist' para o servidor

# Backend
# Upload de todos os arquivos via FTP/SFTP
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name fmlider.co.ao;
    
    root /var/www/fmlider.co.ao;
    
    location /api {
        try_files $uri $uri/ /backend/index.php?$query_string;
    }
    
    location / {
        try_files $uri $uri/ /frontend/index.html;
    }
}
```

## 📝 Configuração MySQL

```sql
-- Criar base de dados
CREATE DATABASE fmlider CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar a base de dados
USE fmlider;

-- Importar schema
SOURCE backend/database/migrations/schema.sql;
```

## 🛠️ Desenvolvimento

### Variáveis de Ambiente

Backend (.env):
```
DB_HOST=localhost
DB_NAME=fmlider
DB_USER=root
DB_PASS=

APP_KEY=your-secret-key
APP_URL=http://localhost:8000
```

Frontend (.env):
```
VITE_API_URL=http://localhost:8000/api
```

### Hot Reload (Frontend)

```bash
cd frontend
npm run dev
```

### Scripts Úteis

```bash
# Frontend
npm install     # Instalar dependências
npm run dev     # Desenvolvimento
npm run build   # Build de produção
npm run preview # Preview do build

# Backend
# Nenhum script necessário (PHP puro)
```

## 📄 Documentação

- Product Requirements Document: [prd.md](prd.md)
- API Routes: [backend/routes/api.php](backend/routes/api.php)
- Database Schema: [backend/database/migrations/schema.sql](backend/database/migrations/schema.sql)

## 🐛 Troubleshooting

### A API não responde

```
1. Verificar se o backend está acessível em http://localhost:8000/api
2. Verificar o arquivo .htaccess no backend
3. Verificar se mod_rewrite está ativado no Apache
```

### Frontend não carrega dados

```
1. Verificar na console do browser os erros CORS
2. Certificar que a API está rodando
3. Verificar VITE_API_URL no .env
```

### Banco de dados não funciona

```
1. Verificar se MySQL está rodando
2. Executar schema.sql
3. Verificar as credenciais de acesso
4. Verificar privilégios do utilizador MySQL
```

## 📞 Suporte

Para dúvidas ou problemas:
- Email: geral@fmlider.co.ao
- Telefone: +244 935141747

## 📜 Licença

© 2024 FMLider Transitário & Logística. Todos os direitos reservados.

---

**Desenvolvido com ❤️ para FMLider**
