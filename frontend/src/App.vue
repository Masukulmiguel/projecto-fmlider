<template>
  <div id="app" class="app">
    <AdminSidebar v-if="isAdminRoute" />
    <FuncionarioSidebar v-else-if="isFuncionarioRoute" :isOpen="sidebarOpen" />
    <ClienteSidebar v-else-if="isClienteRoute" :isOpen="sidebarOpen" />
    <div :class="layoutClass">
      <AdminNavbar v-if="isAdminRoute" />
      <FuncionarioNavbar v-else-if="isFuncionarioRoute" @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <ClienteNavbar v-else-if="isClienteRoute" @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <PublicHeader v-else />

      <main>
        <RouterView />
      </main>

      <PublicFooter v-if="!isAdminRoute && !isClienteRoute && !isFuncionarioRoute" />

      <ChatbotWidget v-if="!isAdminRoute && !isClienteRoute && !isFuncionarioRoute" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import PublicHeader from '@/components/PublicHeader.vue'
import PublicFooter from '@/components/PublicFooter.vue'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminNavbar from '@/admin/components/AdminNavbar.vue'
import ClienteSidebar from '@/cliente/components/ClienteSidebar.vue'
import ClienteNavbar from '@/cliente/components/ClienteNavbar.vue'
import FuncionarioSidebar from '@/funcionario/components/FuncionarioSidebar.vue'
import FuncionarioNavbar from '@/funcionario/components/FuncionarioNavbar.vue'
import ChatbotWidget from '@/components/ChatbotWidget.vue'
import { RouterView } from 'vue-router'
import { trackVisitor } from '@/utils/visitor'

const route = useRoute()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

const isAdminRoute = computed(() => route.meta?.layout === 'admin')
const isClienteRoute = computed(() => route.meta?.layout === 'cliente')
const isFuncionarioRoute = computed(() => route.meta?.layout === 'funcionario')

const layoutClass = computed(() => {
  if (isAdminRoute.value) return 'admin-content'
  if (isClienteRoute.value) return 'cliente-content'
  if (isFuncionarioRoute.value) return 'funcionario-content'
  return 'main-content'
})

onMounted(() => {
  if (!isAdminRoute.value && !isClienteRoute.value && !isFuncionarioRoute.value) {
    trackVisitor()
  }
})

watch(() => route.fullPath, () => {
  if (!isAdminRoute.value && !isClienteRoute.value && !isFuncionarioRoute.value) {
    trackVisitor()
  }
})
</script>

<style scoped>
.app {
  display: flex;
  min-height: 100vh;
}

.main-content {
  width: 100%;
  flex: 1;
}

.admin-content,
.cliente-content,
.funcionario-content {
  flex: 1;
  margin-left: 250px;
  min-height: 100vh;
}

main {
  min-height: calc(100vh - 70px);
}

@media (max-width: 768px) {
  .admin-content,
  .cliente-content,
  .funcionario-content {
    margin-left: 0;
  }
}
</style>
