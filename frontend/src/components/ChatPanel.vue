<template>
  <div class="messenger-panel">
    <!-- SIDEBAR -->
    <div v-if="showSidebar" class="messenger-sidebar">
      <div class="sidebar-header">
        <h5 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>Conversas</h5>
        <button class="new-chat-btn" @click="showNewChat = !showNewChat" title="Nova conversa">
          <i class="bi bi-pencil-square"></i>
        </button>
      </div>

      <!-- New Chat Panel -->
      <div v-if="showNewChat" class="new-chat-panel">
        <div class="new-chat-search">
          <i class="bi bi-search"></i>
          <input v-model="newChatSearch" type="text" placeholder="Pesquisar utilizador..." autofocus />
          <button class="back-btn" @click="showNewChat = false"><i class="bi bi-arrow-left"></i></button>
        </div>
        <div class="conv-list">
          <div v-if="filteredAvailable.length === 0" class="empty-state">
            <p class="mb-0">Nenhum utilizador encontrado</p>
          </div>
          <div v-for="u in filteredAvailable" :key="u.id" class="conv-item" @click="startNewChat(u)">
            <div class="avatar-circle" :style="avatarStyle(u.name)">
              <img v-if="u.photo" :src="u.photo" :alt="u.name" />
              <span v-else>{{ initials(u.name) }}</span>
            </div>
            <div class="conv-info">
              <div class="conv-name-row">
                <span class="conv-name">{{ u.name }}</span>
                <span class="role-badge" :class="u.role === 'funcionario' ? 'badge-func' : 'badge-client'">
                  {{ u.role === 'funcionario' ? 'Funcionário' : 'Cliente' }}
                </span>
              </div>
              <span class="conv-email">{{ u.email }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Conversation List -->
      <div v-if="!showNewChat">
        <div class="sidebar-search">
          <i class="bi bi-search"></i>
          <input v-model="search" type="text" placeholder="Pesquisar conversas..." />
        </div>
        <div class="conv-list">
          <div v-if="filtered.length === 0" class="empty-state">
            <i class="bi bi-inbox"></i>
            <p class="mb-0">Sem conversas</p>
          </div>
          <div
            v-for="c in filtered"
            :key="c.id"
            class="conv-item"
            :class="{ active: chatStore.activeUserId === c.id, unread: (parseInt(c.unread) || 0) > 0 }"
            @click="$emit('select', c)"
          >
            <div class="avatar-circle" :style="avatarStyle(c.name)">
              <img v-if="c.photo" :src="c.photo" :alt="c.name" />
              <span v-else>{{ initials(c.name) }}</span>
              <span v-if="c.online" class="online-dot"></span>
            </div>
            <div class="conv-info">
              <div class="conv-name-row">
                <span class="conv-name">{{ c.name }}</span>
                <span class="conv-time">{{ formatTime(c.last_at) }}</span>
              </div>
              <div class="conv-preview-row">
                <span class="conv-last" :class="{ 'font-bold': (parseInt(c.unread) || 0) > 0 }">
                  {{ c.last_message || 'Sem mensagens' }}
                </span>
                <span v-if="(parseInt(c.unread) || 0) > 0" class="unread-badge">{{ c.unread }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN CHAT -->
    <div class="messenger-main">
      <!-- Header -->
      <div v-if="selected" class="messenger-header">
        <div class="header-left">
          <button class="back-mobile" @click="$emit('back')">
            <i class="bi bi-arrow-left"></i>
          </button>
          <div class="avatar-circle sm" :style="avatarStyle(selected.name)">
            <img v-if="selected.photo" :src="selected.photo" :alt="selected.name" />
            <span v-else>{{ initials(selected.name) }}</span>
          </div>
          <div class="header-info">
            <h6 class="mb-0">{{ selected.name }}</h6>
            <span class="header-status">
              <span class="status-dot"></span> Online
            </span>
          </div>
        </div>
        <div class="header-actions">
          <button class="header-action-btn" title="Ligar"><i class="bi bi-telephone"></i></button>
          <button class="header-action-btn" title="Videochamada"><i class="bi bi-camera-video"></i></button>
          <button class="header-action-btn" title="Mais opções"><i class="bi bi-three-dots"></i></button>
        </div>
      </div>
      <div v-else class="messenger-header placeholder-header">
        <div class="text-muted">
          <i class="bi bi-chat-square-text me-2"></i>Selecione uma conversa para começar
        </div>
      </div>

      <!-- Messages Area -->
      <div class="messages-area" ref="messagesRef">
        <!-- Loading -->
        <div v-if="chatStore.loading && chatStore.messages.length === 0" class="messages-empty">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">A carregar mensagens...</p>
        </div>
        <!-- Empty -->
        <div v-else-if="chatStore.messages.length === 0" class="messages-empty">
          <div class="empty-icon">
            <i class="bi bi-chat-dots"></i>
          </div>
          <h5>Sem mensagens ainda</h5>
          <p>Envie a primeira mensagem para iniciar a conversa</p>
        </div>
        <!-- Messages -->
        <template v-else>
          <div class="date-divider" v-if="chatStore.messages.length > 0">
            <span>{{ formatDateDivider(chatStore.messages[0]?.created_at) }}</span>
          </div>
          <div
            v-for="(m, i) in chatStore.messages"
            :key="m.id || i"
            class="msg-row"
            :class="isMine(m) ? 'mine' : 'theirs'"
          >
            <div v-if="!isMine(m)" class="msg-avatar" :style="avatarStyle(selected?.name)">
              <img v-if="selected?.photo" :src="selected.photo" :alt="selected.name" />
              <span v-else>{{ initials(selected?.name) }}</span>
            </div>
            <div class="msg-content">
              <div class="msg-bubble" :class="isMine(m) ? 'bubble-mine' : 'bubble-theirs'">
                <div class="msg-text">{{ m.message }}</div>
              </div>
              <div class="msg-meta" :class="isMine(m) ? 'meta-mine' : 'meta-theirs'">
                <span class="msg-time">{{ formatTime(m.created_at) }}</span>
                <i v-if="isMine(m)" class="bi check-icon" :class="m.is_read ? 'bi-check2-all read' : 'bi-check2'"></i>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Input Area -->
      <div v-if="selected" class="input-area">
        <div class="input-wrapper">
          <button class="input-action" title="Anexar"><i class="bi bi-plus-circle"></i></button>
          <textarea
            v-model="input"
            rows="1"
            placeholder="Escreva uma mensagem..."
            :disabled="chatStore.sending"
            @keydown.enter.exact.prevent="onSend"
            @input="autoResize"
            ref="textareaRef"
          ></textarea>
          <button class="input-action" title="Emoji"><i class="bi bi-emoji-smile"></i></button>
          <button
            class="send-btn"
            :class="{ active: input.trim() }"
            @click="onSend"
            :disabled="!input.trim() || chatStore.sending"
          >
            <i class="bi" :class="input.trim() ? 'bi-send-fill' : 'bi-mic'"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { useChatStore } from '@/stores/chatStore'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'

const { t, locale } = useI18n()

const props = defineProps({
  selected: { type: Object, default: null },
  showSidebar: { type: Boolean, default: true },
})
const emit = defineEmits(['select', 'back', 'sent'])

const chatStore = useChatStore()
const authStore = useAuthStore()
const input = ref('')
const search = ref('')
const messagesRef = ref(null)
const textareaRef = ref(null)
const showNewChat = ref(false)
const newChatSearch = ref('')

const avatarColors = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
  'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
  'linear-gradient(135deg, #fccb90 0%, #d57eeb 100%)',
  'linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%)',
]

const avatarStyle = (name) => {
  if (!name) return {}
  const idx = name.split('').reduce((a, c) => a + c.charCodeAt(0), 0) % avatarColors.length
  return { background: avatarColors[idx] }
}

const filteredAvailable = computed(() => {
  const q = newChatSearch.value.trim().toLowerCase()
  let users = chatStore.availableUsers || []
  if (q) {
    users = users.filter(u =>
      (u.name || '').toLowerCase().includes(q) ||
      (u.email || '').toLowerCase().includes(q)
    )
  }
  return users
})

const startNewChat = async (user) => {
  showNewChat.value = false
  newChatSearch.value = ''
  emit('select', user)
  await chatStore.fetchMessages(user.id)
}

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
  const loc = locale.value === 'fr' ? 'fr-FR' : locale.value === 'en' ? 'en-US' : 'pt-PT'
  if (sameDay) return d.toLocaleTimeString(loc, { hour: '2-digit', minute: '2-digit' })
  const diff = (now - d) / 1000
  if (diff < 7 * 86400) return d.toLocaleDateString(loc, { weekday: 'short' })
  return d.toLocaleDateString(loc, { day: '2-digit', month: '2-digit' })
}

