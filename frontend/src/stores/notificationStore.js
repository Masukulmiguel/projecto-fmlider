import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

export const useNotificationStore = defineStore('notifications', () => {
  const items = ref([])
  const unread = ref(0)
  const loading = ref(false)
  const dropdownOpen = ref(false)
  let pollHandle = null

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
      if (!error) unread.value = count || 0
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
    pollHandle = setInterval(fetchUnread, intervalMs)
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
