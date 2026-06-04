<template>
  <navbar class="admin-navbar">
    <div class="navbar-content">
      <h2>{{ pageTitle }}</h2>
      <div class="navbar-actions">
        <div class="user-dropdown">
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
  </navbar>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

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
    'AdminProfile': 'Perfil'
  }
  return titles[route.name] || 'Admin'
})

const logout = () => {
  authStore.logout()
  router.push('/login')
}
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
</style>
