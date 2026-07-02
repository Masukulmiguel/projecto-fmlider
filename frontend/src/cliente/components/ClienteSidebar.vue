<template>
  <div class="sidebar-wrapper">
    <div v-if="isOpen" class="sidebar-overlay" @click="$emit('close')"></div>
    <aside class="cliente-sidebar" :class="{ show: isOpen, collapsed: collapsed }">
      <div class="sidebar-inner">
        <div class="sidebar-logo">
          <img :src="logoUrl" alt="FMLider" class="logo-img" />
          <span class="logo-text" v-show="!collapsed">{{ t('cliente.sidebar_title') }}</span>
          <button class="collapse-btn" @click="$emit('toggle-collapse')" :title="collapsed ? t('cliente.sidebar_title') : t('cliente.sidebar_title')">
            <i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
          </button>
        </div>

        <nav class="sidebar-menu" @click="handleNavClick">
          <div class="menu-section">
            <span class="section-label" v-show="!collapsed">{{ t('cliente.sidebar_general') }}</span>
            <router-link to="/dashboard" class="menu-item" :class="{ active: $route.path === '/dashboard' }" :title="collapsed ? t('cliente.sidebar_dashboard') : ''">
              <i class="bi bi-grid-1x2-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_dashboard') }}</span>
            </router-link>
          </div>

          <div class="menu-section">
            <span class="section-label" v-show="!collapsed">{{ t('cliente.sidebar_operations') }}</span>
            <router-link to="/embarques" class="menu-item" :class="{ active: $route.path === '/embarques' }" :title="collapsed ? t('cliente.sidebar_shipments') : ''">
              <i class="bi bi-box-seam-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_shipments') }}</span>
            </router-link>
            <router-link to="/cotacoes" class="menu-item" :class="{ active: $route.path === '/cotacoes' }" :title="collapsed ? t('cliente.sidebar_quotes') : ''">
              <i class="bi bi-receipt-cutoff menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_quotes') }}</span>
            </router-link>
            <router-link to="/documentos" class="menu-item" :class="{ active: $route.path === '/documentos' }" :title="collapsed ? t('cliente.sidebar_documents') : ''">
              <i class="bi bi-file-earmark-text-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_documents') }}</span>
            </router-link>
            <router-link to="/licenciamentos" class="menu-item" :class="{ active: $route.path.startsWith('/licenciamentos') }" :title="collapsed ? t('cliente.sidebar_licenciamentos') : ''">
              <i class="bi bi-file-earmark-medical menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_licenciamentos') }}</span>
            </router-link>
            <router-link to="/mensagens" class="menu-item" :class="{ active: $route.path === '/mensagens' }" :title="collapsed ? t('cliente.sidebar_messages') : ''">
              <i class="bi bi-chat-dots-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_messages') }}</span>
              <span v-if="chatUnread > 0" class="menu-badge badge-yellow">{{ chatUnread }}</span>
            </router-link>
          </div>

          <div class="menu-section">
            <span class="section-label" v-show="!collapsed">{{ t('cliente.sidebar_contacts') }}</span>
            <router-link to="/contactos" class="menu-item" :class="{ active: $route.path === '/contactos' }" :title="collapsed ? t('cliente.sidebar_contacts') : ''">
              <i class="bi bi-person-lines-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_contacts') }}</span>
            </router-link>
            <router-link to="/perfil" class="menu-item" :class="{ active: $route.path === '/perfil' }" :title="collapsed ? t('cliente.sidebar_profile') : ''">
              <i class="bi bi-person-fill menu-icon"></i>
              <span class="menu-text" v-show="!collapsed">{{ t('cliente.sidebar_profile') }}</span>
            </router-link>
          </div>
        </nav>

        <div class="sidebar-user" v-show="!collapsed">
          <div class="user-avatar">{{ userInitials }}</div>
          <div class="user-info">
            <span class="user-name">{{ authStore.user?.name || t('cliente.sidebar_title') }}</span>
            <span class="user-role">{{ t('cliente.sidebar_title') }}</span>
          </div>
          <button class="logout-btn" @click="handleLogout" :title="t('cliente.sidebar_logout')">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>

        <div class="sidebar-user collapsed-user" v-show="collapsed">
          <button class="logout-btn-icon" @click="handleLogout" :title="t('cliente.sidebar_logout')">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useChatStore } from '@/stores/chatStore'
import { useSiteImages } from '@/composables/useSiteImages'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

const props = defineProps({
  isOpen: Boolean,
  collapsed: Boolean
})

