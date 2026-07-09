<template>
  <div class="funcionario-sidebar" :class="{ show: isOpen, collapsed: collapsed }">
    <div class="sidebar-overlay" @click="$emit('close')"></div>
    <div class="sidebar-inner">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          <img :src="logoUrl" alt="FMLider" class="brand-logo" />
          <span class="brand-text">FMLider</span>
        </div>
        <button class="collapse-btn d-none d-lg-flex" @click="emit('toggle-collapse')" :title="collapsed ? t('admin_sidebar.expand') : t('admin_sidebar.collapse')">
          <i :class="collapsed ? 'bi bi-chevron-double-right' : 'bi bi-chevron-double-left'"></i>
        </button>
        <button class="close-btn d-lg-none" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">{{ t('funcionario.sidebar_main') }}</div>
        <router-link to="/funcionario" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_dashboard') : ''">
          <i class="bi bi-grid-1x2-fill nav-icon"></i>
          <span class="nav-text">{{ t('funcionario.sidebar_dashboard') }}</span>
        </router-link>
        <router-link to="/funcionario/mensagens" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_messages') : ''">
          <i class="bi bi-chat-dots-fill nav-icon"></i>
          <span class="nav-text">{{ t('funcionario.sidebar_messages') }}</span>
          <span v-if="chatUnread > 0" class="nav-badge">{{ chatUnread }}</span>
        </router-link>

        <template v-if="can('clients.view')">
          <div class="nav-section">{{ t('funcionario.sidebar_clients') }}</div>
          <router-link to="/funcionario/clientes" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_clients') : ''">
            <i class="bi bi-people-fill nav-icon"></i>
            <span class="nav-text">{{ t('funcionario.sidebar_clients') }}</span>
          </router-link>
        </template>

        <template v-if="can('documentos.view') || can('contactos.view')">
          <div class="nav-section">{{ t('funcionario.sidebar_operations') }}</div>
          <router-link v-if="can('documentos.view')" to="/funcionario/documentos" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_documents') : ''">
            <i class="bi bi-file-earmark-text-fill nav-icon"></i>
            <span class="nav-text">{{ t('funcionario.sidebar_documents') }}</span>
          </router-link>
          <router-link v-if="can('contactos.view')" to="/funcionario/contactos" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_contacts') : ''">
            <i class="bi bi-person-lines-fill nav-icon"></i>
            <span class="nav-text">{{ t('funcionario.sidebar_contacts') }}</span>
          </router-link>
          <router-link v-if="can('licenciamentos.view')" to="/funcionario/licenciamentos" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_licenciamentos') : ''">
            <i class="bi bi-sticky-fill nav-icon"></i>
            <span class="nav-text">{{ t('funcionario.sidebar_licenciamentos') }}</span>
          </router-link>
          <router-link v-if="can('documentos.view')" to="/funcionario/contentores" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Contentores' : ''">
            <i class="bi bi-box-seam nav-icon"></i>
            <span class="nav-text">Contentores</span>
          </router-link>
          <router-link v-if="can('documentos.view')" to="/funcionario/processos" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Processos' : ''">
            <i class="bi bi-clipboard2-data nav-icon"></i>
            <span class="nav-text">Processos</span>
          </router-link>
        </template>

        <template v-if="can('logistica.view')">
          <div class="nav-section">Logística</div>
          <router-link to="/funcionario/entregas" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Entregas' : ''">
            <i class="bi bi-truck nav-icon"></i>
            <span class="nav-text">Entregas</span>
          </router-link>
          <router-link v-if="can('motoristas.view')" to="/funcionario/motoristas" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Motoristas' : ''">
            <i class="bi bi-person-badge nav-icon"></i>
            <span class="nav-text">Motoristas</span>
          </router-link>
          <router-link v-if="can('camioes.view')" to="/funcionario/camioes" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Camiões' : ''">
            <i class="bi bi-truck nav-icon"></i>
            <span class="nav-text">Camiões</span>
          </router-link>
          <router-link v-if="can('logistica.view') || can('documentos.view')" to="/funcionario/contentores" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Contentores' : ''">
            <i class="bi bi-box-seam nav-icon"></i>
            <span class="nav-text">Contentores</span>
          </router-link>
          <router-link v-if="can('logistica.view')" to="/funcionario/processos" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? 'Processos' : ''">
            <i class="bi bi-clipboard2-data nav-icon"></i>
            <span class="nav-text">Processos</span>
          </router-link>
        </template>

        <div class="nav-section">{{ t('funcionario.sidebar_account') }}</div>
        <router-link to="/funcionario/perfil" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_profile') : ''">
          <i class="bi bi-person nav-icon"></i>
          <span class="nav-text">{{ t('funcionario.sidebar_profile') }}</span>
        </router-link>
        <router-link to="/mudar-senha" class="nav-item" active-class="active" :class="{ 'icon-only': collapsed }" :title="collapsed ? t('funcionario.sidebar_change_password') : ''">
          <i class="bi bi-shield-lock nav-icon"></i>
          <span class="nav-text">{{ t('funcionario.sidebar_change_password') }}</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="footer-user" v-if="authStore.user">
          <div class="user-avatar">{{ initials(authStore.user.name) }}</div>
          <div class="user-info" v-if="!collapsed">
            <div class="user-name">{{ authStore.user.name }}</div>
            <div class="user-dept" v-if="authStore.user.departamento">{{ deptLabels[authStore.user.departamento] || authStore.user.position }}</div>
            <div class="user-role" v-else>{{ authStore.user.position || t('funcionario.profile_role') }}</div>
          </div>
        </div>
        <button class="logout-btn" :class="{ 'icon-only': collapsed }" @click="logout" :title="collapsed ? t('funcionario.sidebar_logout') : ''">
          <i class="bi bi-box-arrow-right"></i>
          <span v-if="!collapsed">{{ t('funcionario.sidebar_logout') }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useChatStore } from '@/stores/chatStore'
