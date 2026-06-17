<template>
  <nav class="funcionario-navbar">
    <div class="navbar-content">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" @click="$emit('toggle-sidebar')">
          <i class="bi bi-list"></i>
        </button>
        <h2 class="page-title">{{ pageTitle }}</h2>
      </div>
      <div class="navbar-center d-none d-md-block">
        <div class="search-bar">
          <i class="bi bi-search search-icon"></i>
          <input
            type="text"
            class="search-input"
            placeholder="Pesquisar..."
            v-model="searchQuery"
            @keyup.enter="handleSearch"
          />
        </div>
      </div>
      <div class="navbar-actions">
        <NotificationBell />

        <button class="nav-action-btn" @click="toggleLocale" :title="locale === 'pt' ? 'English' : 'Português'">
          <span class="lang-flag">{{ locale === 'pt' ? '🇦🇴' : '🇬🇧' }}</span>
        </button>

        <button class="nav-action-btn" @click="themeStore.cycleTheme()" :title="themeStore.themeLabel()">
          <i :class="['bi', themeStore.themeIcon()]"></i>
        </button>

        <div class="user-dropdown d-flex align-items-center gap-2">
          <div class="user-avatar">
            <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user.name">
            <span v-else>{{ initials(authStore.user?.name) }}</span>
          </div>
          <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
            <span class="d-none d-md-inline">{{ authStore.user?.name || 'Funcionário' }}</span>
            <span class="d-md-none">Menu</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><router-link to="/funcionario/perfil" class="dropdown-item">
              <i class="bi bi-person-fill me-1"></i> {{ t('sidebar.profile') }}
            </router-link></li>
            <li><router-link to="/mudar-senha" class="dropdown-item">
              <i class="bi bi-shield-lock me-1"></i> {{ t('sidebar.change_password') }}
            </router-link></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="#" class="dropdown-item" @click.prevent="logout">
              <i class="bi bi-box-arrow-right me-1"></i> {{ t('sidebar.logout') }}
            </a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { useI18n } from '@/composables/useI18n.js'
import NotificationBell from '@/components/NotificationBell.vue'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const { t, locale, toggleLocale } = useI18n()

const searchQuery = ref('')

const searchPages = [
  { keyword: 'embarque', route: '/funcionario/embarques', label: 'Embarques' },
  { keyword: 'cotação', route: '/funcionario/cotacoes', label: 'Cotações' },
  { keyword: 'cotacao', route: '/funcionario/cotacoes', label: 'Cotações' },
  { keyword: 'documento', route: '/funcionario/documentos', label: 'Documentos' },
  { keyword: 'contacto', route: '/funcionario/contactos', label: 'Contactos' },
  { keyword: 'cliente', route: '/funcionario/clientes', label: 'Clientes' },
  { keyword: 'mensagem', route: '/funcionario/mensagens', label: 'Mensagens' },
  { keyword: 'chat', route: '/funcionario/mensagens', label: 'Mensagens' },
  { keyword: 'perfil', route: '/funcionario/perfil', label: 'Perfil' },
]

const handleSearch = () => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return
  const match = searchPages.find(p => q.includes(p.keyword))
  if (match) {
    router.push(match.route)
    searchQuery.value = ''
  }
}

const pageTitle = computed(() => {
  const titles = {
    'FuncionarioDashboard': t('sidebar.dashboard'),
    'FuncionarioMessages': t('sidebar.messages'),
    'FuncionarioProfile': t('sidebar.profile'),
    'FuncionarioEmbarques': t('dashboard.shipments'),
    'FuncionarioCotacoes': t('dashboard.quotes'),
    'FuncionarioDocumentos': t('dashboard.documents'),
    'FuncionarioContactos': t('dashboard.contacts'),
    'FuncionarioClientes': t('sidebar.clients_section'),
  }
  return titles[route.name] || 'Funcionário'
})

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
</script>

<style scoped>
.funcionario-navbar {
  background: var(--navbar-bg);
  border-bottom: 1px solid var(--navbar-border);
  padding: 1rem 1.5rem;
  position: sticky;
  top: 0;
  z-index: 100;
  margin-left: 250px;
  transition: background 0.3s ease, border-color 0.3s ease;
}
.navbar-content { display: flex; justify-content: space-between; align-items: center; }
.page-title { font-size: 1.4rem; margin: 0; color: var(--navbar-text); font-weight: 700; }
.navbar-center { flex: 1; max-width: 400px; margin: 0 1.5rem; }
.search-bar { position: relative; }
.search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; }
.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  background: var(--navbar-bg);
  color: var(--navbar-text);
  transition: border-color 0.2s;
}
.search-input:focus { border-color: #1877f2; }
.navbar-actions { display: flex; gap: 0.5rem; align-items: center; }

.nav-action-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid var(--card-border);
  background: var(--card-bg);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1.1rem;
}
.nav-action-btn:hover {
  background: var(--accent-light);
  border-color: var(--accent);
  color: var(--accent);
  transform: translateY(-1px);
}

.lang-flag {
  font-size: 1.2rem;
  line-height: 1;
}

.user-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: var(--sidebar-avatar-bg);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 700;
  overflow: hidden;
  flex-shrink: 0;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }

@media (max-width: 768px) {
  .funcionario-navbar { margin-left: 0; padding: 0.75rem 1rem; }
}
</style>
