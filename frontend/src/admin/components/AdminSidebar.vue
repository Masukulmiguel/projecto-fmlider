<template>
  <div class="admin-sidebar" :class="{ show: isOpen }">
    <div class="sidebar-logo">
      <img src="/assets/img/logo.png" alt="FMLider" height="42">
      <small class="d-block sidebar-subtitle">Painel Administrativo</small>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-section">Geral</div>
      <router-link to="/admin" class="menu-item" active-class="active">
        <i class="bi bi-grid-1x2-fill menu-icon"></i>
        <span class="menu-text">Dashboard</span>
      </router-link>
      <router-link to="/admin/utilizadores" class="menu-item" active-class="active">
        <i class="bi bi-people-fill menu-icon"></i>
        <span class="menu-text">Clientes</span>
        <span v-if="pendingCount > 0" class="menu-badge">{{ pendingCount }}</span>
      </router-link>
      <router-link to="/admin/mensagens" class="menu-item" active-class="active">
        <i class="bi bi-chat-dots-fill menu-icon"></i>
        <span class="menu-text">Mensagens</span>
        <span v-if="chatUnread > 0" class="menu-badge">{{ chatUnread }}</span>
      </router-link>

      <div class="menu-section">Operações</div>
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
        <span class="menu-text">Contactos Clientes</span>
      </router-link>

      <div class="menu-section">Site</div>
      <router-link to="/admin/funcionarios" class="menu-item" active-class="active">
        <i class="bi bi-person-badge-fill menu-icon"></i>
        <span class="menu-text">Funcionários</span>
      </router-link>
      <router-link to="/admin/visitantes" class="menu-item" active-class="active">
        <i class="bi bi-globe2 menu-icon"></i>
        <span class="menu-text">Visitantes</span>
      </router-link>
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
      <router-link to="/admin/contactos" class="menu-item" active-class="active">
        <i class="bi bi-envelope-fill menu-icon"></i>
        <span class="menu-text">Mensagens</span>
      </router-link>
      <router-link to="/admin/testemunhos" class="menu-item" active-class="active">
        <i class="bi bi-star-fill menu-icon"></i>
        <span class="menu-text">Testemunhos</span>
      </router-link>
      <router-link to="/admin/faqs" class="menu-item" active-class="active">
        <i class="bi bi-question-circle-fill menu-icon"></i>
        <span class="menu-text">FAQs</span>
      </router-link>
      <router-link to="/admin/banners" class="menu-item" active-class="active">
        <i class="bi bi-megaphone-fill menu-icon"></i>
        <span class="menu-text">Banners</span>
      </router-link>

      <div class="menu-section">Sistema</div>
      <router-link to="/admin/configuracoes" class="menu-item" active-class="active">
        <i class="bi bi-gear-fill menu-icon"></i>
        <span class="menu-text">Configurações</span>
      </router-link>
    </nav>

    <div v-if="authStore.user" class="sidebar-user">
      <div class="sidebar-user-avatar">
        <img v-if="authStore.user.photo" :src="authStore.user.photo" :alt="authStore.user.name">
        <span v-else>{{ initials(authStore.user.name) }}</span>
      </div>
      <div class="sidebar-user-info">
        <strong>{{ authStore.user.name }}</strong>
        <small v-if="authStore.user.position"><i class="bi bi-person-badge"></i> {{ authStore.user.position }}</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'
import { useChatStore } from '@/stores/chatStore'

defineProps({
  isOpen: { type: Boolean, default: false }
})

const authStore = useAuthStore()
const chatStore = useChatStore()
const pendingCount = ref(0)
const chatUnread = ref(0)
let pollInterval = null

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

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
  width: 260px;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  padding: 1rem 0;
  z-index: 1000;
  transition: transform 0.3s ease;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-logo {
  padding: 1.75rem 1.5rem;
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 1rem;
}

.sidebar-logo img {
  filter: brightness(0) invert(1);
  margin-bottom: 0.5rem;
}

.sidebar-subtitle {
  color: #94a3b8;
  font-size: 0.72rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-weight: 500;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  padding: 0.5rem 0.75rem;
  gap: 2px;
  flex: 1;
  overflow-y: auto;
}

.menu-section {
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 1rem 1rem 0.5rem;
  margin-top: 0.5rem;
}

.menu-section:first-child { margin-top: 0; padding-top: 0.5rem; }

.menu-item {
  padding: 0.75rem 1rem;
  color: #cbd5e1;
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

.menu-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.menu-item.active {
  background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
  color: #ffffff;
  border-left-color: #3b82f6;
}

.menu-item.active .menu-icon {
  color: #3b82f6;
}

.menu-icon {
  font-size: 1.15rem;
  width: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  transition: color 0.2s ease;
  flex-shrink: 0;
}

.menu-item:hover .menu-icon {
  color: #e2e8f0;
}

.menu-text {
  flex: 1;
}

.menu-badge {
  background: #f59e0b;
  color: #1e293b;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  min-width: 22px;
  text-align: center;
  animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
  0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
  50% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }
  .admin-sidebar.show {
    transform: translateX(0);
  }
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  margin: 0 0.75rem 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 1rem;
}
.sidebar-user-avatar {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600;
  flex-shrink: 0; overflow: hidden;
}
.sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sidebar-user-info { min-width: 0; flex: 1; }
.sidebar-user-info strong {
  display: block;
  font-size: 0.85rem;
  color: white;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sidebar-user-info small {
  display: block;
  font-size: 0.7rem;
  color: #94a3b8;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
</style>