import { useSiteImages } from '@/composables/useSiteImages'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const { getImage } = useSiteImages()

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  collapsed: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'toggle-collapse'])

const authStore = useAuthStore()
const logoUrl = computed(() => getImage('header', 'logo', '/assets/img/logo.png'))

const deptLabels = {
  certificacao: 'Certificação',
  documentacao: 'Documentação',
  licenciamentos: 'Licenciamentos',
  facturacao: 'Facturação',
  logistica: 'Logística',
  administracao: 'Administração'
}
const chatStore = useChatStore()
const router = useRouter()
const chatUnread = ref(0)
let pollInterval = null

const deptPermissions = {
  certificacao: ['dashboard.view', 'clients.view', 'contactos.view', 'contactos.manage', 'chat.view', 'chat.reply'],
  documentacao: ['dashboard.view', 'documentos.view', 'documentos.manage', 'clients.view', 'contactos.view', 'chat.view', 'contentores.view', 'contentores.manage', 'processos.view', 'processos.manage'],
  licenciamentos: ['dashboard.view', 'licenciamentos.view', 'licenciamentos.manage', 'clients.view', 'contactos.view', 'chat.view'],
  facturacao: ['dashboard.view', 'clients.view', 'clients.manage', 'contactos.view', 'chat.view'],
  logistica: ['dashboard.view', 'logistica.view', 'logistica.manage', 'motoristas.view', 'motoristas.manage', 'camioes.view', 'camioes.manage', 'entregas.view', 'entregas.manage', 'contentores.view', 'processos.view', 'clients.view', 'contactos.view', 'chat.view'],
  administracao: ['dashboard.view', 'clients.view', 'clients.manage', 'documentos.view', 'documentos.manage', 'contactos.view', 'contactos.manage', 'chat.view', 'chat.reply', 'licenciamentos.view', 'licenciamentos.manage', 'logistica.view', 'logistica.manage', 'motoristas.view', 'motoristas.manage', 'camioes.view', 'camioes.manage', 'entregas.view', 'entregas.manage', 'contentores.view', 'contentores.manage', 'processos.view', 'processos.manage', 'visitors.view', 'content.manage']
}

