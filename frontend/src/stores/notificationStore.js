import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

function playNotificationSound() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)()

    const osc1 = ctx.createOscillator()
    const gain1 = ctx.createGain()
    osc1.type = 'sine'
    osc1.frequency.setValueAtTime(880, ctx.currentTime)
    gain1.gain.setValueAtTime(0.3, ctx.currentTime)
    gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15)
    osc1.connect(gain1)
    gain1.connect(ctx.destination)
    osc1.start(ctx.currentTime)
    osc1.stop(ctx.currentTime + 0.15)

    const osc2 = ctx.createOscillator()
    const gain2 = ctx.createGain()
    osc2.type = 'sine'
    osc2.frequency.setValueAtTime(1100, ctx.currentTime + 0.12)
    gain2.gain.setValueAtTime(0.3, ctx.currentTime + 0.12)
    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3)
    osc2.connect(gain2)
    gain2.connect(ctx.destination)
    osc2.start(ctx.currentTime + 0.12)
    osc2.stop(ctx.currentTime + 0.3)
  } catch (e) { /* silent */ }
}

export const useNotificationStore = defineStore('notifications', () => {
  const items = ref([])
  const unread = ref(0)
  const loading = ref(false)
  const dropdownOpen = ref(false)
  let pollHandle = null
  let previousUnread = 0

  const authStore = useAuthStore()

  const fetchAll = async () => {
    if (!authStore.user) return
    loading.value = true
    try {
      const userId = authStore.user.id
      const { data, error } = await supabase
        .from('notifications')
        .select('*')
        .eq('user_id', userId)
        .order('created_at', { ascending: false })
        .limit(50)
      if (!error && data) items.value = data
    } catch (e) { /* silent */ }
    finally { loading.value = false }
  }

  const fetchUnread = async () => {
    if (!authStore.user) return
    try {
      const userId = authStore.user.id
      const { count, error } = await supabase
        .from('notifications')
        .select('*', { count: 'exact', head: true })
        .eq('user_id', userId)
        .eq('is_read', false)
      if (!error) {
        const newCount = count || 0
        if (newCount > previousUnread) {
          playNotificationSound()
        }
        previousUnread = newCount
        unread.value = newCount
      }
    } catch (e) { unread.value = 0 }
  }

  const markRead = async (id = null) => {
    if (!authStore.user) return
    try {
      const userId = authStore.user.id
      if (id) {
        await supabase.from('notifications').update({ is_read: true }).eq('id', id).eq('user_id', userId)
      } else {
        await supabase.from('notifications').update({ is_read: true }).eq('user_id', userId).eq('is_read', false)
      }
      await fetchAll()
      await fetchUnread()
    } catch (e) { /* silent */ }
  }

  const toggleDropdown = async () => {
    dropdownOpen.value = !dropdownOpen.value
    if (dropdownOpen.value) await fetchAll()
  }

  const closeDropdown = () => { dropdownOpen.value = false }

  const startPolling = (intervalMs = 15000) => {
    stopPolling()
    fetchUnread()
    fetchAll()
    pollHandle = setInterval(async () => {
      await fetchUnread()
    }, intervalMs)
  }

  const stopPolling = () => {
    if (pollHandle) {
      clearInterval(pollHandle)
      pollHandle = null
    }
  }

  const reset = () => {
    items.value = []
    unread.value = 0
    stopPolling()
  }

  return {
    items, unread, loading, dropdownOpen,
    fetchAll, fetchUnread, markRead,
    toggleDropdown, closeDropdown,
    startPolling, stopPolling, reset,
  }
})
