# 🚀 FMLIDER Website - Setup & Installation Guide

## ✅ Checklist de Instalação

### 1️⃣ Preparação do Ambiente

- [ ] Verificar PHP 7.4+ instalado: `php -v`
- [ ] Verificar MySQL 8.0+ instalado e rodando
- [ ] Verificar Node.js 14+ instalado: `node -v`
- [ ] Verificar npm instalado: `npm -v`

### 2️⃣ Configuração do Banco de Dados

```bash
# Abrir MySQL
mysql -u root -p

# Criar base de dados
CREATE DATABASE fmlider CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Usar a base de dados
USE fmlider;

# Importar o schema
source C:/xampp/htdocs/fmlider.co.ao/backend/database/migrations/schema.sql;

# Sair
exit;
```

### 3️⃣ Instalar Dependências - Frontend

```bash
# Navegar para a pasta frontend
cd C:\xampp\htdocs\fmlider.co.ao\frontend

# Copiar imagens para a pasta public (IMPORTANTE!)
# Windows - PowerShell
Copy-Item -Path ..\assets\img\* -Destination .\public\assets\img\ -Force

# Ou Windows - CMD
xcopy ..\assets\img\* .\public\assets\img\ /Y

# Instalar dependências npm
npm install

# Verificar instalação
npm list
```

### 4️⃣ Configurar Apache

#### Virtual Host (Windows)

Editar: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    ServerName fmlider.local
    DocumentRoot "C:/xampp/htdocs/fmlider.co.ao"
    
    <Directory "C:/xampp/htdocs/fmlider.co.ao">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Rotas Frontend
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^frontend/(.*)$ frontend/index.html [L]
    </IfModule>
</VirtualHost>
```

#### Editar Hosts (Windows)

Editar: `C:\Windows\System32\drivers\etc\hosts`

Adicionar:
```
127.0.0.1   fmlider.local
127.0.0.1   api.fmlider.local
```

#### Ativar mod_rewrite

```
# No XAMPP
1. Abrir Apache Config
2. Descomentar: LoadModule rewrite_module modules/mod_rewrite.so
3. Reiniciar Apache
```

### 5️⃣ Dados Iniciais (Seeder)

```bash
# Navegar para a pasta backend
cd C:\xampp\htdocs\fmlider.co.ao\backend

# Executar seeder (se estiver em CLI)
php database/seeders/DatabaseSeeder.php

# Ou executar SQL manualmente
mysql -u root -p fmlider < database/migrations/schema.sql
```

### 6️⃣ Iniciar Servidor Frontend (Desenvolvimento)

```bash
# Na pasta frontend
npm run dev

# A aplicação estará disponível em:
# http://localhost:5173
```

### 7️⃣ Testar a API

```bash
# Teste de login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@fmlider.co.ao","password":"Admin@2026"}'

# Teste de serviços
curl -X GET http://localhost:8000/api/services
```

---

## 📁 Estrutura de Arquivos Criados

### Backend
```
backend/
├── index.php                          # Entry point da API
├── .htaccess                          # Configuração Apache
├── app/
│   ├── Models/                        # Modelos de dados
│   │   ├── User.php
│   │   ├── Service.php
│   │   ├── News.php
│   │   ├── Gallery.php
│   │   ├── Partner.php
│   │   ├── Contact.php
│   │   ├── Testimonial.php
│   │   ├── FAQ.php
│   │   ├── Banner.php
│   │   ├── ActivityLog.php
│   │   └── Setting.php
│   ├── Controllers/                   # Controladores da API
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ServiceController.php
│   │   ├── NewsController.php
│   │   ├── GalleryController.php
│   │   ├── PartnerController.php
│   │   ├── ContactController.php
│   │   ├── TestimonialController.php
│   │   ├── FAQController.php
│   │   └── BannerController.php
│   └── Middleware/
│       ├── AuthMiddleware.php
│       └── PermissionMiddleware.php
├── database/
│   ├── migrations/
│   │   └── schema.sql                 # Todas as tabelas
│   └── seeders/
│       └── DatabaseSeeder.php         # Dados iniciais
├── routes/
│   └── api.php                        # Definição de rotas
├── config/
│   └── database.php                   # Configuração MySQL
└── storage/
    └── uploads/                       # Armazenamento de arquivos
