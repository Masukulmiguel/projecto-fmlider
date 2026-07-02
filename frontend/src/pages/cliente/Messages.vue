<template>
  <div class="messenger-page">
    <div class="page-header">
      <div>
        <h1 class="page-title"><i class="bi bi-chat-dots-fill me-2"></i>Mensagens</h1>
        <p class="text-muted mb-0">Fale directamente com a nossa equipa</p>
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import ChatPanel from '@/components/ChatPanel.vue'
import { useChatStore } from '@/stores/chatStore'

const chatStore = useChatStore()
const selected = ref(null)

const onSelect = async (conv) => {
  selected.value = conv
  await chatStore.fetchMessages(conv.id)
  chatStore.startPolling(5000)
}

onMounted(async () => {
  await chatStore.fetchAvailableUsers()
  await chatStore.fetchConversations()
  if (chatStore.conversations.length > 0 && !selected.value) {
    selected.value = chatStore.conversations[0]
    await chatStore.fetchMessages(selected.value.id)
  }
  chatStore.startPolling(5000)
})

onBeforeUnmount(() => { chatStore.stopPolling() })
</script>

<style scoped>
.messenger-page { padding: 1.5rem; }
.page-header { margin-bottom: 1rem; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
</style>