const can = (perm) => {
  if (authStore.isAdmin) return true
  const dept = authStore.user?.departamento
  if (!dept) {
    const perms = authStore.user?.permissions
    if (!perms || perms.length === 0) return true
    return perms.includes(perm)
  }
  return deptPermissions[dept]?.includes(perm) || false
}
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

onMounted(() => {
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
  background: #ffffff;
  color: #050505;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  overflow: hidden;
  z-index: 1100;
  transition: width 0.2s ease;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e4e6eb;
}

.funcionario-sidebar.collapsed {
  width: 72px;
}

.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: transparent;
  z-index: 999;
}

.sidebar-inner {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  flex-shrink: 0;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: contain;
  flex-shrink: 0;
}

.brand-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1877f2;
  white-space: nowrap;
  transition: opacity 0.2s;
}

.collapsed .brand-text {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.collapse-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 50%;
  background: #f0f2f5;
  color: #65676b;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s;
}

.collapse-btn:hover {
  background: #e4e6eb;
  color: #050505;
}

.collapsed .collapse-btn {
  transform: rotate(180deg);
}

.close-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 50%;
  background: #f0f2f5;
  color: #65676b;
  font-size: 1rem;
  cursor: pointer;
}

.close-btn:hover {
  background: #e4e6eb;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 8px;
}

.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.nav-section {
  color: #65676b;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 12px 12px 6px;
  white-space: nowrap;
  overflow: hidden;
}

.collapsed .nav-section {
  text-align: center;
  padding: 12px 4px 6px;
  font-size: 0;
}

.collapsed .nav-section::after {
  content: '•••';
  font-size: 0.6rem;
  display: block;
  text-align: center;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  color: #050505;
  text-decoration: none;
  font-size: 0.938rem;
  font-weight: 500;
  transition: background 0.15s;
  margin-bottom: 2px;
  position: relative;
}

.nav-item:hover {
  background: #f0f2f5;
  color: #050505;
  text-decoration: none;
}

.nav-item.active {
  background: #e7f3ff;
  color: #1877f2;
  font-weight: 600;
}

.nav-item.active .nav-icon {
  color: #1877f2;
}

.nav-item.icon-only {
  justify-content: center;
  padding: 10px;
}

.nav-icon {
  font-size: 1.2rem;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #65676b;
  transition: color 0.15s;
}

.nav-item:hover .nav-icon {
  color: #050505;
}

.nav-item.active .nav-icon {
  color: #1877f2;
}

.nav-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  transition: opacity 0.2s;
}

.collapsed .nav-text {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.nav-badge {
  background: #e41e3f;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
  flex-shrink: 0;
  line-height: 1.2;
}

.collapsed .nav-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  padding: 1px 4px;
  min-width: 14px;
  font-size: 0.6rem;
}

.sidebar-footer {
  padding: 12px;
  border-top: 1px solid #e4e6eb;
  flex-shrink: 0;
}

.footer-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 8px;
  margin-bottom: 8px;
  overflow: hidden;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0a5dc2);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
}

.user-info {
  overflow: hidden;
  transition: opacity 0.2s;
}

.collapsed .user-info {
  opacity: 0;
  width: 0;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #050505;
  white-space: nowrap;
}

.user-role {
  font-size: 0.75rem;
  color: #65676b;
}

.user-dept {
  font-size: 0.7rem;
  color: #1a365d;
  font-weight: 600;
  background: #e8f0fe;
  padding: 1px 6px;
  border-radius: 4px;
  display: inline-block;
  margin-top: 2px;
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #65676b;
  font-size: 0.938rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
  overflow: hidden;
}

.logout-btn:hover {
  background: #fee2e2;
  color: #dc3545;
}

.logout-btn.icon-only {
  justify-content: center;
  padding: 10px;
}

@media (max-width: 1023px) {
  .funcionario-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1100;
  }

  .funcionario-sidebar.show {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .collapse-btn {
    display: none !important;
  }

  .close-btn {
    display: flex;
  }
}
</style>
