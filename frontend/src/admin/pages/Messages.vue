<template>
  <div class="admin-messages-page">
    <div class="page-header">
      <div>
        <h1 class="page-title"><i class="bi bi-chat-dots-fill me-2"></i>Mensagens</h1>
        <p class="text-muted mb-0">Converse diretamente com os seus clientes.</p>
      </div>
    </div>
    <ChatPanel
      :selected="selected"
      :show-sidebar="true"
      @select="onSelect"
      @back="selected = null"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import ChatPanel from '@/components/ChatPanel.vue'
import { supabase } from '@/lib/supabase'

const selected = ref(null)
const conversations = ref([])
const messages = ref([])
let pollingInterval = null

const fetchConversations = async () => {
  const { data, error } = await supabase.from('chat_messages').select('conversation_id, sender_name, message, created_at').order('created_at', { ascending: false })
  if (!error && data) {
    const convMap = {}
    data.forEach(msg => {
      if (!convMap[msg.conversation_id]) {
        convMap[msg.conversation_id] = {
          id: msg.conversation_id,
          lastMessage: msg.message,
          lastTime: msg.created_at,
          sender_name: msg.sender_name
        }
      }
    })
    conversations.value = Object.values(convMap)
  }
}

const fetchMessages = async (conversationId) => {
  const { data, error } = await supabase.from('chat_messages').select('*').eq('conversation_id', conversationId).order('created_at', { ascending: true })
  if (!error) messages.value = data
}

const onSelect = async (conv) => {
  selected.value = conv
  await fetchMessages(conv.id)
}

const startPolling = (interval = 5000) => {
  stopPolling()
  pollingInterval = setInterval(fetchConversations, interval)
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

onMounted(async () => {
  await fetchConversations()
  if (conversations.value.length > 0 && !selected.value) {
    selected.value = conversations.value[0]
    await fetchMessages(selected.value.id)
  }
  startPolling(5000)
})

onBeforeUnmount(() => {
  stopPolling()
})

watch(() => conversations.value.length, (n) => {
  if (n > 0 && !selected.value) {
    selected.value = conversations.value[0]
    fetchMessages(selected.value.id)
  }
})
</script>

<style scoped>
.admin-messages-page { padding: 1.5rem; }
.page-header { margin-bottom: 1rem; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
</style>
