<template>
  <header class="funcionario-navbar">
    <div class="navbar-left">
      <button class="hamburger-btn" @click="$emit('toggle-sidebar')">
        <i class="bi bi-list"></i>
      </button>
      <h1 class="page-title">{{ pageTitle }}</h1>
    </div>

    <div class="navbar-center">
      <div class="search-box" :class="{ focused: searchFocused }">
        <i class="bi bi-search search-icon"></i>
        <input
          type="text"
          class="search-input"
          placeholder="Pesquisar..."
          v-model="searchQuery"
          @focus="searchFocused = true"
          @blur="handleSearchBlur"
          @input="handleSearch"
        >
        <div v-if="showResults && (filteredPages.length || dataResults.length)" class="search-dropdown">
          <div v-if="filteredPages.length" class="search-section">
            <div class="search-section-title">Páginas</div>
            <router-link
              v-for="page in filteredPages"
              :key="page.route"
              :to="page.route"
              class="search-result"
              @click="clearSearch"
            >
              <i :class="page.icon" class="result-icon"></i>
              <span>{{ page.label }}</span>
            </router-link>
          </div>
          <div v-if="dataResults.length" class="search-section">
            <div class="search-section-title">Dados</div>
            <router-link
              v-for="item in dataResults"
              :key="item.id"
              :to="item.link"
              class="search-result"
              @click="clearSearch"
            >
              <i :class="item.icon" class="result-icon"></i>
              <span class="result-label">{{ item.label }}</span>
              <span class="result-meta">{{ item.meta }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="navbar-right">
      <button class="nav-icon-btn" @click="cycleLocale" :title="locale === 'pt' ? 'English' : locale === 'en' ? 'Français' : 'Português'">
        <span class="lang-flag">{{ locale === 'pt' ? '🇦🇴' : locale === 'en' ? '🇬🇧' : '🇫🇷' }}</span>
      </button>
      <button class="nav-icon-btn" @click="themeStore.cycleTheme()" :title="themeStore.themeLabel()">
        <i :class="['bi', themeStore.themeIcon()]"></i>
      </button>
      <NotificationBell />
      <div class="user-section" @click="toggleDropdown" ref="dropdownRef">
        <div class="user-avatar-sm">{{ userInitials }}</div>
        <span class="user-name-sm">{{ authStore.user?.name || 'Funcionário' }}</span>
        <i class="bi bi-chevron-down chevron"></i>
        <Transition name="dropdown">
          <div v-if="showDropdown" class="user-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-avatar">{{ userInitials }}</div>
              <div>
                <div class="dropdown-name">{{ authStore.user?.name }}</div>
                <div class="dropdown-role">{{ authStore.user?.position || 'Funcionário' }}</div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <router-link to="/funcionario/perfil" class="dropdown-item" @click="showDropdown = false">
              <i class="bi bi-person"></i> Perfil
            </router-link>
            <router-link to="/mudar-senha" class="dropdown-item" @click="showDropdown = false">
              <i class="bi bi-shield-lock"></i> Alterar Senha
            </router-link>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item danger" @click="handleLogout">
              <i class="bi bi-box-arrow-right"></i> Sair
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n.js'
import NotificationBell from '@/components/NotificationBell.vue'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const { t, locale, cycleLocale } = useI18n()

const searchQuery = ref('')
const searchFocused = ref(false)
const showDropdown = ref(false)
const showResults = ref(false)
const dropdownRef = ref(null)
const dataResults = ref([])
let searchTimeout = null

const userInitials = computed(() => {
  const name = authStore.user?.name || 'F'
  return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
})

const pageTitle = computed(() => {
  const map = {
    'FuncionarioDashboard': 'Dashboard',
    'FuncionarioMessages': 'Mensagens',
    'FuncionarioProfile': 'Perfil',
    'FuncionarioEmbarques': 'Embarques',
    'FuncionarioCotacoes': 'Cotações',
    'FuncionarioDocumentos': 'Documentos',
    'FuncionarioContactos': 'Contactos',
    'FuncionarioClientes': 'Clientes',
  }
  return map[route.name] || 'Funcionário'
})

const searchPages = [
  { label: 'Dashboard', route: '/funcionario', icon: 'bi bi-grid-1x2-fill', keywords: ['dashboard', 'inicio'] },
  { label: 'Mensagens', route: '/funcionario/mensagens', icon: 'bi bi-chat-dots-fill', keywords: ['mensagens', 'chat'] },
  { label: 'Clientes', route: '/funcionario/clientes', icon: 'bi bi-people-fill', keywords: ['clientes'] },
  { label: 'Embarques', route: '/funcionario/embarques', icon: 'bi bi-box-seam-fill', keywords: ['embarques', 'shipments'] },
  { label: 'Cotações', route: '/funcionario/cotacoes', icon: 'bi bi-receipt-cutoff', keywords: ['cotacoes', 'quotes'] },
  { label: 'Documentos', route: '/funcionario/documentos', icon: 'bi bi-file-earmark-text-fill', keywords: ['documentos', 'docs'] },
  { label: 'Contactos', route: '/funcionario/contactos', icon: 'bi bi-person-lines-fill', keywords: ['contactos'] },
  { label: 'Perfil', route: '/funcionario/perfil', icon: 'bi bi-person', keywords: ['perfil'] },
]

const filteredPages = computed(() => {
  if (!searchQuery.value) return []
  const q = searchQuery.value.toLowerCase()
  return searchPages.filter(p =>
    p.label.toLowerCase().includes(q) ||
    p.keywords.some(k => k.includes(q))
  ).slice(0, 8)
})

const handleSearch = () => {
  showResults.value = searchQuery.value.length > 0
  clearTimeout(searchTimeout)
  if (searchQuery.value.length < 2) { dataResults.value = []; return }
  searchTimeout = setTimeout(async () => {
    const q = searchQuery.value.trim()
    const results = []
    try {
      const { data: users } = await supabase.from('users').select('id, name, email, role').or(`name.ilike.%${q}%,email.ilike.%${q}%`).limit(5)
      ;(users || []).forEach(u => {
        const roleLabel = u.role === 'cliente' ? 'Cliente' : u.role === 'funcionario' ? 'Funcionário' : 'Admin'
        results.push({ id: 'u-' + u.id, label: u.name, meta: roleLabel, icon: 'bi bi-person-fill', link: '/funcionario/clientes' })
      })
      const { data: lic } = await supabase.from('licenciamentos').select('id, referencia, empresa, shipper, estado').or(`referencia.ilike.%${q}%,empresa.ilike.%${q}%,shipper.ilike.%${q}%`).limit(5)
      ;(lic || []).forEach(l => {
        results.push({ id: 'l-' + l.id, label: l.referencia || 'Sem referência', meta: (l.empresa || '') + (l.shipper ? ' · ' + l.shipper : ''), icon: 'bi bi-file-earmark-check', link: `/funcionario/licenciamentos` })
      })
      const { data: emb } = await supabase.from('embarques').select('id, tracking_number, client_name, company_name').or(`tracking_number.ilike.%${q}%,client_name.ilike.%${q}%`).limit(5)
      ;(emb || []).forEach(e => {
        results.push({ id: 'e-' + e.id, label: e.tracking_number || 'Sem tracking', meta: e.client_name || '', icon: 'bi bi-box-seam', link: '/funcionario/embarques' })
      })
    } catch (e) { console.error('Search error:', e) }
    dataResults.value = results.slice(0, 8)
  }, 300)
}

const handleSearchBlur = () => {
  setTimeout(() => {
    searchFocused.value = false
    showResults.value = false
  }, 200)
}

const clearSearch = () => {
  searchQuery.value = ''
  showResults.value = false
  dataResults.value = []
}

const toggleDropdown = (e) => {
  e.stopPropagation()
  showDropdown.value = !showDropdown.value
}

const handleLogout = () => {
  showDropdown.value = false
  authStore.logout()
  router.push('/login')
}

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.funcionario-navbar {
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 60px;
  padding: 0 16px;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid #e4e6eb;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.06);
  gap: 12px;
  transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 1;
  min-width: 0;
  overflow: hidden;
}

.hamburger-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: #f0f2f5;
  color: #050505;
  font-size: 1.4rem;
  cursor: pointer;
  transition: background 0.2s;
}

