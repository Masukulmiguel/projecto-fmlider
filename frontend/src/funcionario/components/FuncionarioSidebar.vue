<template>
  <div class="funcionario-sidebar" :class="{ show: isOpen }">
    <div class="sidebar-overlay" @click="$emit('close')"></div>
    <div class="sidebar-logo">
      <img :src="logoUrl" alt="FMLider" height="42">
      <small class="d-block sidebar-subtitle">{{ t('sidebar.panel') }}</small>
      <div class="sidebar-user" v-if="authStore.user">
        <div class="sidebar-user-avatar">
          <img v-if="authStore.user.photo" :src="authStore.user.photo" :alt="authStore.user.name">
          <span v-else>{{ initials(authStore.user.name) }}</span>
        </div>
        <div class="sidebar-user-info">
          <strong>{{ authStore.user.name }}</strong>
          <small v-if="authStore.user.position">
            <i class="bi bi-person-badge"></i> {{ authStore.user.position }}
          </small>
        </div>
      </div>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-section">{{ t('sidebar.general') }}</div>
      <router-link to="/funcionario" class="menu-item" active-class="active">
        <i class="bi bi-grid-1x2-fill menu-icon"></i>
        <span class="menu-text">{{ t('sidebar.dashboard') }}</span>
      </router-link>
      <router-link to="/funcionario/mensagens" class="menu-item" active-class="active">
        <i class="bi bi-chat-dots-fill menu-icon"></i>
        <span class="menu-text">{{ t('sidebar.messages') }}</span>
        <span v-if="chatUnread > 0" class="menu-badge">{{ chatUnread }}</span>
      </router-link>

      <template v-if="can('clients.view')">
        <div class="menu-section">{{ t('sidebar.clients_section') }}</div>
        <router-link to="/funcionario/clientes" class="menu-item" active-class="active">
          <i class="bi bi-people-fill menu-icon"></i>
          <span class="menu-text">{{ t('sidebar.clients_section') }}</span>
        </router-link>
      </template>

      <template v-if="can('embarques.view') || can('cotacoes.view') || can('documentos.view') || can('contactos.view')">
        <div class="menu-section">{{ t('sidebar.operations') }}</div>
        <router-link v-if="can('embarques.view')" to="/funcionario/embarques" class="menu-item" active-class="active">
          <i class="bi bi-box-seam-fill menu-icon"></i>
          <span class="menu-text">{{ t('dashboard.shipments') }}</span>
        </router-link>
        <router-link v-if="can('cotacoes.view')" to="/funcionario/cotacoes" class="menu-item" active-class="active">
          <i class="bi bi-receipt menu-icon"></i>
          <span class="menu-text">{{ t('dashboard.quotes') }}</span>
        </router-link>
        <router-link v-if="can('documentos.view')" to="/funcionario/documentos" class="menu-item" active-class="active">
          <i class="bi bi-file-earmark-text-fill menu-icon"></i>
          <span class="menu-text">{{ t('dashboard.documents') }}</span>
        </router-link>
        <router-link v-if="can('contactos.view')" to="/funcionario/contactos" class="menu-item" active-class="active">
          <i class="bi bi-person-rolodex menu-icon"></i>
          <span class="menu-text">{{ t('dashboard.contacts') }}</span>
        </router-link>
      </template>

      <div class="menu-section">{{ t('sidebar.account') }}</div>
      <router-link to="/funcionario/perfil" class="menu-item" active-class="active">
        <i class="bi bi-person-fill menu-icon"></i>
        <span class="menu-text">{{ t('sidebar.profile') }}</span>
      </router-link>
      <router-link to="/mudar-senha" class="menu-item" active-class="active">
        <i class="bi bi-shield-lock menu-icon"></i>
        <span class="menu-text">{{ t('sidebar.change_password') }}</span>
      </router-link>
    </nav>
    <div class="sidebar-footer">
      <button class="logout-btn" @click="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>{{ t('sidebar.logout') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useSiteImages } from '@/composables/useSiteImages'

