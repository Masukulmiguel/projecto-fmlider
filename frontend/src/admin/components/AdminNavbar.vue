<template>
  <nav class="admin-navbar">
    <div class="navbar-content">
      <h2>{{ pageTitle }}</h2>
      <div class="navbar-actions">
        <NotificationBell />
        <div class="user-dropdown d-flex align-items-center gap-2">
          <div class="user-avatar">
            <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user?.name">
            <span v-else>{{ initials(authStore.user?.name) }}</span>
          </div>
          <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
            {{ authStore.user?.name || 'Admin' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><router-link to="/admin/perfil" class="dropdown-item">Perfil</router-link></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="#" class="dropdown-item" @click.prevent="logout">Sair</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import NotificationBell from '@/components/NotificationBell.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const pageTitle = computed(() => {
  const titles = {
    'AdminDashboard': 'Dashboard',
    'AdminUsers': 'Clientes',
    'AdminServices': 'Serviços',
    'AdminNews': 'Notícias',
    'AdminGallery': 'Galeria',
    'AdminPartners': 'Parceiros',
    'AdminContacts': 'Mensagens',
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

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
</script>

<style scoped>
.admin-navbar {
  background: white;
  border-bottom: 1px solid #ddd;
  padding: 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
  margin-left: 250px;
}

.navbar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

@media (max-width: 768px) {
  .admin-navbar {
    margin-left: 0;
  }
}

.user-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600;
  overflow: hidden;
  flex-shrink: 0;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
</style>
