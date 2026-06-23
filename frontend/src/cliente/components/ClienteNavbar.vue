<template>
  <header class="cliente-navbar">
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
        <div v-if="showResults && filteredPages.length" class="search-dropdown">
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
      </div>
    </div>

    <div class="navbar-right">
      <NotificationBell />
      <div class="user-section" @click="toggleDropdown" ref="dropdownRef">
        <div class="user-avatar-sm">{{ userInitials }}</div>
        <span class="user-name-sm">{{ authStore.user?.name || t('cliente.sidebar_title') }}</span>
        <i class="bi bi-chevron-down chevron"></i>
        <Transition name="dropdown">
          <div v-if="showDropdown" class="user-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-avatar">{{ userInitials }}</div>
              <div>
                <div class="dropdown-name">{{ authStore.user?.name }}</div>
                <div class="dropdown-role">{{ t('cliente.sidebar_title') }}</div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <router-link to="/perfil" class="dropdown-item" @click="showDropdown = false">
              <i class="bi bi-person"></i> {{ t('cliente.sidebar_profile') }}
            </router-link>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item danger" @click="handleLogout">
              <i class="bi bi-box-arrow-right"></i> {{ t('cliente.sidebar_logout') }}
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
import NotificationBell from '@/components/NotificationBell.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

defineProps({ collapsed: Boolean })
defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const searchQuery = ref('')
const searchFocused = ref(false)
const showDropdown = ref(false)
const showResults = ref(false)
const dropdownRef = ref(null)

const userInitials = computed(() => {
  const name = authStore.user?.name || 'C'
  return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
})

const pageTitle = computed(() => {
  const map = {
    'ClienteDashboard': 'Dashboard',
    'ClienteEmbarques': 'Embarques',
    'ClienteCotacoes': 'Cotações',
    'ClienteDocumentos': 'Documentos',
    'ClienteMessages': 'Mensagens',
    'ClienteContacts': 'Contactos',
    'ClienteProfile': 'Perfil',
  }
  return map[route.name] || 'Cliente'
})

const searchPages = [
  { label: 'Dashboard', route: '/dashboard', icon: 'bi bi-grid-1x2-fill', keywords: ['dashboard', 'inicio'] },
  { label: 'Embarques', route: '/embarques', icon: 'bi bi-box-seam-fill', keywords: ['embarques', 'shipments'] },
  { label: 'Cotações', route: '/cotacoes', icon: 'bi bi-receipt-cutoff', keywords: ['cotacoes', 'quotes'] },
  { label: 'Documentos', route: '/documentos', icon: 'bi bi-file-earmark-text-fill', keywords: ['documentos', 'docs'] },
  { label: 'Mensagens', route: '/mensagens', icon: 'bi bi-chat-dots-fill', keywords: ['mensagens', 'messages', 'chat'] },
  { label: 'Contactos', route: '/contactos', icon: 'bi bi-person-lines-fill', keywords: ['contactos', 'contacts'] },
  { label: 'Perfil', route: '/perfil', icon: 'bi bi-person', keywords: ['perfil', 'profile'] },
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
.cliente-navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 60px;
  padding: 0 16px;
  background: #ffffff;
  border-bottom: 1px solid #e4e6eb;
  gap: 12px;
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

.navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
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
