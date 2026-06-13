<template>
  <div class="admin-sidebar" :class="{ show: isOpen }">
    <div class="sidebar-overlay" @click="$emit('close')"></div>
    <div class="sidebar-inner">
      <div class="sidebar-logo">
        <img src="/assets/img/logo.png" alt="FMLider" height="36">
        <span class="logo-text">FMLider</span>
      </div>

      <nav class="sidebar-menu">
        <div class="menu-section">
          <span class="section-label">Principal</span>
        </div>
        <router-link to="/admin" class="menu-item" active-class="active" :class="{ active: $route.path === '/admin' }">
          <i class="bi bi-grid-1x2-fill menu-icon"></i>
          <span class="menu-text">Dashboard</span>
        </router-link>
        <router-link to="/admin/utilizadores" class="menu-item" active-class="active">
          <i class="bi bi-people-fill menu-icon"></i>
          <span class="menu-text">Utilizadores</span>
          <span v-if="pendingCount > 0" class="menu-badge">{{ pendingCount }}</span>
        </router-link>
        <router-link to="/admin/funcionarios" class="menu-item" active-class="active">
          <i class="bi bi-person-badge-fill menu-icon"></i>
          <span class="menu-text">Funcionários</span>
        </router-link>

        <div class="menu-section">
          <span class="section-label">Operações</span>
        </div>
        <router-link to="/admin/embarques" class="menu-item" active-class="active">
          <i class="bi bi-box-seam-fill menu-icon"></i>
          <span class="menu-text">Embarques</span>
        </router-link>
        <router-link to="/admin/cotacoes" class="menu-item" active-class="active">
          <i class="bi bi-receipt menu-icon"></i>
          <span class="menu-text">Cotações</span>
        </router-link>
        <router-link to="/admin/documentos" class="menu-item" active-class="active">
          <i class="bi bi-file-earmark-text-fill menu-icon"></i>
          <span class="menu-text">Documentos</span>
        </router-link>
        <router-link to="/admin/contactos-cliente" class="menu-item" active-class="active">
          <i class="bi bi-person-rolodex menu-icon"></i>
          <span class="menu-text">Contactos</span>
        </router-link>

        <div class="menu-section">
          <span class="section-label">Conteúdo</span>
        </div>
        <router-link to="/admin/servicos" class="menu-item" active-class="active">
          <i class="bi bi-tools menu-icon"></i>
          <span class="menu-text">Serviços</span>
        </router-link>
        <router-link to="/admin/noticias" class="menu-item" active-class="active">
          <i class="bi bi-newspaper menu-icon"></i>
          <span class="menu-text">Notícias</span>
        </router-link>
        <router-link to="/admin/galeria" class="menu-item" active-class="active">
          <i class="bi bi-images menu-icon"></i>
          <span class="menu-text">Galeria</span>
        </router-link>
        <router-link to="/admin/parceiros" class="menu-item" active-class="active">
          <i class="bi bi-handshake menu-icon"></i>
          <span class="menu-text">Parceiros</span>
        </router-link>
        <router-link to="/admin/banners" class="menu-item" active-class="active">
          <i class="bi bi-megaphone-fill menu-icon"></i>
          <span class="menu-text">Banners</span>
        </router-link>
        <router-link to="/admin/testemunhos" class="menu-item" active-class="active">
          <i class="bi bi-star-fill menu-icon"></i>
          <span class="menu-text">Testemunhos</span>
        </router-link>
        <router-link to="/admin/faqs" class="menu-item" active-class="active">
          <i class="bi bi-question-circle-fill menu-icon"></i>
          <span class="menu-text">FAQs</span>
        </router-link>

        <div class="menu-section">
          <span class="section-label">Sistema</span>
        </div>
        <router-link to="/admin/mensagens" class="menu-item" active-class="active">
          <i class="bi bi-chat-dots-fill menu-icon"></i>
          <span class="menu-text">Mensagens</span>
          <span v-if="chatUnread > 0" class="menu-badge badge-danger">{{ chatUnread }}</span>
        </router-link>
        <router-link to="/admin/visitantes" class="menu-item" active-class="active">
          <i class="bi bi-globe2 menu-icon"></i>
          <span class="menu-text">Visitantes</span>
        </router-link>
        <router-link to="/admin/contactos" class="menu-item" active-class="active">
          <i class="bi bi-envelope-fill menu-icon"></i>
          <span class="menu-text">Contactos Form</span>
        </router-link>
        <router-link to="/admin/configuracoes" class="menu-item" active-class="active">
          <i class="bi bi-gear-fill menu-icon"></i>
          <span class="menu-text">Definições</span>
        </router-link>
      </nav>

      <div v-if="authStore.user" class="sidebar-user">
        <div class="sidebar-user-avatar">
          <img v-if="authStore.user.photo" :src="authStore.user.photo" :alt="authStore.user.name">
          <span v-else>{{ initials(authStore.user.name) }}</span>
        </div>
        <div class="sidebar-user-info">
          <strong class="sidebar-user-name">{{ authStore.user.name }}</strong>
          <small class="sidebar-user-role" v-if="authStore.user.position">{{ authStore.user.position }}</small>
        </div>
        <button class="sidebar-user-action" @click="logout" title="Sair">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'
