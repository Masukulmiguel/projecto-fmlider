import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

export const useChatStore = defineStore('chat', () => {
  const conversations = ref([])
  const messages = ref([])
  const activeUserId = ref(null)
  const loading = ref(false)
  const sending = ref(false)
  const totalUnread = ref(0)
  let pollHandle = null

  const authStore = useAuthStore()
  const headers = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

  const fetchConversations = async () => {
    try {
      const r = await axios.get('/api/chat/conversations', headers())
      if (r.data.success) {
        conversations.value = r.data.data.conversations || []
        totalUnread.value = conversations.value.reduce((s, c) => s + (parseInt(c.unread) || 0), 0)
      }
    } catch (e) {
      // silent
    }
  }

  const fetchMessages = async (userId) => {
    activeUserId.value = userId
    loading.value = true
    try {
      const r = await axios.get('/api/chat/messages', {
        ...headers(),
        params: userId ? { user_id: userId } : {}
      })
      if (r.data.success) {
        messages.value = r.data.data.messages || []
      }
    } catch (e) {
      messages.value = []
    } finally {
      loading.value = false
    }
  }

  const sendMessage = async (message, receiverId = null) => {
    const text = (message || '').trim()
    if (!text) return { success: false, error: 'Mensagem vazia' }
    sending.value = true
    try {
      const payload = { message: text }
      if (receiverId) payload.receiver_id = receiverId
      const r = await axios.post('/api/chat/send', payload, headers())
      if (r.data.success) {
        await fetchMessages(receiverId || activeUserId.value)
        await fetchConversations()
        return { success: true }
      }
      return { success: false, error: r.data.message }
    } catch (e) {
      return { success: false, error: e.response?.data?.message || e.message }
    } finally {
      sending.value = false
    }
  }

  const refreshUnread = async () => {
    await fetchConversations()
  }

  const startPolling = (intervalMs = 5000) => {
    stopPolling()
    pollHandle = setInterval(async () => {
      await fetchConversations()
      if (activeUserId.value !== null) {
        await fetchMessages(activeUserId.value)
      }
    }, intervalMs)
  }

  const stopPolling = () => {
    if (pollHandle) {
      clearInterval(pollHandle)
      pollHandle = null
    }
  }

  const reset = () => {
    conversations.value = []
    messages.value = []
    activeUserId.value = null
    totalUnread.value = 0
    stopPolling()
  }

  return {
    conversations,
    messages,
    activeUserId,
    loading,
    sending,
    totalUnread,
    fetchConversations,
    fetchMessages,
    sendMessage,
    refreshUnread,
    startPolling,
    stopPolling,
    reset,
  }
})