const formatDateDivider = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const loc = locale.value === 'fr' ? 'fr-FR' : locale.value === 'en' ? 'en-US' : 'pt-PT'
  if (d.toDateString() === now.toDateString()) return 'Hoje'
  const yesterday = new Date(now)
  yesterday.setDate(yesterday.getDate() - 1)
  if (d.toDateString() === yesterday.toDateString()) return 'Ontem'
  return d.toLocaleDateString(loc, { day: 'numeric', month: 'long', year: 'numeric' })
}

const scrollDown = async () => {
  await nextTick()
  if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
}

const autoResize = () => {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

const onSend = async () => {
  if (!input.value.trim() || !props.selected) return
  const text = input.value
  input.value = ''
  if (textareaRef.value) textareaRef.value.style.height = 'auto'
  const res = await chatStore.sendMessage(text, props.selected.id)
  if (!res.success) {
    input.value = text
  } else {
    emit('sent')
  }
  await scrollDown()
}

watch(() => chatStore.messages.length, scrollDown)
</script>

<style scoped>
.messenger-panel {
  display: flex;
  height: calc(100vh - 140px);
  min-height: 550px;
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
}

/* ── SIDEBAR ── */
.messenger-sidebar {
  width: 340px;
  border-right: 1px solid #e4e6eb;
  display: flex;
  flex-direction: column;
  background: #fff;
  flex-shrink: 0;
}

.sidebar-header {
  padding: 16px 16px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e4e6eb;
}
.sidebar-header h5 { font-weight: 700; font-size: 1.3rem; color: #050505; }

.new-chat-btn {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: none;
  background: #e4e6eb;
  color: #050505;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s;
}
.new-chat-btn:hover { background: #d8dadf; }

.sidebar-search {
  position: relative;
  padding: 8px 16px;
}
.sidebar-search i {
  position: absolute; left: 28px; top: 50%; transform: translateY(-50%);
  color: #65676b; font-size: 0.85rem;
}
.sidebar-search input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border: none;
  border-radius: 20px;
  font-size: 0.9rem;
  outline: none;
  background: #f0f2f5;
  color: #050505;
}
.sidebar-search input:focus { background: #fff; box-shadow: 0 0 0 2px #0084ff; }
.sidebar-search input::placeholder { color: #65676b; }

.conv-list { flex: 1; overflow-y: auto; }
.conv-list::-webkit-scrollbar { width: 6px; }
.conv-list::-webkit-scrollbar-thumb { background: #bcc0c4; border-radius: 3px; }

.empty-state { text-align: center; color: #65676b; padding: 3rem 1rem; }
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.5; }

.conv-item {
  display: flex; gap: 12px; padding: 10px 16px;
  cursor: pointer; align-items: center;
  transition: background 0.15s;
}
.conv-item:hover { background: #f2f3f5; }
.conv-item.active { background: #e7f3ff; }

/* ── AVATAR ── */
.avatar-circle {
  width: 48px; height: 48px; border-radius: 50%;
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.95rem;
  flex-shrink: 0; overflow: hidden; position: relative;
}
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.avatar-circle.sm { width: 36px; height: 36px; font-size: 0.75rem; }

.online-dot {
  position: absolute; bottom: 1px; right: 1px;
  width: 12px; height: 12px; border-radius: 50%;
  background: #31a24c; border: 2px solid #fff;
}

.conv-info { flex: 1; min-width: 0; }
.conv-name-row { display: flex; justify-content: space-between; align-items: baseline; }
.conv-name { font-size: 0.95rem; font-weight: 600; color: #050505; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-time { color: #65676b; font-size: 0.72rem; flex-shrink: 0; margin-left: 8px; }
.conv-preview-row { display: flex; justify-content: space-between; align-items: center; margin-top: 2px; }
.conv-last { color: #65676b; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
.conv-last.font-bold { color: #050505; font-weight: 600; }
.conv-email { color: #65676b; font-size: 0.75rem; display: block; margin-top: 1px; }

.unread-badge {
  background: #0084ff; color: #fff;
  font-size: 0.7rem; font-weight: 700;
  min-width: 20px; height: 20px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; margin-left: 8px;
}

.role-badge {
  font-size: 0.6rem; font-weight: 700;
  padding: 2px 8px; border-radius: 10px;
  text-transform: uppercase; letter-spacing: 0.3px;
  flex-shrink: 0; margin-left: 6px;
}
.badge-func { background: #e6f7f5; color: #0f766e; }
.badge-client { background: #f3e8ff; color: #7c3aed; }

/* ── MAIN CHAT ── */
.messenger-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }

.messenger-header {
  padding: 10px 16px;
  border-bottom: 1px solid #e4e6eb;
  display: flex; align-items: center; justify-content: space-between;
  background: #fff;
  min-height: 60px;
}
.placeholder-header { color: #65676b; font-size: 0.95rem; }

.header-left { display: flex; align-items: center; gap: 10px; }
.header-info h6 { font-weight: 700; font-size: 0.95rem; color: #050505; }
.header-status { font-size: 0.75rem; color: #65676b; display: flex; align-items: center; gap: 4px; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #31a24c; display: inline-block; }

.header-actions { display: flex; gap: 4px; }
.header-action-btn {
  width: 36px; height: 36px; border-radius: 50%; border: none;
  background: transparent; color: #0084ff;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 1.1rem; transition: background 0.2s;
}
.header-action-btn:hover { background: #f0f2f5; }

.back-mobile { display: none; background: none; border: none; font-size: 1.2rem; color: #0084ff; cursor: pointer; padding: 4px; }

/* ── MESSAGES ── */
.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f0f2f5;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.messages-area::-webkit-scrollbar { width: 6px; }
.messages-area::-webkit-scrollbar-thumb { background: #bcc0c4; border-radius: 3px; }

.messages-empty {
  text-align: center; color: #65676b;
  margin: auto; padding: 2rem;
}
.messages-empty .empty-icon {
  width: 80px; height: 80px; border-radius: 50%;
  background: #e4e6eb; display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
}
.messages-empty .empty-icon i { font-size: 2.5rem; color: #65676b; }
.messages-empty h5 { color: #050505; font-weight: 700; }
.messages-empty p { color: #65676b; font-size: 0.9rem; }

.date-divider {
  text-align: center; margin: 12px 0;
}
.date-divider span {
  background: #e4e6eb; color: #65676b;
  font-size: 0.75rem; font-weight: 600;
  padding: 4px 12px; border-radius: 12px;
}

.msg-row { display: flex; gap: 8px; max-width: 70%; margin-bottom: 2px; }
.msg-row.mine { align-self: flex-end; flex-direction: row-reverse; }
.msg-row.theirs { align-self: flex-start; }

.msg-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 0.6rem; font-weight: 700;
  flex-shrink: 0; overflow: hidden; align-self: flex-end;
}
.msg-avatar img { width: 100%; height: 100%; object-fit: cover; }

.msg-content { display: flex; flex-direction: column; }

.msg-bubble {
  padding: 8px 14px;
  border-radius: 18px;
  word-wrap: break-word;
  max-width: 100%;
  line-height: 1.35;
}
.bubble-mine {
  background: #0084ff;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.bubble-theirs {
  background: #fff;
  color: #050505;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
}

.msg-text { font-size: 0.93rem; white-space: pre-wrap; }

.msg-meta {
  display: flex; align-items: center; gap: 4px;
  margin-top: 2px; padding: 0 4px;
}
.meta-mine { justify-content: flex-end; }
.meta-theirs { justify-content: flex-start; }
.msg-time { font-size: 0.68rem; color: #65676b; }
.check-icon { font-size: 0.8rem; }
.check-icon.read { color: #0084ff; }

/* ── INPUT ── */
.input-area {
  padding: 10px 16px;
  background: #fff;
  border-top: 1px solid #e4e6eb;
}

.input-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  background: #f0f2f5;
  border-radius: 20px;
  padding: 6px 8px 6px 12px;
}

.input-action {
  width: 32px; height: 32px; border-radius: 50%; border: none;
  background: transparent; color: #0084ff;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 1.2rem; flex-shrink: 0;
  transition: background 0.2s;
}
.input-action:hover { background: #e4e6eb; }

.input-wrapper textarea {
  flex: 1;
  border: none;
  background: transparent;
  resize: none;
  font-size: 0.93rem;
  font-family: inherit;
  outline: none;
  max-height: 120px;
  padding: 6px 0;
  line-height: 1.35;
  color: #050505;
}
.input-wrapper textarea::placeholder { color: #65676b; }
.input-wrapper textarea:disabled { opacity: 0.6; }

.send-btn {
  width: 32px; height: 32px; border-radius: 50%; border: none;
  background: transparent; color: #65676b;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 1.1rem; flex-shrink: 0;
  transition: all 0.2s;
}
.send-btn.active { color: #0084ff; }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.send-btn:not(:disabled):hover { background: #e4e6eb; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .messenger-panel { border-radius: 0; height: calc(100vh - 60px); }
  .messenger-sidebar { width: 100%; position: absolute; inset: 0; z-index: 10; }
  .messenger-main { width: 100%; }
  .back-mobile { display: flex; }
  .header-action-btn:not(:last-child) { display: none; }
  .msg-row { max-width: 85%; }
}
</style>
