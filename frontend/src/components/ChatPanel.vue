<template>
  <div class="chat-panel">
    <div v-if="showSidebar" class="chat-sidebar">
      <div class="chat-sidebar-header">
        <h6 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>Conversas</h6>
        <span v-if="chatStore.totalUnread > 0" class="badge bg-primary ms-2">{{ chatStore.totalUnread }}</span>
      </div>
      <div class="chat-search">
        <i class="bi bi-search"></i>
        <input v-model="search" type="text" placeholder="Pesquisar..." />
      </div>
      <div class="chat-conversations">
        <div v-if="filtered.length === 0" class="empty-conversations">
          <i class="bi bi-inbox"></i>
          <p class="mb-0">Sem conversas.</p>
        </div>
        <div
          v-for="c in filtered"
          :key="c.id"
          class="conv-item"
          :class="{ active: chatStore.activeUserId === c.id, unread: (parseInt(c.unread) || 0) > 0 }"
          @click="$emit('select', c)"
        >
          <div class="conv-avatar">
            <img v-if="c.photo" :src="c.photo" :alt="c.name" />
            <span v-else>{{ initials(c.name) }}</span>
          </div>
          <div class="conv-body">
              <div class="conv-top">
              <strong class="conv-name">{{ c.name }}</strong>
              <span v-if="c.role === 'funcionario'" class="role-tag">Funcionário</span>
              <small class="conv-time">{{ formatTime(c.last_at) }}</small>
            </div>
            <div class="conv-preview">
              <span class="conv-last">{{ c.last_message || 'Sem mensagens...' }}</span>
              <span v-if="(parseInt(c.unread) || 0) > 0" class="conv-badge">{{ c.unread }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="chat-main">
      <div v-if="selected" class="chat-header">
        <div class="chat-header-info">
          <div class="conv-avatar lg">
            <img v-if="selected.photo" :src="selected.photo" :alt="selected.name" />
            <span v-else>{{ initials(selected.name) }}</span>
          </div>
          <div>
            <h6 class="mb-0">{{ selected.name }}</h6>
            <small class="text-muted">
              <i class="bi bi-envelope me-1"></i>{{ selected.email }}
            </small>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-secondary d-md-none" @click="$emit('back')">
          <i class="bi bi-arrow-left"></i>
        </button>
      </div>
      <div v-else class="chat-header placeholder">
        <div class="text-muted"><i class="bi bi-chat-dots me-2"></i>Selecione uma conversa</div>
      </div>

      <div class="chat-messages" ref="messagesRef">
        <div v-if="chatStore.loading && chatStore.messages.length === 0" class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm"></div>
          <p class="mb-0 mt-2 small">A carregar mensagens...</p>
        </div>
        <div v-else-if="chatStore.messages.length === 0" class="empty-chat">
          <i class="bi bi-chat-square-text"></i>
          <p class="mb-0">Sem mensagens. Comece a conversa!</p>
        </div>
        <template v-else>
          <div v-for="(m, i) in chatStore.messages" :key="m.id || i" class="msg" :class="isMine(m) ? 'mine' : 'theirs'">
            <div class="msg-bubble">
              <div class="msg-text">{{ m.message }}</div>
              <div class="msg-time">
                {{ formatTime(m.created_at) }}
                <i v-if="isMine(m)" class="bi" :class="m.is_read ? 'bi-check2-all' : 'bi-check2'"></i>
              </div>
            </div>
          </div>
        </template>
      </div>

      <form v-if="selected" class="chat-input" @submit.prevent="onSend">
        <textarea
          v-model="input"
          rows="1"
          placeholder="Escreve uma mensagem..."
          :disabled="chatStore.sending"
          @keydown.enter.exact.prevent="onSend"
        ></textarea>
        <button type="submit" class="send-btn" :disabled="!input.trim() || chatStore.sending">
          <i class="bi bi-send-fill"></i>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { useChatStore } from '@/stores/chatStore'
import { useAuthStore } from '@/stores/authStore'

const props = defineProps({
  selected: { type: Object, default: null },
  showSidebar: { type: Boolean, default: true },
})
defineEmits(['select', 'back', 'sent'])

const chatStore = useChatStore()
const authStore = useAuthStore()
const input = ref('')
const search = ref('')
const messagesRef = ref(null)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return chatStore.conversations
  return chatStore.conversations.filter(c =>
    (c.name || '').toLowerCase().includes(q) ||
    (c.email || '').toLowerCase().includes(q) ||
    (c.last_message || '').toLowerCase().includes(q)
  )
})

const isMine = (m) => {
  const myId = authStore.user?.id
  const myRole = authStore.user?.role
  if (myId == null) return false
  if (parseInt(m.sender_id) === parseInt(myId)) return true
  if (myRole === 'admin' && m.sender_id == null) return true
  return false
}

const initials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
}

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const sameDay = d.toDateString() === now.toDateString()
  if (sameDay) return d.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' })
  const diff = (now - d) / 1000
  if (diff < 7 * 86400) return d.toLocaleDateString('pt-PT', { weekday: 'short' })
  return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit' })
}

