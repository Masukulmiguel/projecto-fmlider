<template>
  <div class="notif-wrapper" v-click-outside="close">
    <button class="notif-bell" :class="{ has: notif.unread > 0 }" @click="notif.toggleDropdown" aria-label="Notificações">
      <i class="bi bi-bell-fill"></i>
      <span v-if="notif.unread > 0" class="notif-badge">{{ notif.unread > 99 ? '99+' : notif.unread }}</span>
    </button>

    <transition name="notif-fade">
      <div v-if="notif.dropdownOpen" class="notif-dropdown">
        <div class="notif-header">
          <h6 class="mb-0">Notificações</h6>
          <button v-if="notif.unread > 0" class="btn btn-sm btn-link p-0" @click="markAllRead">
            Marcar todas como lidas
          </button>
        </div>
        <div class="notif-list">
          <div v-if="notif.loading && notif.items.length === 0" class="notif-empty">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>
          <div v-else-if="notif.items.length === 0" class="notif-empty">
            <i class="bi bi-bell-slash"></i>
            <p class="mb-0 mt-2 small">Sem notificações.</p>
          </div>
          <div
            v-for="n in notif.items"
            :key="n.id"
            class="notif-item"
            :class="{ unread: !n.is_read }"
            @click="handleClick(n)"
          >
            <div class="notif-icon">
              <i class="bi" :class="n.icon || 'bi-bell-fill'"></i>
            </div>
            <div class="notif-body">
              <div class="notif-title">{{ n.title }}</div>
              <div class="notif-text">{{ n.body }}</div>
              <div class="notif-time">{{ formatTime(n.created_at) }}</div>
            </div>
            <span v-if="!n.is_read" class="notif-dot"></span>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notificationStore'

const notif = useNotificationStore()
const router = useRouter()

const vClickOutside = {
  mounted(el, binding) {
    el._handler = (e) => { if (!el.contains(e.target)) binding.value() }
    document.addEventListener('click', el._handler)
  },
  unmounted(el) { document.removeEventListener('click', el._handler) }
}

const handleClick = async (n) => {
  if (!n.is_read) await notif.markRead(n.id)
  notif.closeDropdown()
  if (n.link) {
    try { router.push(n.link) } catch (e) {}
  }
}

const markAllRead = async () => { await notif.markRead() }

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const diff = (now - d) / 1000
  if (diff < 60) return 'agora'
  if (diff < 3600) return `${Math.floor(diff/60)}min`
  if (diff < 86400) return `${Math.floor(diff/3600)}h`
  if (diff < 7 * 86400) return `${Math.floor(diff/86400)}d`
  return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit' })
}

onMounted(() => notif.startPolling(15000))
onBeforeUnmount(() => notif.stopPolling())
</script>

<style scoped>
.notif-wrapper { position: relative; }

.notif-bell {
  position: relative;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #475569;
  border: none;
  font-size: 1.05rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, transform 0.2s;
}
.notif-bell:hover { background: #e2e8f0; color: #1e293b; }
.notif-bell.has { color: #2563eb; background: #eff6ff; }

.notif-badge {
  position: absolute;
  top: -2px; right: -2px;
  min-width: 18px; height: 18px;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  border-radius: 10px;
  padding: 0 5px;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
  animation: pulse-badge 2s infinite;
}
@keyframes pulse-badge {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
  50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
}

.notif-dropdown {
  position: absolute;
  top: 50px; right: 0;
  width: 380px;
  max-width: calc(100vw - 24px);
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(15, 23, 42, 0.05);
  z-index: 1100;
  overflow: hidden;
  max-height: 70vh;
  display: flex;
  flex-direction: column;
}
.notif-fade-enter-active, .notif-fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-6px); }

.notif-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #eef0f3;
  background: #f8fafc;
}
.notif-header h6 { font-weight: 700; color: #0f172a; }

.notif-list { flex: 1; overflow-y: auto; max-height: 60vh; }
.notif-list::-webkit-scrollbar { width: 6px; }
.notif-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

.notif-empty {
  text-align: center;
  color: #94a3b8;
  padding: 2.5rem 1rem;
}
.notif-empty i { font-size: 2rem; display: block; }

.notif-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
  align-items: flex-start;
}
.notif-item:hover { background: #f8fafc; }
.notif-item:last-child { border-bottom: none; }
.notif-item.unread { background: #eff6ff; }
.notif-item.unread:hover { background: #dbeafe; }

.notif-icon {
  width: 36px; height: 36px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 0.95rem;
}
.notif-body { flex: 1; min-width: 0; }
.notif-title { font-weight: 600; color: #0f172a; font-size: 0.88rem; line-height: 1.3; }
.notif-text { color: #475569; font-size: 0.8rem; line-height: 1.35; margin-top: 2px; word-wrap: break-word; }
.notif-time { color: #94a3b8; font-size: 0.7rem; margin-top: 4px; }

.notif-dot {
  width: 8px; height: 8px;
  background: #2563eb;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 8px;
}

@media (max-width: 480px) {
  .notif-dropdown { width: calc(100vw - 24px); right: -8px; }
}
</style>
