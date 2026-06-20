<template>
  <div id="app" class="app" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <InactivityClock :timeout="600000" />
    <AdminSidebar v-if="isAdminRoute" :isOpen="sidebarOpen" :collapsed="sidebarCollapsed" @close="sidebarOpen = false" @toggle-collapse="sidebarCollapsed = !sidebarCollapsed" />
    <FuncionarioSidebar v-else-if="isFuncionarioRoute" :isOpen="sidebarOpen" :collapsed="sidebarCollapsed" @close="sidebarOpen = false" @toggle-collapse="sidebarCollapsed = !sidebarCollapsed" />
    <ClienteSidebar v-else-if="isClienteRoute" :isOpen="sidebarOpen" :collapsed="sidebarCollapsed" @close="sidebarOpen = false" @toggle-collapse="sidebarCollapsed = !sidebarCollapsed" />
    <div :class="layoutClass">
      <AdminNavbar v-if="isAdminRoute" @toggle-sidebar="sidebarOpen = !sidebarOpen" @toggle-collapse="sidebarCollapsed = !sidebarCollapsed" :collapsed="sidebarCollapsed" />
      <FuncionarioNavbar v-else-if="isFuncionarioRoute" @toggle-sidebar="sidebarOpen = !sidebarOpen" :collapsed="sidebarCollapsed" />
      <ClienteNavbar v-else-if="isClienteRoute" @toggle-sidebar="sidebarOpen = !sidebarOpen" :collapsed="sidebarCollapsed" />
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
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import PublicHeader from '@/components/PublicHeader.vue'
import PublicFooter from '@/components/PublicFooter.vue'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminNavbar from '@/admin/components/AdminNavbar.vue'
import ClienteSidebar from '@/cliente/components/ClienteSidebar.vue'
import ClienteNavbar from '@/cliente/components/ClienteNavbar.vue'
import FuncionarioSidebar from '@/funcionario/components/FuncionarioSidebar.vue'
import FuncionarioNavbar from '@/funcionario/components/FuncionarioNavbar.vue'
import ChatbotWidget from '@/components/ChatbotWidget.vue'
import InactivityClock from '@/components/InactivityClock.vue'
import { RouterView } from 'vue-router'
import { trackVisitor } from '@/utils/visitor'

const route = useRoute()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)

const isAdminRoute = computed(() => route.meta?.layout === 'admin')
const isClienteRoute = computed(() => route.meta?.layout === 'cliente')
const isFuncionarioRoute = computed(() => route.meta?.layout === 'funcionario')

const layoutClass = computed(() => {
  if (isAdminRoute.value) return 'admin-content'
  if (isClienteRoute.value) return 'cliente-content'
  if (isFuncionarioRoute.value) return 'funcionario-content'
  return 'main-content'
})

const checkWidth = () => {
  if (window.innerWidth < 768) {
    sidebarCollapsed.value = false
    sidebarOpen.value = false
  } else if (window.innerWidth < 1024) {
    sidebarCollapsed.value = true
    sidebarOpen.value = false
  } else {
    sidebarCollapsed.value = false
  }
}

onMounted(() => {
  themeStore.applyTheme()
  checkWidth()
  window.addEventListener('resize', checkWidth)
  if (!isAdminRoute.value && !isClienteRoute.value && !isFuncionarioRoute.value) {
    trackVisitor()
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkWidth)
})

watch(() => route.fullPath, () => {
  sidebarOpen.value = false
  if (!isAdminRoute.value && !isClienteRoute.value && !isFuncionarioRoute.value) {
    trackVisitor()
  }
})
</script>

<style scoped>
.app {
  display: flex;
  min-height: 100vh;
  background: var(--content-bg, #f0f2f5);
}

.main-content {
  width: 100%;
  flex: 1;
}

.admin-content,
.cliente-content,
.funcionario-content {
  flex: 1;
  margin-left: 260px;
  min-height: 100vh;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-collapsed .admin-content,
.sidebar-collapsed .cliente-content,
.sidebar-collapsed .funcionario-content {
  margin-left: 72px;
}

main {
  min-height: calc(100vh - 64px);
  padding: 0;
}

@media (max-width: 1023px) {
  .admin-content,
  .cliente-content,
  .funcionario-content {
    margin-left: 72px;
  }
}

@media (max-width: 767px) {
  .admin-content,
  .cliente-content,
  .funcionario-content {
    margin-left: 0;
  }
}
</style>