const emit = defineEmits(['close', 'toggle-collapse'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const chatStore = useChatStore()
const { getImage } = useSiteImages()

const chatUnread = ref(0)
const logoUrl = computed(() => getImage('header', 'logo', '/assets/img/logo.png'))

const userInitials = computed(() => {
  const name = authStore.user?.name || 'C'
  return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
})

const handleNavClick = () => {
  if (window.innerWidth < 768) {
    emit('close')
  }
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

let pollInterval = null

onMounted(async () => {
  await fetchChatUnread()
  pollInterval = setInterval(async () => {
    await fetchChatUnread()
  }, 30000)
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
})

const fetchChatUnread = async () => {
  try {
    chatUnread.value = chatStore.unreadCount || 0
  } catch (e) {}
}
</script>

<style scoped>
.sidebar-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  height: 100vh;
}

.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: transparent;
  z-index: 999;
}

.cliente-sidebar {
  width: 260px;
  height: 100vh;
  background: #ffffff;
  border-right: 1px solid #e4e6eb;
  display: flex;
  flex-direction: column;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  position: relative;
  z-index: 1000;
}

.cliente-sidebar.collapsed {
  width: 72px;
}

.sidebar-inner {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  scrollbar-color: #ccd0d5 transparent;
}

.sidebar-inner::-webkit-scrollbar {
  width: 4px;
}

.sidebar-inner::-webkit-scrollbar-thumb {
  background: #ccd0d5;
  border-radius: 4px;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 12px;
  border-bottom: 1px solid #e4e6eb;
  flex-shrink: 0;
  min-height: 60px;
}

.logo-img {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: contain;
  flex-shrink: 0;
}

.logo-text {
  font-size: 1.1rem;
  font-weight: 700;
  color: #050505;
  white-space: nowrap;
}

.collapse-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 50%;
  background: #f0f2f5;
  color: #65676b;
  cursor: pointer;
  transition: all 0.2s;
  margin-left: auto;
  flex-shrink: 0;
  font-size: 0.75rem;
}

.collapse-btn:hover {
  background: #e4e6eb;
  color: #050505;
}

@media (min-width: 1024px) {
  .collapse-btn {
    display: flex;
  }
}

.sidebar-menu {
  flex: 1;
  padding: 8px 8px;
  overflow-y: auto;
}

.menu-section {
  margin-bottom: 8px;
}

.section-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 700;
  color: #65676b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 12px 12px 4px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  color: #050505;
  text-decoration: none;
  font-size: 0.938rem;
  font-weight: 500;
  transition: background 0.2s;
  position: relative;
  min-height: 40px;
}

.menu-item:hover {
  background: #f0f2f5;
  text-decoration: none;
  color: #050505;
}

.menu-item.active {
  background: #e7f3ff;
  color: #1877f2;
}

.menu-icon {
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
  color: #65676b;
}

.menu-item.active .menu-icon {
  color: #1877f2;
}

.menu-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.menu-badge {
  margin-left: auto;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
}

.badge-yellow {
  background: #f7b928;
  color: #fff;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  border-top: 1px solid #e4e6eb;
  flex-shrink: 0;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0a5dc2);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
}

.user-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #050505;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.7rem;
  color: #65676b;
}

.logout-btn {
  background: none;
  border: none;
  color: #65676b;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 6px;
  border-radius: 50%;
  transition: all 0.2s;
  flex-shrink: 0;
}

.logout-btn:hover {
  background: #fee2e2;
  color: #dc3545;
}

.collapsed-user {
  justify-content: center;
  padding: 12px 8px;
}

.logout-btn-icon {
  background: none;
  border: none;
  color: #65676b;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.logout-btn-icon:hover {
  background: #fee2e2;
  color: #dc3545;
}

/* Collapsed state */
.collapsed .sidebar-logo {
  justify-content: center;
  padding: 16px 8px;
}

.collapsed .logo-text {
  display: none;
}

.collapsed .menu-item {
  justify-content: center;
  padding: 10px;
  gap: 0;
}

.collapsed .menu-item .menu-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  padding: 1px 5px;
  font-size: 0.6rem;
}

.collapsed .section-label {
  display: none;
}

.collapsed .sidebar-user {
  justify-content: center;
  padding: 12px 8px;
}

.collapsed .user-info,
.collapsed .logout-btn {
  display: none;
}

/* Mobile */
@media (max-width: 767px) {
  .cliente-sidebar {
    position: fixed;
    left: -300px;
    width: 280px;
    max-width: 85vw;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: none;
  }

  .cliente-sidebar.show {
    left: 0;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
  }

  .sidebar-overlay {
    display: block;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
  }

  .cliente-sidebar.show ~ .sidebar-overlay,
  .sidebar-overlay.active {
    opacity: 1;
    pointer-events: auto;
  }

  .cliente-sidebar.collapsed {
    width: 280px;
    max-width: 85vw;
  }

  .collapse-btn {
    display: none;
  }

  .collapsed .menu-item {
    justify-content: flex-start;
    padding: 8px 12px;
    gap: 12px;
  }

  .collapsed .section-label {
    display: block;
  }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1023px) {
  .cliente-sidebar {
    width: 72px;
  }

  .cliente-sidebar .sidebar-logo {
    justify-content: center;
    padding: 16px 8px;
  }

  .cliente-sidebar .logo-text {
    display: none;
  }

  .cliente-sidebar .menu-item {
    justify-content: center;
    padding: 10px;
    gap: 0;
  }

  .cliente-sidebar .menu-item .menu-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    padding: 1px 5px;
    font-size: 0.6rem;
  }

  .cliente-sidebar .section-label {
    display: none;
  }

  .cliente-sidebar .sidebar-user {
    justify-content: center;
    padding: 12px 8px;
  }

  .cliente-sidebar .user-info,
  .cliente-sidebar .logout-btn {
    display: none;
  }
}
</style>