import { useChatStore } from '@/stores/chatStore'

defineProps({
  isOpen: { type: Boolean, default: false }
})

defineEmits(['close'])

const router = useRouter()
const authStore = useAuthStore()
const chatStore = useChatStore()
const pendingCount = ref(0)
const chatUnread = ref(0)
let pollInterval = null

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const fetchPending = async () => {
  if (!authStore.token) return
  try {
    const response = await axios.get('/api/admin/users/pending/count', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    if (response.data.success) {
      pendingCount.value = response.data.data.count
    }
  } catch (error) {
    pendingCount.value = 0
  }
}

const fetchChatUnread = async () => {
  if (!authStore.token) return
  try {
    await chatStore.refreshUnread()
    chatUnread.value = chatStore.totalUnread
  } catch (error) {
    chatUnread.value = 0
  }
}

onMounted(() => {
  fetchPending()
  fetchChatUnread()
  pollInterval = setInterval(() => {
    fetchPending()
    fetchChatUnread()
  }, 30000)
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<style scoped>
.admin-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  width: 260px;
  z-index: 1000;
}

.sidebar-overlay {
  display: none;
}

.sidebar-inner {
  width: 100%;
  height: 100%;
  background: #ffffff;
  border-right: 1px solid #dddfe2;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-logo {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid #f0f2f5;
  min-height: 64px;
}

.sidebar-logo img {
  height: 36px;
  object-fit: contain;
}

.logo-text {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1c1e21;
}

.sidebar-menu {
  flex: 1;
  overflow-y: auto;
  padding: 8px 12px;
  scrollbar-width: thin;
  scrollbar-color: #dddfe2 transparent;
}

.sidebar-menu::-webkit-scrollbar {
  width: 6px;
}

.sidebar-menu::-webkit-scrollbar-thumb {
  background: #dddfe2;
  border-radius: 3px;
}

.menu-section {
  padding: 16px 12px 8px;
}

.section-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #65676b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  margin: 2px 0;
  color: #65676b;
  text-decoration: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
  position: relative;
  border-left: 3px solid transparent;
}

.menu-item:hover {
  background: #f0f2f5;
  color: #1c1e21;
}

.menu-item.active {
  background: #e7f3ff;
  color: #1877f2;
  border-left-color: #1877f2;
  font-weight: 600;
}

.menu-item.active .menu-icon {
  color: #1877f2;
}

.menu-icon {
  font-size: 1.1rem;
  width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #65676b;
  transition: color 0.2s ease;
  flex-shrink: 0;
}

.menu-item:hover .menu-icon {
  color: #1c1e21;
}

.menu-text {
  flex: 1;
}

.menu-badge {
  background: #f7b928;
  color: #1c1e21;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  min-width: 22px;
  text-align: center;
  line-height: 1.4;
}

.menu-badge.badge-danger {
  background: #dc3545;
  color: #ffffff;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border-top: 1px solid #f0f2f5;
  background: #fafbfc;
}

.sidebar-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0d5bbd);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 600;
  flex-shrink: 0;
  overflow: hidden;
}

.sidebar-user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sidebar-user-info {
  flex: 1;
  min-width: 0;
}

.sidebar-user-name {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #1c1e21;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-user-role {
  display: block;
  font-size: 0.75rem;
  color: #65676b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-user-action {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #65676b;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.sidebar-user-action:hover {
  background: #fee2e2;
  color: #dc3545;
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .admin-sidebar.show {
    transform: translateX(0);
  }

  .admin-sidebar.show .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: -1;
  }
}
</style>