const { getImage, fetchAll } = useSiteImages()
const logoUrl = ref('/assets/img/logo.png')
import { useChatStore } from '@/stores/chatStore'
import { useI18n } from '@/composables/useI18n.js'

defineProps({ isOpen: { type: Boolean, default: false } })

defineEmits(['close'])

const authStore = useAuthStore()
const chatStore = useChatStore()
const router = useRouter()
const { t } = useI18n()
const chatUnread = ref(0)
let pollInterval = null

const can = (perm) => authStore.can(perm)
const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const fetchChatUnread = async () => {
  if (!authStore.token) return
  try {
    await chatStore.refreshUnread()
    chatUnread.value = chatStore.totalUnread
  } catch (e) { chatUnread.value = 0 }
}

const logout = () => {
  authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  await fetchAll()
  logoUrl.value = getImage('sidebar', 'funcionario_logo', '/assets/img/logo.png')
  fetchChatUnread()
  pollInterval = setInterval(fetchChatUnread, 30000)
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<style scoped>
.funcionario-sidebar {
  width: 260px;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  padding: 1rem 0;
  z-index: 1000;
  transition: transform 0.3s ease, background 0.3s ease;
  display: flex;
  flex-direction: column;
  border-right: 1px solid var(--sidebar-divider);
}

.sidebar-logo {
  padding: 1.75rem 1.5rem;
  text-align: center;
  border-bottom: 1px solid var(--sidebar-divider);
  margin-bottom: 1rem;
}
.sidebar-logo img { filter: brightness(0) invert(1); margin-bottom: 0.5rem; }
.sidebar-subtitle {
  color: var(--sidebar-text-muted);
  font-size: 0.72rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-weight: 500;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
  padding: 0.75rem;
  background: var(--sidebar-user-bg);
  border-radius: 10px;
  text-align: left;
}
.sidebar-user-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: var(--sidebar-avatar-bg);
  color: var(--text-inverse);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  flex-shrink: 0;
  overflow: hidden;
}
.sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sidebar-user-info { display: flex; flex-direction: column; min-width: 0; flex: 1; }
.sidebar-user-info strong {
  color: var(--sidebar-text);
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sidebar-user-info small {
  color: var(--sidebar-text-muted);
  font-size: 0.72rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  padding: 0.5rem 0.75rem;
  gap: 2px;
  flex: 1;
}

.menu-section {
  color: var(--sidebar-section);
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 1rem 1rem 0.5rem;
  margin-top: 0.5rem;
}
.menu-section:first-child { margin-top: 0; padding-top: 0.5rem; }

.menu-item {
  padding: 0.7rem 1rem;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: all 0.2s ease;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  font-size: 0.92rem;
  font-weight: 500;
  position: relative;
  border-left: 3px solid transparent;
  margin-left: -3px;
}
.menu-item:hover { background: var(--sidebar-hover-bg); color: var(--sidebar-active-text); }
.menu-item.active {
  background: var(--sidebar-active-bg);
  color: var(--sidebar-active-text);
  border-left-color: var(--sidebar-active-border);
}
.menu-item.active .menu-icon { color: var(--sidebar-active-border); }
.menu-icon {
  font-size: 1.1rem;
  width: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--sidebar-icon);
  transition: color 0.2s ease;
  flex-shrink: 0;
}
.menu-item:hover .menu-icon { color: var(--sidebar-text); }
.menu-text { flex: 1; }

.menu-badge {
  background: var(--badge-bg);
  color: var(--badge-text);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  min-width: 22px;
  text-align: center;
  animation: badge-pulse 2s infinite;
}

.sidebar-footer {
  padding: 1rem 0.75rem;
  border-top: 1px solid var(--sidebar-divider);
}
.logout-btn {
  width: 100%;
  background: transparent;
  border: 1px solid var(--sidebar-divider);
  color: var(--sidebar-text);
  padding: 0.7rem 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  font-size: 0.92rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}
.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
  color: #fca5a5;
}

.sidebar-overlay {
  display: none;
}

@media (max-width: 768px) {
  .funcionario-sidebar { transform: translateX(-100%); }
  .funcionario-sidebar.show { transform: translateX(0); }
  .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: -1;
  }
}
</style>
