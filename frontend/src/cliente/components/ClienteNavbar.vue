<template>
  <nav class="cliente-navbar">
    <div class="navbar-content">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" @click="$emit('toggle-sidebar')">☰</button>
        <h2 class="page-title">{{ pageTitle }}</h2>
      </div>
      <div class="navbar-center d-none d-md-block">
        <div class="search-bar">
          <i class="bi bi-search search-icon"></i>
          <input
            type="text"
            class="search-input"
            placeholder="Pesquisar..."
            v-model="searchQuery"
            @keyup.enter="handleSearch"
          />
        </div>
      </div>
      <div class="navbar-actions">
        <NotificationBell />
        <div class="user-info d-none d-sm-block">
          <small class="text-muted">{{ authStore.user?.email }}</small>
        </div>
        <div class="user-dropdown">
          <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <div class="user-avatar">
              <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user?.name">
              <span v-else>{{ initials(authStore.user?.name) }}</span>
            </div>
            {{ authStore.user?.name || 'Cliente' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><router-link to="/perfil" class="dropdown-item">Perfil</router-link></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="#" class="dropdown-item" @click.prevent="logout">Sair</a></li>
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

const searchQuery = ref('')

const searchPages = [
  { keyword: 'embarque', route: '/embarques', label: 'Embarques' },
  { keyword: 'documento', route: '/documentos', label: 'Documentos' },
  { keyword: 'contacto', route: '/contactos', label: 'Contactos' },
  { keyword: 'cotação', route: '/cotacoes', label: 'Cotações' },
  { keyword: 'cotacao', route: '/cotacoes', label: 'Cotações' },
  { keyword: 'mensagem', route: '/mensagens', label: 'Mensagens' },
  { keyword: 'chat', route: '/mensagens', label: 'Mensagens' },
  { keyword: 'perfil', route: '/perfil', label: 'Perfil' },
]

const handleSearch = () => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return
  const match = searchPages.find(p => q.includes(p.keyword))
  if (match) {
    router.push(match.route)
    searchQuery.value = ''
  }
}

const pageTitle = computed(() => {
  const titles = {
    'ClienteDashboard': 'Dashboard',
    'ClienteProfile': 'Perfil',
    'SetupCompany': 'Configurar Empresa',
    'EmbarquesList': 'Embarques',
    'EmbarqueNew': 'Novo Embarque',
    'EmbarqueEdit': 'Editar Embarque',
    'DocumentosList': 'Documentos',
    'ContactosList': 'Contactos',
    'CotacoesList': 'Cotações',
    'CotacaoNew': 'Nova Cotação',
    'CotacaoEdit': 'Editar Cotação',
    'ClienteMessages': 'Mensagens'
  }
  return titles[route.name] || 'Cliente'
})

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const initials = (name) => (name || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
</script>

<style scoped>
.cliente-navbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
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

.page-title {
  font-size: 1.4rem;
  margin: 0;
  color: #1f2937;
}

.navbar-center { flex: 1; max-width: 400px; margin: 0 1.5rem; }
.search-bar { position: relative; }
.search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; }
.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  background: #fff;
  color: #1e293b;
  transition: border-color 0.2s;
}
.search-input:focus { border-color: #1877f2; }

.navbar-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.user-info {
  font-size: 0.85rem;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  flex-shrink: 0;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

@media (max-width: 768px) {
  .cliente-navbar {
    margin-left: 0;
  }
}
</style>