const scrollDown = async () => {
  await nextTick()
  if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
}

const onSend = async () => {
  if (!input.value.trim() || !props.selected) return
  const text = input.value
  input.value = ''
  const res = await chatStore.sendMessage(text, props.selected.id)
  if (!res.success) {
    input.value = text
  }
  await scrollDown()
}

watch(() => chatStore.messages.length, scrollDown)
</script>

<style scoped>
.chat-panel {
  display: flex;
  height: calc(100vh - 140px);
  min-height: 500px;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
  border: 1px solid #eef0f3;
}

.chat-sidebar {
  width: 320px;
  border-right: 1px solid #eef0f3;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  flex-shrink: 0;
}
.chat-sidebar-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #eef0f3;
  display: flex;
  align-items: center;
  background: #fff;
}
.chat-search {
  position: relative;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eef0f3;
  background: #fff;
}
.chat-search i { position: absolute; left: 1.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem; }
.chat-search input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  background: #f8fafc;
}
.chat-search input:focus { border-color: #2563eb; background: #fff; }

.chat-conversations { flex: 1; overflow-y: auto; }
.empty-conversations { text-align: center; color: #94a3b8; padding: 2rem 1rem; }
.empty-conversations i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

.conv-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
  align-items: center;
}
.conv-item:hover { background: #f1f5f9; }
.conv-item.active { background: #eff6ff; border-left: 3px solid #2563eb; padding-left: calc(1rem - 3px); }

.conv-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 600; font-size: 0.85rem;
  flex-shrink: 0; overflow: hidden;
}
.conv-avatar img { width: 100%; height: 100%; object-fit: cover; }
.conv-avatar.lg { width: 42px; height: 42px; }

.conv-body { flex: 1; min-width: 0; }
.conv-top { display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem; }
.conv-name { font-size: 0.9rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-time { color: #94a3b8; font-size: 0.7rem; flex-shrink: 0; }
.conv-preview { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; margin-top: 2px; }
.conv-last { color: #64748b; font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-badge {
  background: #2563eb; color: #fff; font-size: 0.7rem; font-weight: 700;
  padding: 2px 7px; border-radius: 10px; flex-shrink: 0;
}
.conv-item.unread .conv-name { color: #0f172a; }
.conv-item.unread .conv-last { color: #334155; font-weight: 500; }

.role-tag {
  display: inline-block;
  font-size: 0.62rem;
  font-weight: 700;
  background: #0f766e;
  color: #ccfbf1;
  padding: 1px 7px;
  border-radius: 8px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  flex-shrink: 0;
}

.chat-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.chat-header {
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid #eef0f3;
  display: flex; align-items: center; justify-content: space-between;
  background: #fff;
  min-height: 65px;
}
.chat-header.placeholder { color: #94a3b8; }
.chat-header-info { display: flex; align-items: center; gap: 0.75rem; }

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  background:
    radial-gradient(circle at 20% 20%, rgba(37,99,235,0.04), transparent 40%),
    radial-gradient(circle at 80% 80%, rgba(124,58,237,0.04), transparent 40%),
    #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

.empty-chat { text-align: center; color: #94a3b8; padding: 3rem 1rem; margin: auto; }
.empty-chat i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }

.msg { display: flex; }
.msg.mine { justify-content: flex-end; }
.msg.theirs { justify-content: flex-start; }

.msg-bubble {
  max-width: 70%;
  padding: 0.55rem 0.85rem;
  border-radius: 16px;
  word-wrap: break-word;
  position: relative;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.msg.mine .msg-bubble {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: #fff;
  border-bottom-right-radius: 4px;
}
.msg.theirs .msg-bubble {
  background: #fff;
  color: #0f172a;
  border-bottom-left-radius: 4px;
  border: 1px solid #eef0f3;
}
.msg-text { font-size: 0.9rem; line-height: 1.45; white-space: pre-wrap; }
.msg-time {
  font-size: 0.65rem;
  opacity: 0.7;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 3px;
  justify-content: flex-end;
}
.msg.mine .msg-time { color: rgba(255,255,255,0.85); }

.chat-input {
  display: flex;
  gap: 0.5rem;
  padding: 0.85rem 1rem;
  background: #fff;
  border-top: 1px solid #eef0f3;
  align-items: flex-end;
}
.chat-input textarea {
  flex: 1;
  resize: none;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.6rem 0.85rem;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  max-height: 120px;
  transition: border-color 0.2s;
}
.chat-input textarea:focus { border-color: #2563eb; }
.chat-input textarea:disabled { background: #f1f5f9; }
.send-btn {
  width: 42px; height: 42px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: #fff;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: opacity 0.2s, transform 0.2s;
}
.send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.send-btn:not(:disabled):hover { transform: scale(1.05); }

@media (max-width: 768px) {
  .chat-sidebar { width: 100%; }
  .chat-panel { height: calc(100vh - 110px); }
}
</style>
