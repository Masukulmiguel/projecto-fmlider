<template>
  <div class="messenger-page">
    <div class="page-header">
      <div>
        <h1 class="page-title"><i class="bi bi-chat-dots-fill me-2"></i>Mensagens</h1>
        <p class="text-muted mb-0">Comunique directamente com a administração</p>
      </div>
    </div>

    <div v-if="!adminConv" class="empty-chat-card">
      <div class="empty-chat-inner">
        <div class="empty-icon-wrap">
          <i class="bi bi-headset"></i>
        </div>
        <h4>Fale com a Administração</h4>
        <p>Inicie uma conversa para tirar dúvidas, enviar pedidos ou acompanhar processos.</p>
        <button class="btn-start-chat" @click="startChat">
          <i class="bi bi-chat-dots-fill me-2"></i>Iniciar Conversa
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
  const admin = await chatStore.findAdmin()
  if (admin) {
    adminConv.value = admin
    await chatStore.fetchMessages(admin.id)
    chatStore.startPolling(5000)
  }
}

const onSent = () => {}

onMounted(async () => {
  await chatStore.fetchConversations()
  if (chatStore.conversations.length > 0) {
    adminConv.value = chatStore.conversations[0]
    await chatStore.fetchMessages(adminConv.value.id)
    chatStore.startPolling(5000)
  } else {
    const admin = await chatStore.findAdmin()
    if (admin) {
      adminConv.value = admin
      await chatStore.fetchMessages(admin.id)
      chatStore.startPolling(5000)
    }
  }
})

onBeforeUnmount(() => { chatStore.stopPolling() })
</script>

<style scoped>
.messenger-page { padding: 1.5rem; }
.page-header { margin-bottom: 1rem; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.empty-chat-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}
.empty-chat-inner {
  text-align: center;
  padding: 4rem 2rem;
}
.empty-icon-wrap {
  width: 100px; height: 100px; border-radius: 50%;
  background: linear-gradient(135deg, #e7f3ff, #cce5ff);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.5rem;
}
.empty-icon-wrap i { font-size: 2.8rem; color: #0084ff; }
.empty-chat-inner h4 { color: #050505; font-weight: 700; margin-bottom: 0.5rem; }
.empty-chat-inner p { color: #65676b; font-size: 0.95rem; max-width: 400px; margin: 0 auto 1.5rem; }
.btn-start-chat {
  background: #0084ff; color: #fff; border: none;
  padding: 12px 28px; border-radius: 24px;
  font-size: 0.95rem; font-weight: 600;
  cursor: pointer; transition: background 0.2s;
  display: inline-flex; align-items: center;
}
.btn-start-chat:hover { background: #0073e6; }
</style>