.hamburger-btn:hover {
  background: #e4e6eb;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #050505;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.navbar-center {
  flex: 1;
  max-width: 480px;
  margin: 0 auto;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  background: #f0f2f5;
  border-radius: 50px;
  padding: 0 14px;
  height: 40px;
  transition: all 0.2s;
}

.search-box.focused {
  background: #fff;
  box-shadow: 0 0 0 2px #1877f2;
}

.search-icon {
  color: #65676b;
  font-size: 1rem;
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  padding: 0 10px;
  font-size: 0.938rem;
  color: #050505;
}

.search-input::placeholder {
  color: #65676b;
}

.search-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  padding: 6px;
  z-index: 200;
  max-height: 400px;
  overflow-y: auto;
}

.search-section {
  margin-bottom: 4px;
}

.search-section-title {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #65676b;
  padding: 4px 12px 2px;
  letter-spacing: 0.5px;
}

.search-result {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 6px;
  color: #050505;
  text-decoration: none;
  font-size: 0.938rem;
  transition: background 0.15s;
}

.search-result:hover {
  background: #f0f2f5;
  color: #050505;
  text-decoration: none;
}

.result-icon {
  color: #1877f2;
  font-size: 1.1rem;
}

.result-label {
  flex: 1;
}

.result-meta {
  font-size: 0.8rem;
  color: #65676b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.nav-icon-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: #f0f2f5;
  color: #050505;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 1.1rem;
}

