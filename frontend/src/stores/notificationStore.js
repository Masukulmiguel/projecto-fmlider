import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

export const useNotificationStore = defineStore('notifications', () => {
  const items = ref([])
  const unread = ref(0)
  const loading = ref(false)
  const dropdownOpen = ref(false)
  let pollHandle = null

  const authStore = useAuthStore()
  const headers = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

  const fetchAll = async () => {
    if (!authStore.token) return
    loading.value = true
    try {
      const r = await axios.get('/api/notifications', { ...headers(), params: {} })
      if (r.data.success) items.value = r.data.data.notifications || []
    } catch (e) { /* silent */ }
    finally { loading.value = false }
  }

  const fetchUnread = async () => {
    if (!authStore.token) return
    try {
      const r = await axios.get('/api/notifications/unread-count', headers())
      if (r.data.success) unread.value = parseInt(r.data.data.count) || 0
    } catch (e) { unread.value = 0 }
  }

  const markRead = async (id = null) => {
    try {
      if (id) {
        await axios.post(`/api/notifications/${id}/read`, {}, headers())
      } else {
        await axios.post('/api/notifications/mark-read', { all: true }, headers())
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
