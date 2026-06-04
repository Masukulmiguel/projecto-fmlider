<template>
  <div id="app" class="app">
    <AdminSidebar v-if="isAdmin" />
    <div :class="{ 'admin-content': isAdmin, 'main-content': !isAdmin }">
      <AdminNavbar v-if="isAdmin" />
      <PublicHeader v-else />
      
      <main>
        <RouterView />
      </main>
      
      <PublicFooter v-if="!isAdmin" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import PublicHeader from '@/components/PublicHeader.vue'
import PublicFooter from '@/components/PublicFooter.vue'
import AdminSidebar from '@/admin/components/AdminSidebar.vue'
import AdminNavbar from '@/admin/components/AdminNavbar.vue'
import { RouterView } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const isAdmin = computed(() => {
  return router.currentRoute.value.meta.admin === true
})
</script>

<style scoped>
.app {
  display: flex;
  min-height: 100vh;
}

.main-content {
  width: 100%;
}

.admin-content {
  flex: 1;
  margin-left: 250px;
}

main {
  min-height: 100vh;
}

@media (max-width: 768px) {
  .admin-content {
    margin-left: 0;
  }
}
</style>
