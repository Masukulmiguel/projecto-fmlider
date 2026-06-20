# Contexto do Projecto - FMLider

## O que é este projecto
Website corporativo premium para **FMLider Transitário & Logística** (Angola).
- Frontend: Vue.js 3 + Vite + Bootstrap 5
- Backend: PHP puro (Laravel-like API REST)
- BD: MySQL 8
- Deploy: Vercel (frontend), XAMPP (local)

## Estado Actual
- Site em manutenção (mensagem de manutenção activa)
- Frontend em `frontend/` com Vue 3
- Backend em `backend/` com API PHP
- Painel de cliente em desenvolvimento

## Estrutura Principal
```
frontend/
  src/
    pages/        # Páginas públicas (Home, Services, Contact, etc.)
    pages/cliente/ # Painel do cliente (Dashboard, Embarques, Cotações, etc.)
    components/   # Componentes reutilizáveis
    stores/       # Pinia stores (auth, chat, company, etc.)
    locales/      # pt.js, en.js (internacionalização)
    lib/          # supabase.js
backend/
  api/            # Endpoints PHP
  database/       # Migrations SQL
```

## Convenções
- Componentes Vue em PascalCase
- Stores em camelCase (authStore, chatStore)
- Estilos em styles.css global
- IDs de páginas: home, sobre, servicos, contactos, noticias, galeria, Frota, FAQ

## Notas Importantes
- Usar Supabase para autenticação
- Internacionalização PT/EN
- Design responsivo mobile-first
- Cores: azul escuro (#1a365d), dourado (#d4af37)

## O que foi feito recentemente
- Criado sistema de memória: CONTEXT.md, AGENTS.md, opencode.json
- Configurado o OpenCode para lembrar do contexto entre sessões
