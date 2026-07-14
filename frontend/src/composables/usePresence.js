import { ref, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const ONLINE_THRESHOLD = 60000
const HEARTBEAT_INTERVAL = 30000

export function usePresence() {
  const onlineUserIds = ref(new Set())
  let heartbeatTimer = null
  let checkTimer = null
  const authStore = useAuthStore()

  const goOnline = async () => {
    const userId = authStore.user?.id
    if (!userId) return
    try {
      await supabase.from('user_presence').upsert({
        user_id: userId,
        last_seen: new Date().toISOString(),
        is_online: true
      }, { onConflict: 'user_id' })
    } catch (e) { /* silent */ }
  }

  const goOffline = async () => {
    const userId = authStore.user?.id
    if (!userId) return
    try {
      await supabase.from('user_presence').update({
        last_seen: new Date().toISOString(),
        is_online: false
      }).eq('user_id', userId)
    } catch (e) { /* silent */ }
  }

  const checkOnlineUsers = async () => {
    try {
      const { data } = await supabase
        .from('user_presence')
        .select('user_id, last_seen, is_online')
        .eq('is_online', true)

      if (data) {
        const now = Date.now()
        const online = new Set()
        for (const p of data) {
          const diff = now - new Date(p.last_seen).getTime()
          if (diff < ONLINE_THRESHOLD) {
            online.add(p.user_id)
          }
        }
        onlineUserIds.value = online
      }
    } catch (e) { /* silent */ }
  }

  const isUserOnline = (userId) => onlineUserIds.value.has(userId)

  const startHeartbeat = () => {
    stopHeartbeat()
    if (!authStore.isAuthenticated) return
    goOnline()
    heartbeatTimer = setInterval(goOnline, HEARTBEAT_INTERVAL)
    checkTimer = setInterval(checkOnlineUsers, HEARTBEAT_INTERVAL)
    checkOnlineUsers()
  }

  const stopHeartbeat = () => {
    if (heartbeatTimer) { clearInterval(heartbeatTimer); heartbeatTimer = null }
    if (checkTimer) { clearInterval(checkTimer); checkTimer = null }
  }

  onMounted(() => {
    if (authStore.isAuthenticated) {
      startHeartbeat()
      window.addEventListener('online', goOnline)
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') goOnline()
      })
    }
  })

  onBeforeUnmount(() => {
    stopHeartbeat()
    window.removeEventListener('online', goOnline)
  })

  return { onlineUserIds, isUserOnline, goOnline, goOffline, startHeartbeat, stopHeartbeat }
}