.nav-icon-btn:hover {
  background: #e4e6eb;
}

.lang-flag {
  font-size: 1.1rem;
  line-height: 1;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px 4px 4px;
  border-radius: 50px;
  cursor: pointer;
  transition: background 0.2s;
  position: relative;
}

.user-section:hover {
  background: #f0f2f5;
}

.user-avatar-sm {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0a5dc2);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 700;
}

.user-name-sm {
  font-size: 0.875rem;
  font-weight: 600;
  color: #050505;
}

.chevron {
  font-size: 0.7rem;
  color: #65676b;
  transition: transform 0.2s;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 280px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
  padding: 8px;
  z-index: 300;
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 8px;
}

.dropdown-header:hover {
  background: #f0f2f5;
}

.dropdown-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0a5dc2);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
}

.dropdown-name {
  font-size: 0.938rem;
  font-weight: 600;
  color: #050505;
}

.dropdown-role {
  font-size: 0.8rem;
  color: #65676b;
}

.dropdown-divider {
  height: 1px;
  background: #e4e6eb;
  margin: 4px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 6px;
  color: #050505;
  text-decoration: none;
  font-size: 0.938rem;
  font-weight: 500;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
  transition: background 0.15s;
}

.dropdown-item:hover {
  background: #f0f2f5;
  color: #050505;
  text-decoration: none;
}

.dropdown-item.danger {
  color: #dc3545;
}

.dropdown-item.danger:hover {
  background: #fee2e2;
  color: #dc3545;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}

@media (max-width: 767px) {
  .funcionario-navbar {
    left: 0;
  }

  .hamburger-btn {
    display: flex;
  }

  .page-title {
    font-size: 1rem;
  }

  .navbar-center {
    display: none;
  }

  .user-name-sm {
    display: none;
  }

  .chevron {
    display: none;
  }

  .user-dropdown {
    right: -8px;
    width: calc(100vw - 16px);
    max-width: 300px;
  }
}

@media (min-width: 768px) and (max-width: 1023px) {
  .funcionario-navbar {
    left: 72px;
  }

  .page-title {
    font-size: 1rem;
  }

  .navbar-center {
    max-width: 280px;
  }

  .user-name-sm {
    display: none;
  }

  .chevron {
    display: none;
  }
}
</style>
