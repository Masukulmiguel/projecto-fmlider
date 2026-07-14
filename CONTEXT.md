# 📋 Contexto do Projecto - FMLider

## 🎯 O que é este projecto

Website corporativo premium para a **FMLider Transitário & Logística** (Angola).

| Campo | Valor |
|-------|-------|
| 👨‍💻 **Desenvolvedor** | Masukulu Miguel |
| 🏠 **Organização** | [CodingLifeDev](https://github.com/codinglifedev) |
| 📦 **Projecto Pai** | seefast-project |
| 📅 **Início** | Abril 2025 |
| 🏷️ **Versão Actual** | `2.2.1` |
| 🌍 **Idioma** | Português de Portugal (PT-PT) |

## 🛠️ Stack Tecnológica

- 💻 **Frontend**: Vue.js 3 + Vite + Bootstrap 5
- 🐘 **Backend**: PHP puro (Laravel-like API REST)
- 🗃️ **Base de Dados**: MySQL 8
- 🔐 **Auth**: Supabase
- 🚀 **Deploy**: Vercel (frontend), XAMPP (local)

## 📊 Estado Actual

- ✅ Site em produção (Vercel)
- 💻 Frontend em `frontend/` com Vue 3
- 🗄️ Backend em `backend/` com API PHP
- 👤 Painel de cliente em desenvolvimento
- 🤖 Chatbot com IA integrado
- 📍 Rastreamento de contentores activo

## 📂 Estrutura Principal

```
frontend/
  src/
    📄 pages/        # Páginas públicas (Home, Services, Contact, etc.)
    📄 pages/cliente/ # Painel do cliente (Dashboard, Embarques, Cotações, etc.)
    🧩 components/   # Componentes reutilizáveis
    🗃️ stores/       # Pinia stores (auth, chat, company, etc.)
    🌐 locales/      # pt.js, en.js, fr.js (internacionalização)
    📂 composable/   # Composables Vue
    🔗 lib/          # supabase.js
backend/
  📡 api/            # Endpoints PHP
  🗃️ database/       # Migrations SQL
```

## 📏 Convenções

- 🧩 Componentes Vue em PascalCase
- 🗃️ Stores em camelCase (authStore, chatStore)
- 🎨 Estilos em styles.css global
- 📄 IDs de páginas: home, sobre, servicos, contactos, noticias, galeria, Frota, FAQ

## 📌 Notas Importantes

- 🔐 Usar Supabase para autenticação
- 🌐 Internacionalização PT/EN/FR
- 📱 Design responsivo mobile-first
- 🎨 Cores: azul escuro (#1a365d), dourado (#d4af37)

## 📝 Histórico de Alterações

- 🚀 Criado sistema de memória: CONTEXT.md, AGENTS.md, opencode.json
- ⚙️ Configurado o OpenCode para lembrar do contexto entre sessões
- 🔧 Implementado chatbot com Groq API
- 📍 Implementado rastreamento de contentores
- 🔍 Implementado lookup de BI/NIF
- 🛡️ Implementadas medidas de segurança avançadas
- 📱 Melhorado design responsivo
- 🎨 Redesenhadas páginas de administração

---

**🏷️ Versão Actual**: `2.2.1`  
**📅 Última Actualização**: Julho 2025  
**🌍 Idioma**: Português de Portugal (PT-PT)  
**👨‍💻 Desenvolvido por**: Masukulu Miguel (CodingLifeDev) para o projecto **seefast-project**
