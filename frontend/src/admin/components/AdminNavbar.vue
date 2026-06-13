<template>
  <nav class="admin-navbar">
    <div class="navbar-content">
      <div class="navbar-left">
        <button class="hamburger-btn d-lg-none" @click="$emit('toggle-sidebar')">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="navbar-title">{{ pageTitle }}</h1>
      </div>

      <div class="navbar-center">
        <div class="search-bar">
          <i class="bi bi-search search-icon"></i>
          <input
            type="text"
            class="search-input"
            placeholder="Pesquisar..."
            v-model="searchQuery"
          />
        </div>
      </div>

      <div class="navbar-right">
        <NotificationBell />

        <div class="user-dropdown" ref="dropdownRef">
          <button class="user-dropdown-toggle" @click="toggleDropdown">
            <div class="user-avatar">
              <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user?.name">
              <span v-else>{{ initials(authStore.user?.name) }}</span>
            </div>
            <div class="user-info">
              <span class="user-name">{{ authStore.user?.name || 'Admin' }}</span>
              <span class="user-role">{{ authStore.user?.position || 'Administrador' }}</span>
            </div>
            <i class="bi bi-chevron-down dropdown-arrow" :class="{ open: showDropdown }"></i>
          </button>
          <transition name="dropdown">
            <div v-if="showDropdown" class="dropdown-menu-custom">
              <router-link to="/admin/perfil" class="dropdown-item-custom" @click="showDropdown = false">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </router-link>
              <router-link to="/admin/configuracoes" class="dropdown-item-custom" @click="showDropdown = false">
                <i class="bi bi-gear"></i>
                <span>Definições</span>
              </router-link>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item-custom dropdown-item-danger" @click="logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import NotificationBell from '@/components/NotificationBell.vue'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const searchQuery = ref('')
const showDropdown = ref(false)
const dropdownRef = ref(null)

const pageTitle = computed(() => {
  const titles = {
    'AdminDashboard': 'Dashboard',
    'AdminUsers': 'Utilizadores',
    'AdminServices': 'Serviços',
    'AdminNews': 'Notícias',
    'AdminGallery': 'Galeria',
    'AdminPartners': 'Parceiros',
    'AdminContacts': 'Contactos',
    'AdminTestimonials': 'Testemunhos',
    'AdminFAQs': 'FAQs',
    'AdminBanners': 'Banners',
    'AdminProfile': 'Perfil',
    'AdminEmbarques': 'Embarques',
    'AdminDocumentos': 'Documentos',
    'AdminCotacoes': 'Cotações',
    'AdminContactosCliente': 'Contactos dos Clientes',
    'AdminMessages': 'Mensagens',
    'AdminVisitors': 'Visitantes',
    'AdminFuncionarios': 'Funcionários'
  }
  return titles[route.name] || 'Admin'
})

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const logout = () => {
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
.admin-navbar {
  background: #ffffff;
  border-bottom: 1px solid #dddfe2;
  position: sticky;
  top: 0;
  z-index: 100;
  height: 64px;
}

.navbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 100%;
  gap: 16px;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.hamburger-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #f0f2f5;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #65676b;
  font-size: 1.25rem;
  transition: all 0.2s ease;
}

.hamburger-btn:hover {
  background: #e4e6e9;
  color: #1c1e21;
}

.navbar-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1c1e21;
  margin: 0;
  white-space: nowrap;
}

.navbar-center {
  flex: 1;
  max-width: 480px;
  margin: 0 16px;
}

.search-bar {
  position: relative;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #65676b;
  font-size: 0.9rem;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 16px 10px 42px;
  border: none;
  border-radius: 20px;
  background: #f0f2f5;
  font-size: 0.9rem;
  color: #1c1e21;
  transition: all 0.2s ease;
  outline: none;
}

.search-input::placeholder {
  color: #65676b;
}

.search-input:focus {
  background: #ffffff;
  box-shadow: 0 0 0 2px #1877f2;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.user-dropdown {
  position: relative;
}

.user-dropdown-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px 6px 6px;
  border: none;
  background: transparent;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-dropdown-toggle:hover {
  background: #f0f2f5;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0d5bbd);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1c1e21;
  line-height: 1.2;
}

.user-role {
  font-size: 0.7rem;
  color: #65676b;
  line-height: 1.2;
}

.dropdown-arrow {
  font-size: 0.75rem;
  color: #65676b;
  transition: transform 0.2s ease;
}

.dropdown-arrow.open {
  transform: rotate(180deg);
}

.dropdown-menu-custom {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 220px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
  z-index: 1000;
  border: 1px solid #e4e6e9;
}

.dropdown-item-custom {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  color: #1c1e21;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background 0.2s ease;
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
}

.dropdown-item-custom:hover {
  background: #f0f2f5;
}

.dropdown-item-custom i {
  font-size: 1.1rem;
  color: #65676b;
  width: 20px;
  text-align: center;
}

.dropdown-item-danger {
  color: #dc3545;
}

.dropdown-item-danger i {
  color: #dc3545;
}

.dropdown-item-danger:hover {
  background: #fee2e2;
}

.dropdown-divider {
  height: 1px;
  background: #e4e6e9;
  margin: 4px 0;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 768px) {
  .navbar-center {
    display: none;
  }

  .user-info {
    display: none;
  }

  .dropdown-arrow {
    display: none;
  }

  .navbar-content {
    padding: 0 16px;
  }
}

@media (max-width: 992px) {
  .user-info {
    display: none;
  }

  .dropdown-arrow {
    display: none;
  }
}
</style>