```

### Frontend
```
frontend/
├── index.html                         # HTML principal
├── package.json                       # Dependências Node
├── vite.config.js                     # Configuração Vite
├── src/
│   ├── main.js                        # Entry point
│   ├── App.vue                        # Componente raiz
│   ├── router/
│   │   └── index.js                   # Configuração Vue Router
│   ├── stores/
│   │   └── authStore.js               # Pinia store de autenticação
│   ├── api/                           # (Pronto para serviços API)
│   ├── utils/                         # (Pronto para utilitários)
│   ├── components/
│   │   ├── PublicHeader.vue           # Cabeçalho público
│   │   ├── PublicFooter.vue           # Rodapé público
│   │   ├── ServiceCard.vue            # Cartão de serviço
│   │   ├── Counter.vue                # Contador animado
│   │   ├── GalleryCarousel.vue        # Carrossel de galeria
│   │   ├── NewsCard.vue               # Cartão de notícia
│   │   └── PartnersCarousel.vue       # Carrossel de parceiros
│   ├── pages/
│   │   ├── Home.vue                   # Página inicial
│   │   ├── About.vue                  # Sobre nós
│   │   ├── Services.vue               # Serviços
│   │   ├── ServiceDetail.vue          # Detalhe do serviço
│   │   ├── Fleet.vue                  # Frota
│   │   ├── News.vue                   # Notícias
│   │   ├── NewsDetail.vue             # Detalhe da notícia
│   │   ├── Gallery.vue                # Galeria
│   │   ├── Contact.vue                # Contactos
│   │   └── auth/
│   │       ├── Login.vue              # Login
│   │       ├── Register.vue           # Registro
│   │       ├── ForgotPassword.vue     # Recuperação de senha
│   │       └── ResetPassword.vue      # Redefinição de senha
│   └── admin/
│       ├── components/
│       │   ├── AdminSidebar.vue       # Menu lateral admin
│       │   └── AdminNavbar.vue        # Barra superior admin
│       └── pages/
│           ├── Dashboard.vue          # Dashboard
│           ├── Users.vue              # Gestão de utilizadores
│           ├── Services.vue           # Gestão de serviços
│           ├── News.vue               # Gestão de notícias
│           ├── Gallery.vue            # Gestão de galeria
│           ├── Partners.vue           # Gestão de parceiros
│           ├── Contacts.vue           # Visualização de contactos
│           ├── Testimonials.vue       # Gestão de testemunhos
│           ├── FAQs.vue               # Gestão de FAQs
│           ├── Banners.vue            # Gestão de banners
│           └── Profile.vue            # Perfil do utilizador
```

### Raiz do Projeto
```
fmlider.co.ao/
├── README.md                          # Documentação principal
├── API_DOCUMENTATION.md               # Documentação da API
├── prd.md                             # Product Requirements
├── sitemap.xml                        # Sitemap para SEO
├── robots.txt                         # Arquivo robots
├── .gitignore                         # Git ignore
├── assets/
│   └── img/                           # Imagens da empresa
├── backend/                           # API REST
├── frontend/                          # Aplicação Vue.js
```

---

## 🔑 Credenciais Padrão

| Campo | Valor |
|-------|-------|
| Email | admin@fmlider.co.ao |
| Senha | Admin@2026 |
| Função | admin |

---

## 🌐 URLs de Acesso

| Serviço | URL | Descrição |
|---------|-----|-----------|
| Frontend Dev | http://localhost:5173 | Aplicação Vue.js em desenvolvimento |
| Frontend Prod | http://fmlider.local | Aplicação compilada |
| API | http://localhost:8000/api | Endpoints da API |
| Admin | http://localhost:5173/admin | Painel administrativo |
| Database | localhost:3306 | MySQL |

---

## 🧪 Testes

### Teste Frontend (Hot Reload)

```bash
cd frontend
npm run dev
# Abrir http://localhost:5173
# Editar um arquivo .vue e ver as mudanças em tempo real
```

### Teste API

```bash
# Teste de login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@fmlider.co.ao","password":"Admin@2026"}'

# Teste de serviços
curl http://localhost:8000/api/services
```

### Teste de Banco de Dados

```bash
mysql -u root -p fmlider
SELECT * FROM users;
SELECT * FROM services;
EXIT;
```

---

## 🚀 Build para Produção

### Frontend

```bash
cd frontend

# Gerar build otimizado
npm run build

# Visualizar build antes de deploy
npm run preview

# Arquivo gerado: dist/
```

### Deploy

1. Fazer upload de `frontend/dist/` para o servidor web
2. Fazer upload de `backend/` para o servidor
3. Configurar MySQL na produção
4. Atualizar variáveis de ambiente
5. Testar endpoints

---

## ⚠️ Troubleshooting

### Erro: "Cannot find module 'vue'"

```bash
cd frontend
npm install
```

### Erro: "Connection refused" na API

Verificar:
1. Se o backend está rodando
2. URLs de API estão corretas
3. CORS está configurado

### Erro: "Database connection failed"

Verificar:
1. MySQL está rodando
2. Credenciais corretas
3. Banco de dados foi criado

### Erro: "mod_rewrite not enabled"

```
1. XAMPP > Apache > Config > httpd.conf
2. Descomentar: LoadModule rewrite_module modules/mod_rewrite.so
3. Reiniciar Apache
```

### Erro: "Imagens não aparecem no site"

Verifique se as imagens foram copiadas para `frontend/public/assets/img/`:

```bash
# Se não estiverem, copie manualmente:
cd C:\xampp\htdocs\fmlider.co.ao\frontend

# PowerShell
Copy-Item -Path ..\assets\img\* -Destination .\public\assets\img\ -Force

# Ou CMD
xcopy ..\assets\img\* .\public\assets\img\ /Y

# Depois reinicie o dev server
npm run dev
```

---

## 📚 Recursos

- [Vue.js 3 Documentation](https://v3.vuejs.org/)
- [Vite Documentation](https://vitejs.dev/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PHP Documentation](https://www.php.net/manual/)

---

## 💡 Próximos Passos

1. ✅ Backend API funcional
2. ✅ Frontend Vue.js funcional
3. ✅ Banco de dados configurado
4. ⏳ Integração completa API-Frontend
5. ⏳ Sistema de notificações
6. ⏳ Dashboard analytics
7. ⏳ Email notifications
8. ⏳ Two-factor authentication

---

**Última Atualização**: Janeiro 2024  
**Status**: ✅ Pronto para Desenvolvimento
