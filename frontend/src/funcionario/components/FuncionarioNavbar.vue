<template>
  <nav class="funcionario-navbar">
    <div class="navbar-content">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" @click="$emit('toggle-sidebar')">☰</button>
        <h2 class="page-title">{{ pageTitle }}</h2>
      </div>
      <div class="navbar-actions">
        <NotificationBell />
        <div class="user-info d-none d-sm-block text-end">
          <small class="text-muted">{{ authStore.user?.email }}</small>
          <small v-if="authStore.user?.position" class="d-block text-muted" style="font-size: 0.7rem;">
            <i class="bi bi-person-badge"></i> {{ authStore.user.position }}
          </small>
        </div>
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
              <i class="bi bi-person-fill me-1"></i> Perfil
            </router-link></li>
            <li><router-link to="/mudar-senha" class="dropdown-item">
              <i class="bi bi-shield-lock me-1"></i> Alterar senha
            </router-link></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="#" class="dropdown-item" @click.prevent="logout">
              <i class="bi bi-box-arrow-right me-1"></i> Sair
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
import NotificationBell from '@/components/NotificationBell.vue'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const pageTitle = computed(() => {
  const titles = {
    'FuncionarioDashboard': 'Dashboard',
    'FuncionarioMessages': 'Mensagens',
    'FuncionarioProfile': 'Perfil',
    'FuncionarioEmbarques': 'Embarques',
    'FuncionarioCotacoes': 'Cotações',
    'FuncionarioDocumentos': 'Documentos',
    'FuncionarioContactos': 'Contactos',
    'FuncionarioClientes': 'Clientes',
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
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
  margin-left: 250px;
}
.navbar-content { display: flex; justify-content: space-between; align-items: center; }
.page-title { font-size: 1.4rem; margin: 0; color: #0f172a; }
.navbar-actions { display: flex; gap: 1rem; align-items: center; }
.user-info { font-size: 0.85rem; line-height: 1.1; }

.user-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 700;
  overflow: hidden;
  flex-shrink: 0;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }

@media (max-width: 768px) {
  .funcionario-navbar { margin-left: 0; }
}
</style>
