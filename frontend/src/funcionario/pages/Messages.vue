<template>
  <div class="funcionario-messages">
    <div class="page-header">
      <div>
        <h1 class="page-title"><i class="bi bi-chat-dots-fill me-2"></i>Mensagens com a Administração</h1>
        <p class="text-muted mb-0">Comunica diretamente com a equipa de administração da FMLider.</p>
      </div>
    </div>

    <div v-if="!adminConv" class="card empty-card">
      <div class="card-body text-center py-5">
        <i class="bi bi-headset" style="font-size: 3rem; color: #0f766e;"></i>
        <h5 class="mt-3">Administração FMLider</h5>
        <p class="text-muted mb-3">Inicia uma conversa para falar com os administradores.</p>
        <button class="btn btn-success" @click="startChat">
          <i class="bi bi-chat-dots me-1"></i> Iniciar conversa
        </button>
      </div>
    </div>

    <ChatPanel v-else :selected="adminConv" :show-sidebar="false" @sent="onSent" />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import ChatPanel from '@/components/ChatPanel.vue'
import { useChatStore } from '@/stores/chatStore'

const chatStore = useChatStore()
const adminConv = ref(null)

const startChat = async () => {
  await chatStore.fetchConversations()
  adminConv.value = chatStore.conversations[0] || null
  if (adminConv.value) {
    await chatStore.fetchMessages(adminConv.value.id)
  }
  chatStore.startPolling(5000)
}

const onSent = () => { /* handled in store */ }

onMounted(async () => {
  await chatStore.fetchConversations()
  if (chatStore.conversations.length > 0) {
    adminConv.value = chatStore.conversations[0]
    await chatStore.fetchMessages(adminConv.value.id)
    chatStore.startPolling(5000)
  }
})

onBeforeUnmount(() => {
  chatStore.stopPolling()
})
</script>

<style scoped>
.funcionario-messages { padding: 1.5rem; }
.page-header { margin-bottom: 1rem; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.empty-card { border: none; border-radius: 14px; box-shadow: 0 4px 18px rgba(15,23,42,0.06); }
</style>
