import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

export const useChatStore = defineStore('chat', () => {
  const conversations = ref([])
  const availableUsers = ref([])
  const messages = ref([])
  const activeUserId = ref(null)
  const loading = ref(false)
  const sending = ref(false)
  const totalUnread = ref(0)
  let pollHandle = null

  const authStore = useAuthStore()

  const fetchAvailableUsers = async () => {
    try {
      const myId = authStore.user?.id
      const myRole = authStore.user?.role
      if (!myId) return

      let query = supabase
        .from('users')
        .select('id, name, email, photo, role')
        .neq('id', myId)

      if (myRole === 'admin') {
        query = query.in('role', ['funcionario', 'cliente'])
      } else if (myRole === 'funcionario') {
        query = query.in('role', ['admin', 'cliente'])
      } else {
        query = query.in('role', ['admin', 'funcionario'])
      }

      const { data, error } = await query.order('name', { ascending: true })
      if (!error) availableUsers.value = data || []
    } catch (e) {
      availableUsers.value = []
    }
  }

  const findAdmin = async () => {
    try {
      const { data, error } = await supabase
        .from('users')
        .select('id, name, email, photo, role')
        .eq('role', 'admin')
        .limit(1)
        .single()
      if (!error && data) return data
      return null
    } catch (e) {
      return null
    }
  }

  const fetchConversations = async () => {
    try {
      const userId = authStore.user?.id
      if (!userId) return

      const { data: sentMsgs, error: e1 } = await supabase
        .from('chat_messages')
        .select('id, sender_id, receiver_id, message, is_read, created_at')
        .eq('sender_id', userId)
        .order('created_at', { ascending: false })

      const { data: receivedMsgs, error: e2 } = await supabase
        .from('chat_messages')
        .select('id, sender_id, receiver_id, message, is_read, created_at')
        .eq('receiver_id', userId)
        .order('created_at', { ascending: false })

      if (e1 && e2) return

      const allMsgs = [...(sentMsgs || []), ...(receivedMsgs || [])]
      allMsgs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

      const convMap = {}
      const otherUserIds = new Set()

      for (const msg of allMsgs) {
        const otherId = msg.sender_id === userId ? msg.receiver_id : msg.sender_id
        if (!otherId) continue
        if (!convMap[otherId]) {
          otherUserIds.add(otherId)
          convMap[otherId] = {
            id: otherId,
            last_message: msg.message,
            last_at: msg.created_at,
            unread: 0,
            name: '',
            email: '',
            photo: null,
            role: 'cliente'
          }
        }
        if (msg.receiver_id === userId && !msg.is_read) {
          convMap[otherId].unread++
        }
      }

      if (otherUserIds.size > 0) {
        const { data: users } = await supabase
          .from('users')
          .select('id, name, email, photo, role')
          .in('id', Array.from(otherUserIds))

        if (users) {
          for (const u of users) {
            if (convMap[u.id]) {
              convMap[u.id].name = u.name
              convMap[u.id].email = u.email
              convMap[u.id].photo = u.photo
              convMap[u.id].role = u.role
            }
          }
        }
      }

      conversations.value = Object.values(convMap)
      totalUnread.value = conversations.value.reduce((s, c) => s + (parseInt(c.unread) || 0), 0)
    } catch (e) {
      // silent
    }
  }

  const fetchMessages = async (userId) => {
    activeUserId.value = userId
    loading.value = true
    try {
      const myId = authStore.user?.id
      if (!myId || !userId) { messages.value = []; return }

      const { data: sentMsgs } = await supabase
        .from('chat_messages')
        .select('*')
        .eq('sender_id', myId)
        .eq('receiver_id', userId)
        .order('created_at', { ascending: true })

      const { data: sentNoReceiver } = await supabase
        .from('chat_messages')
        .select('*')
        .eq('sender_id', myId)
        .is('receiver_id', null)
        .order('created_at', { ascending: true })

      const { data: receivedMsgs } = await supabase
        .from('chat_messages')
        .select('*')
        .eq('sender_id', userId)
        .or(`receiver_id.eq.${myId},receiver_id.is.null`)
        .order('created_at', { ascending: true })

      const all = [...(sentMsgs || []), ...(sentNoReceiver || []), ...(receivedMsgs || [])]
      all.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

      const seen = new Set()
      const unique = all.filter(m => {
        if (seen.has(m.id)) return false
        seen.add(m.id)
        return true
      })

      messages.value = unique
      markMessagesAsRead(userId)
    } catch (e) {
      messages.value = []
    } finally {
      loading.value = false
    }
  }

  const markMessagesAsRead = async (senderId) => {
    try {
      const myId = authStore.user?.id
      if (!myId || !senderId) return
      await supabase
        .from('chat_messages')
        .update({ is_read: true })
        .eq('sender_id', senderId)
        .eq('receiver_id', myId)
        .eq('is_read', false)
      await supabase
        .from('notifications')
        .update({ is_read: true })
        .eq('user_id', myId)
        .eq('is_read', false)
        .in('type', ['chat', 'message'])
      try {
        const { useNotificationStore } = await import('@/stores/notificationStore')
        const notifStore = useNotificationStore()
        await notifStore.fetchUnread()
      } catch {}
    } catch (e) {}
  }

  const sendMessage = async (message, receiverId = null) => {
    const text = (message || '').trim()
    if (!text) return { success: false, error: 'Mensagem vazia' }
    sending.value = true
    try {
      const myId = authStore.user?.id
      if (!myId) return { success: false, error: 'Sessao invalida' }

      const payload = {
        sender_id: myId,
        message: text,
        is_read: false
      }
      if (receiverId) payload.receiver_id = receiverId

      const { error } = await supabase.from('chat_messages').insert(payload)
      if (error) throw error

      if (receiverId) {
        const senderName = authStore.user?.name || authStore.user?.email || 'Utilizador'
        const notifPayload = {
          user_id: receiverId,
          title: 'Nova mensagem',
          body: `${senderName}: ${text.substring(0, 100)}`,
          type: 'chat',
          is_read: false,
          link: '/mensagens',
          icon: 'bi-chat-dots-fill'
        }
        const { error: notifError } = await supabase.from('notifications').insert(notifPayload)
        if (notifError) {
          console.warn('Erro ao criar notificação:', notifError.message)
        }
      }

      await fetchMessages(receiverId || activeUserId.value)
      await fetchConversations()
      return { success: true }
    } catch (e) {
      return { success: false, error: e.message }
    } finally {
      sending.value = false
    }
  }

  const sendFileMessage = async (file, receiverId) => {
    sending.value = true
    try {
      const myId = authStore.user?.id
      if (!myId) return { success: false, error: 'Sessao invalida' }
      if (!receiverId) return { success: false, error: 'Destinatário inválido' }

      let fileUrl = null
      const ext = (file.name.split('.').pop() || 'bin').toLowerCase()
      const path = `chat/${myId}/${Date.now()}_${Math.random().toString(36).slice(2, 8)}.${ext}`
      const opts = { cacheControl: '3600', upsert: false, contentType: file.type || undefined }

      const buckets = ['uploads', 'chat-files']
      for (const bucket of buckets) {
        const { error: upErr } = await supabase.storage.from(bucket).upload(path, file, opts)
        if (!upErr) {
          const { data } = supabase.storage.from(bucket).getPublicUrl(path)
          fileUrl = data?.publicUrl
          break
        }
      }

      if (!fileUrl && file.size <= 2097152) {
        const b64 = await new Promise((resolve, reject) => {
          const reader = new FileReader()
          reader.onload = () => resolve(reader.result)
          reader.onerror = reject
          reader.readAsDataURL(file)
        })
        fileUrl = b64
      }

      if (!fileUrl) return { success: false, error: 'Não foi possível enviar o ficheiro' }

      const fileMeta = JSON.stringify({
        url: fileUrl,
        name: file.name,
        type: file.type,
        size: file.size
      })

      return await sendMessage(`[FILE]${fileMeta}`, receiverId)
    } catch (e) {
      return { success: false, error: e.message }
    } finally {
      sending.value = false
    }
  }

  const refreshUnread = async () => {
    await fetchConversations()
  }

  const deleteConversation = async (otherUserId) => {
    try {
      const myId = authStore.user?.id
      if (!myId || !otherUserId) return { success: false }

      await supabase
        .from('chat_messages')
        .delete()
        .eq('sender_id', myId)
        .eq('receiver_id', otherUserId)

      await supabase
        .from('chat_messages')
        .delete()
        .eq('sender_id', otherUserId)
        .eq('receiver_id', myId)

      if (activeUserId.value === otherUserId) {
        messages.value = []
        activeUserId.value = null
      }

      await fetchConversations()
      return { success: true }
    } catch (e) {
      return { success: false, error: e.message }
    }
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
    availableUsers,
    messages,
    activeUserId,
    loading,
    sending,
    totalUnread,
    fetchAvailableUsers,
    findAdmin,
    fetchConversations,
    fetchMessages,
    sendMessage,
    sendFileMessage,
    deleteConversation,
    refreshUnread,
    startPolling,
    stopPolling,
    reset,
  }
})
