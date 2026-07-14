<template>
  <div class="messenger-panel">
    <!-- ═══ SIDEBAR ═══ -->
    <div v-if="showSidebar" class="messenger-sidebar" :class="{ 'sidebar-hidden': selected && isMobile }">
      <div class="sidebar-header">
        <h5 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>Conversas</h5>
        <button class="new-chat-btn" @click="openNewChat" title="Nova conversa">
          <i class="bi bi-pencil-square"></i>
        </button>
      </div>

      <!-- New Chat Panel -->
      <transition name="slide-right">
        <div v-if="showNewChat" class="new-chat-overlay">
          <div class="nc-header">
            <button class="nc-back" @click="showNewChat = false"><i class="bi bi-arrow-left"></i></button>
            <h6 class="mb-0">Nova Mensagem</h6>
          </div>
          <div class="nc-to">
            <span class="nc-to-label">Para:</span>
            <div class="nc-to-chips">
              <span v-for="u in selectedNewUsers" :key="u.id" class="nc-chip">
                {{ u.name }}
                <button @click="removeNewUser(u)"><i class="bi bi-x"></i></button>
              </span>
              <input
                ref="newChatInputRef"
                v-model="newChatSearch"
                type="text"
                placeholder="Pesquisar nome ou email..."
                class="nc-search-input"
                @input="onNewChatSearch"
              />
            </div>
          </div>
          <div class="nc-divider"></div>
          <div class="nc-results">
            <div v-if="filteredAvailable.length === 0 && newChatSearch" class="nc-empty">
              <i class="bi bi-search"></i>
              <p>Nenhum utilizador encontrado</p>
            </div>
            <div v-else-if="filteredAvailable.length === 0" class="nc-empty">
              <i class="bi bi-people"></i>
              <p>Pesquise por nome ou email</p>
            </div>
            <div
              v-for="u in filteredAvailable"
              :key="u.id"
              class="nc-user-item"
              @click="selectNewUser(u)"
            >
              <div class="avatar" :style="avatarStyle(u.name)">
                <img v-if="u.photo" :src="u.photo" :alt="u.name" />
                <span v-else>{{ initials(u.name) }}</span>
              </div>
              <div class="nc-user-info">
                <span class="nc-user-name">{{ u.name }}</span>
                <span class="nc-user-role">
                  <span class="role-dot" :class="'role-' + u.role"></span>
                  {{ u.role === 'admin' ? 'Administrador' : u.role === 'funcionario' ? 'Funcionário' : 'Cliente' }}
                </span>
              </div>
              <i v-if="selectedNewUsers.find(s => s.id === u.id)" class="bi bi-check-circle-fill nc-check"></i>
            </div>
          </div>
        </div>
      </transition>

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
            <div class="avatar" :style="avatarStyle(c.name)">
              <img v-if="c.photo" :src="c.photo" :alt="c.name" />
              <span v-else>{{ initials(c.name) }}</span>
              <span v-if="c.online || isUserOnline(c.id)" class="online-dot"></span>
            </div>
            <div class="conv-info">
              <div class="conv-row-top">
                <span class="conv-name">{{ c.name }}</span>
                <span class="conv-time">{{ formatTime(c.last_at) }}</span>
              </div>
              <div class="conv-row-bottom">
                <span class="conv-preview" :class="{ bold: (parseInt(c.unread) || 0) > 0 }">
                  {{ previewText(c.last_message) }}
                </span>
                <span v-if="(parseInt(c.unread) || 0) > 0" class="unread-badge">{{ c.unread }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ MAIN CHAT ═══ -->
    <div class="messenger-main" :class="{ 'main-full': !showSidebar || isMobile }">
      <!-- Header -->
      <div v-if="selected" class="chat-header">
        <div class="ch-left">
          <button class="btn-icon mobile-back" @click="$emit('back')"><i class="bi bi-arrow-left"></i></button>
          <div class="avatar sm" :style="avatarStyle(selected.name)">
            <img v-if="selected.photo" :src="selected.photo" :alt="selected.name" />
            <span v-else>{{ initials(selected.name) }}</span>
          </div>
          <div class="ch-info">
            <h6 class="mb-0">{{ selected.name }}</h6>
            <span class="ch-status"><span class="status-dot" :class="{ 'is-online': isUserOnline(selected?.id) }"></span> {{ isUserOnline(selected?.id) ? 'Online' : 'Offline' }}</span>
          </div>
        </div>
        <div class="ch-actions">
          <button class="btn-icon" title="Chamada de voz" @click="startVoiceCall">
            <i class="bi bi-telephone-fill"></i>
          </button>
          <button class="btn-icon" title="Videochamada" @click="startVideoCall">
            <i class="bi bi-camera-video-fill"></i>
          </button>
          <div class="dropdown">
            <button class="btn-icon" title="Mais opções" @click="showMoreMenu = !showMoreMenu">
              <i class="bi bi-three-dots"></i>
            </button>
            <div v-if="showMoreMenu" class="dropdown-menu-custom">
              <button @click="showMoreMenu = false"><i class="bi bi-search me-2"></i>Pesquisar na conversa</button>
              <button @click="showMoreMenu = false"><i class="bi bi-bell-slash me-2"></i>Silenciar notificações</button>
              <button class="danger" @click="showMoreMenu = false; showDeleteConfirm = true"><i class="bi bi-trash me-2"></i>Apagar conversa</button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="chat-header empty-header">
        <div class="text-muted"><i class="bi bi-chat-square-text me-2"></i>Selecione uma conversa</div>
      </div>

      <!-- Messages -->
      <div class="messages-area" ref="messagesRef" @dragover.prevent="onDragOver" @dragleave.prevent="dragOver = false" @drop.prevent="onDrop">
        <div v-if="dragOver" class="drop-overlay">
          <i class="bi bi-cloud-arrow-up"></i>
          <p>Solte o ficheiro para enviar</p>
        </div>

        <div v-if="chatStore.loading && chatStore.messages.length === 0" class="msg-empty">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">A carregar mensagens...</p>
        </div>
        <div v-else-if="chatStore.messages.length === 0" class="msg-empty">
          <div class="empty-icon"><i class="bi bi-chat-dots"></i></div>
          <h5>Sem mensagens ainda</h5>
          <p>Envie a primeira mensagem para iniciar a conversa</p>
        </div>
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
              <img v-if="selected?.photo" :src="selected.photo" :alt="selected.name" @error="($event) => $event.target.style.display='none'" />
              <span v-if="!selected?.photo" class="avatar-initials">{{ initials(selected?.name) }}</span>
            </div>
            <div class="msg-content">
              <!-- File message -->
              <div v-if="isFileMessage(m)" class="msg-bubble file-bubble" :class="isMine(m) ? 'bubble-mine' : 'bubble-theirs'">
                <div v-if="isImageFile(m)" class="file-preview">
                  <img :src="getFileData(m).url" :alt="getFileData(m).name" @click="openFile(getFileData(m))" />
                </div>
                <div class="file-info" @click="openFile(getFileData(m))">
                  <i class="file-icon" :class="getFileIcon(getFileData(m).type)"></i>
                  <div class="file-details">
                    <span class="file-name">{{ getFileData(m).name }}</span>
                    <span class="file-size">{{ formatFileSize(getFileData(m).size) }}</span>
                  </div>
                  <i class="bi bi-download file-dl"></i>
                </div>
              </div>
              <!-- Text message -->
              <div v-else class="msg-bubble" :class="isMine(m) ? 'bubble-mine' : 'bubble-theirs'">
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

      <!-- File Preview Before Send -->
      <div v-if="pendingFile" class="file-pending">
        <div class="fp-card">
          <div class="fp-preview">
            <img v-if="pendingFile.preview" :src="pendingFile.preview" />
            <i v-else :class="getFileIcon(pendingFile.type)" class="fp-icon"></i>
          </div>
          <div class="fp-info">
            <span class="fp-name">{{ pendingFile.name }}</span>
            <span class="fp-size">{{ formatFileSize(pendingFile.size) }}</span>
          </div>
          <button class="fp-remove" @click="cancelPendingFile"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>

      <!-- Input Area -->
      <div v-if="selected" class="input-area">
        <!-- Emoji Picker -->
        <transition name="fade-up">
          <div v-if="showEmoji" class="emoji-picker">
            <div class="emoji-grid">
              <button v-for="e in emojiList" :key="e" class="emoji-btn" @click="insertEmoji(e)">{{ e }}</button>
            </div>
          </div>
        </transition>

        <div class="input-row">
          <input ref="fileInputRef" type="file" class="d-none" @change="onFileSelect" accept="*/*" />
          <button class="btn-icon" title="Anexar ficheiro" @click="$refs.fileInputRef?.click()">
            <i class="bi bi-plus-circle-fill"></i>
          </button>
          <button class="btn-icon" :class="{ active: showEmoji }" title="Emoji" @click="showEmoji = !showEmoji">
            <i class="bi bi-emoji-smile-fill"></i>
          </button>
          <textarea
            ref="textareaRef"
            v-model="input"
            rows="1"
            placeholder="Escreva uma mensagem..."
            :disabled="chatStore.sending"
            @keydown.enter.exact.prevent="onSend"
            @input="autoResize"
          ></textarea>
          <button
            class="send-btn"
            :class="{ active: input.trim() || pendingFile }"
            @click="onSend"
            :disabled="chatStore.sending"
          >
            <i class="bi" :class="input.trim() || pendingFile ? 'bi-send-fill' : 'bi-mic'"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ CALL OVERLAY ═══ -->
    <transition name="fade">
      <div v-if="call.callState.value === 'calling' || call.callState.value === 'connecting' || call.callState.value === 'connected'" class="call-overlay">
        <div class="call-card">
          <div class="call-avatar" :style="avatarStyle(call.remoteUser.value?.name || '')">
            <img v-if="call.remoteUser.value?.photo" :src="call.remoteUser.value.photo" />
            <span v-else>{{ initials(call.remoteUser.value?.name || '?') }}</span>
          </div>
          <h4 class="call-name">{{ call.remoteUser.value?.name }}</h4>
          <p v-if="call.callState.value === 'calling'" class="call-status">A chamar...</p>
          <p v-else-if="call.callState.value === 'connecting'" class="call-status">A ligar...</p>
          <p v-else-if="call.callState.value === 'connected'" class="call-status connected">
            <i class="bi bi-record-circle"></i> {{ call.formatTime(call.callTimer.value) }}
          </p>
          <div class="call-actions">
            <button class="call-btn mute" @click="toggleMute" :class="{ active: isMuted }">
              <i :class="isMuted ? 'bi bi-mic-mute-fill' : 'bi bi-mic-fill'"></i>
            </button>
            <button v-if="call.callType.value === 'video'" class="call-btn mute" @click="toggleVideoOff">
              <i :class="isVideoOff ? 'bi bi-camera-video-off-fill' : 'bi bi-camera-video-fill'"></i>
            </button>
            <button class="call-btn end" @click="call.endCall()">
              <i class="bi bi-telephone-x-fill"></i>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Incoming Call -->
    <transition name="fade">
      <div v-if="call.callState.value === 'ringing' && call.incomingCall.value" class="call-overlay incoming">
        <div class="call-card incoming-card">
          <div class="call-avatar pulse" :style="avatarStyle(call.incomingCall.value?.fromName || '')">
            <span>{{ initials(call.incomingCall.value?.fromName || '?') }}</span>
          </div>
          <h4 class="call-name">{{ call.incomingCall.value?.fromName }}</h4>
          <p class="call-status">
            <i :class="call.incomingCall.value?.callType === 'video' ? 'bi bi-camera-video' : 'bi bi-telephone'"></i>
            Chamada de {{ call.incomingCall.value?.callType === 'video' ? 'vídeo' : 'voz' }}
          </p>
          <div class="call-actions">
            <button class="call-btn end" @click="declineIncoming">
              <i class="bi bi-telephone-x-fill"></i>
            </button>
            <button class="call-btn accept" @click="acceptIncoming">
              <i class="bi bi-telephone-fill"></i>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Dialog -->
    <transition name="fade">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="confirm-dialog">
          <div class="confirm-icon">
            <i class="bi bi-trash"></i>
          </div>
          <h5>Apagar conversa</h5>
          <p>Tem a certeza que deseja apagar esta conversa? Esta acção não pode ser desfeita.</p>
          <div class="confirm-actions">
            <button class="btn-cancel" @click="showDeleteConfirm = false">Cancelar</button>
            <button class="btn-danger" @click="confirmDelete">Apagar</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onMounted, onBeforeUnmount } from 'vue'
import { useChatStore } from '@/stores/chatStore'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'
import { useChatCall } from '@/composables/useChatCall'
import { usePresence } from '@/composables/usePresence'
import { supabase } from '@/lib/supabase'

const { t, locale } = useI18n()

const props = defineProps({
  selected: { type: Object, default: null },
  showSidebar: { type: Boolean, default: true },
})
const emit = defineEmits(['select', 'back', 'sent'])

const chatStore = useChatStore()
const authStore = useAuthStore()
const call = useChatCall()
const { isUserOnline } = usePresence()

const input = ref('')
const search = ref('')
const messagesRef = ref(null)
const textareaRef = ref(null)
const fileInputRef = ref(null)
const newChatInputRef = ref(null)
const showNewChat = ref(false)
const newChatSearch = ref('')
const selectedNewUsers = ref([])
const showEmoji = ref(false)
const showMoreMenu = ref(false)
const pendingFile = ref(null)
const dragOver = ref(false)
const isMuted = ref(false)
const isVideoOff = ref(false)
const isMobile = ref(false)
const prevMsgCount = ref(0)
const showDeleteConfirm = ref(false)

const checkMobile = () => { isMobile.value = window.innerWidth <= 768 }

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  const myId = authStore.user?.id
  if (myId) call.initListener(myId)
})
onBeforeUnmount(() => {
  window.removeEventListener('resize', checkMobile)
})

const playNotifSound = () => {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)()
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.type = 'sine'
    osc.frequency.setValueAtTime(880, ctx.currentTime)
    osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.08)
    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.16)
    gain.gain.setValueAtTime(0.3, ctx.currentTime)
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3)
    osc.start(ctx.currentTime)
    osc.stop(ctx.currentTime + 0.3)
    setTimeout(() => ctx.close(), 400)
  } catch {}
}

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

const emojiList = [
  '😀','😂','😍','🥰','😎','🤩','😇','🤗','😏','😌',
  '😴','🥳','😭','😤','🤔','🙄','😬','🫡','🤯','🥶',
  '👍','👎','👏','🙌','🤝','💪','❤️','🔥','⭐','🎉',
  '🙏','✅','❌','⏰','📎','📁','💡','📌','🎯','🚀',
]

const filteredAvailable = computed(() => {
  const q = newChatSearch.value.trim().toLowerCase()
  let users = chatStore.availableUsers || []
  const selIds = selectedNewUsers.value.map(u => u.id)
  users = users.filter(u => !selIds.includes(u.id))
  if (q) users = users.filter(u => (u.name || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q))
  return users
})

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return chatStore.conversations
  return chatStore.conversations.filter(c =>
    (c.name || '').toLowerCase().includes(q) ||
    (c.last_message || '').toLowerCase().includes(q)
  )
})

const openNewChat = () => {
  showNewChat.value = true
  chatStore.fetchAvailableUsers()
  nextTick(() => newChatInputRef.value?.focus())
}

const selectNewUser = (user) => {
  if (selectedNewUsers.value.find(u => u.id === user.id)) {
    selectedNewUsers.value = selectedNewUsers.value.filter(u => u.id !== user.id)
  } else {
    selectedNewUsers.value.push(user)
  }
}

const removeNewUser = (user) => {
  selectedNewUsers.value = selectedNewUsers.value.filter(u => u.id !== user.id)
}

const onNewChatSearch = () => {
  if (!chatStore.availableUsers.length) chatStore.fetchAvailableUsers()
}

watch(selectedNewUsers, (users) => {
  if (users.length === 1) {
    showNewChat.value = false
    emit('select', users[0])
    chatStore.fetchMessages(users[0].id)
    selectedNewUsers.value = []
    newChatSearch.value = ''
  }
}, { deep: true })

const isMine = (m) => {
  const myId = authStore.user?.id
  if (myId == null) return false
  if (parseInt(m.sender_id) === parseInt(myId)) return true
  if (authStore.user?.role === 'admin' && m.sender_id == null) return true
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
  if (d.toDateString() === now.toDateString()) return 'Hoje'
  const yesterday = new Date(now); yesterday.setDate(yesterday.getDate() - 1)
  if (d.toDateString() === yesterday.toDateString()) return 'Ontem'
  const loc = locale.value === 'fr' ? 'fr-FR' : locale.value === 'en' ? 'en-US' : 'pt-PT'
  return d.toLocaleDateString(loc, { day: 'numeric', month: 'long', year: 'numeric' })
}

const previewText = (msg) => {
  if (!msg) return 'Sem mensagens'
  if (msg.startsWith('[FILE]')) return '📎 Ficheiro'
  return msg
}

const isFileMessage = (m) => (m.message || '').startsWith('[FILE]')
const isImageFile = (m) => {
  try { const d = JSON.parse(m.message.slice(6)); return d.type?.startsWith('image/') } catch { return false }
}
const getFileData = (m) => {
  try { return JSON.parse(m.message.slice(6)) } catch { return { url: '', name: 'Ficheiro', type: '', size: 0 } }
}
const openFile = (data) => { if (data?.url) window.open(data.url, '_blank') }
const getFileIcon = (type) => {
  if (!type) return 'bi bi-file-earmark'
  if (type.startsWith('image/')) return 'bi bi-file-earmark-image'
  if (type.startsWith('video/')) return 'bi bi-file-earmark-play'
  if (type.startsWith('audio/')) return 'bi bi-file-earmark-music'
  if (type.includes('pdf')) return 'bi bi-file-earmark-pdf'
  if (type.includes('word') || type.includes('document')) return 'bi bi-file-earmark-word'
  if (type.includes('sheet') || type.includes('excel')) return 'bi bi-file-earmark-excel'
  return 'bi bi-file-earmark'
}
const formatFileSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(1) + ' MB'
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

const insertEmoji = (emoji) => {
  input.value += emoji
  showEmoji.value = false
  textareaRef.value?.focus()
}

const onFileSelect = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (file.size > 25 * 1024 * 1024) { alert('Ficheiro muito grande (máx 25MB)'); return }
  const fp = { file, name: file.name, size: file.size, type: file.type, preview: null }
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = () => { fp.preview = reader.result }
    reader.readAsDataURL(file)
  }
  pendingFile.value = fp
  e.target.value = ''
}

const cancelPendingFile = () => { pendingFile.value = null }

const onDragOver = () => { dragOver.value = true }
const onDrop = (e) => {
  dragOver.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) {
    if (file.size > 25 * 1024 * 1024) return
    const fp = { file, name: file.name, size: file.size, type: file.type, preview: null }
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = () => { fp.preview = reader.result }
      reader.readAsDataURL(file)
    }
    pendingFile.value = fp
  }
}

const onSend = async () => {
  const text = input.value.trim()
  const file = pendingFile.value
  if (!text && !file) return
  if (!props.selected) return

  if (file) {
    input.value = ''
    pendingFile.value = null
    const res = await chatStore.sendFileMessage(file.file, props.selected.id)
    if (!res.success) { pendingFile.value = file }
  }

  if (text) {
    input.value = ''
    if (textareaRef.value) textareaRef.value.style.height = 'auto'
    const res = await chatStore.sendMessage(text, props.selected.id)
    if (!res.success) input.value = text
  }
  emit('sent')
  await scrollDown()
}

const startVoiceCall = async () => {
  if (!props.selected) return
  const myId = authStore.user?.id
  const myName = authStore.user?.name || authStore.user?.email
  const myPhoto = authStore.user?.photo
  await call.startCall(props.selected.id, props.selected.name, props.selected.photo, 'audio', myId, myName, myPhoto)
}

const startVideoCall = async () => {
  if (!props.selected) return
  const myId = authStore.user?.id
  const myName = authStore.user?.name || authStore.user?.email
  const myPhoto = authStore.user?.photo
  await call.startCall(props.selected.id, props.selected.name, props.selected.photo, 'video', myId, myName, myPhoto)
}

const toggleMute = () => { isMuted.value = !call.toggleMute() }
const toggleVideoOff = () => { isVideoOff.value = !call.toggleVideo() }

const acceptIncoming = async () => {
  const payload = call.incomingCall.value
  if (payload) await call.acceptCall(payload, authStore.user?.id)
}

const declineIncoming = () => { call.declineCall() }

const confirmDelete = async () => {
  if (!props.selected) return
  showDeleteConfirm.value = false
  showMoreMenu.value = false
  const res = await chatStore.deleteConversation(props.selected.id)
  if (res.success) {
    emit('back')
  }
}

watch(() => chatStore.messages.length, (newLen, oldLen) => {
  scrollDown()
  if (newLen > oldLen && oldLen > 0 && prevMsgCount.value > 0) {
    const last = chatStore.messages[chatStore.messages.length - 1]
    if (last && !isMine(last)) playNotifSound()
  }
  prevMsgCount.value = newLen
})

watch(() => props.selected, (sel) => {
  if (sel) { showEmoji.value = false; showNewChat.value = false; pendingFile.value = null; showMoreMenu.value = false }
})
</script>

<style scoped>
.messenger-panel {
  display: flex; height: calc(100vh - 140px); min-height: 550px;
  background: #fff; border-radius: 16px;
  overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.04);
  position: relative;
}

/* ── SIDEBAR ── */
.messenger-sidebar { width: 340px; border-right: 1px solid #e4e6eb; display: flex; flex-direction: column; background: #fff; flex-shrink: 0; position: relative; }
.sidebar-header { padding: 16px 16px 12px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e4e6eb; }
.sidebar-header h5 { font-weight: 700; font-size: 1.3rem; color: #050505; }
.new-chat-btn { width: 36px; height: 36px; border-radius: 50%; border: none; background: #e4e6eb; color: #050505; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; transition: all 0.2s; }
.new-chat-btn:hover { background: #0084ff; color: #fff; }

.sidebar-search { position: relative; padding: 8px 16px; }
.sidebar-search i { position: absolute; left: 28px; top: 50%; transform: translateY(-50%); color: #65676b; font-size: 0.85rem; }
.sidebar-search input { width: 100%; padding: 8px 12px 8px 36px; border: none; border-radius: 20px; font-size: 0.9rem; outline: none; background: #f0f2f5; color: #050505; }
.sidebar-search input:focus { background: #fff; box-shadow: 0 0 0 2px #0084ff; }
.sidebar-search input::placeholder { color: #65676b; }

.conv-list { flex: 1; overflow-y: auto; }
.conv-list::-webkit-scrollbar { width: 6px; }
.conv-list::-webkit-scrollbar-thumb { background: #bcc0c4; border-radius: 3px; }

.empty-state { text-align: center; color: #65676b; padding: 3rem 1rem; }
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.5; }

.conv-item { display: flex; gap: 12px; padding: 10px 16px; cursor: pointer; align-items: center; transition: background 0.15s; }
.conv-item:hover { background: #f2f3f5; }
.conv-item.active { background: #e7f3ff; }

/* ── NEW CHAT OVERLAY ── */
.new-chat-overlay { position: absolute; inset: 0; z-index: 30; background: #fff; display: flex; flex-direction: column; }
.nc-header { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid #e4e6eb; }
.nc-header h6 { font-weight: 700; color: #050505; font-size: 1.05rem; }
.nc-back { width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; color: #0084ff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: background 0.2s; }
.nc-back:hover { background: #f0f2f5; }
.nc-to { display: flex; align-items: flex-start; gap: 8px; padding: 10px 16px; border-bottom: 1px solid #e4e6eb; }
.nc-to-label { font-size: 0.85rem; font-weight: 600; color: #65676b; padding-top: 6px; flex-shrink: 0; }
.nc-to-chips { display: flex; flex-wrap: wrap; gap: 4px; flex: 1; align-items: center; }
.nc-chip { display: inline-flex; align-items: center; gap: 4px; background: #e7f3ff; color: #0084ff; padding: 4px 10px; border-radius: 16px; font-size: 0.8rem; font-weight: 600; animation: chipIn 0.15s ease; }
.nc-chip button { background: none; border: none; color: #0084ff; cursor: pointer; padding: 0; font-size: 0.85rem; display: flex; }
@keyframes chipIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.nc-search-input { border: none; outline: none; font-size: 0.9rem; flex: 1; min-width: 140px; padding: 6px 0; background: transparent; color: #050505; }
.nc-search-input::placeholder { color: #65676b; }
.nc-divider { height: 1px; background: #e4e6eb; }
.nc-results { flex: 1; overflow-y: auto; }
.nc-results::-webkit-scrollbar { width: 6px; }
.nc-results::-webkit-scrollbar-thumb { background: #bcc0c4; border-radius: 3px; }
.nc-empty { text-align: center; padding: 3rem 1rem; color: #65676b; }
.nc-empty i { font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem; }
.nc-user-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; cursor: pointer; transition: background 0.15s; }
.nc-user-item:hover { background: #f2f3f5; }
.nc-user-info { flex: 1; }
.nc-user-name { display: block; font-weight: 600; font-size: 0.93rem; color: #050505; }
.nc-user-role { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #65676b; margin-top: 2px; }
.role-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.role-admin { background: #e74c3c; }
.role-funcionario { background: #2ecc71; }
.role-cliente { background: #9b59b6; }
.nc-check { color: #0084ff; font-size: 1.1rem; }

/* ── AVATAR ── */
.avatar { width: 48px; height: 48px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; flex-shrink: 0; overflow: hidden; position: relative; }
.avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar .avatar-initials { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.avatar.sm { width: 36px; height: 36px; font-size: 0.75rem; }
.online-dot { position: absolute; bottom: 1px; right: 1px; width: 12px; height: 12px; border-radius: 50%; background: #31a24c; border: 2px solid #fff; }

.conv-info { flex: 1; min-width: 0; }
.conv-row-top { display: flex; justify-content: space-between; align-items: baseline; }
.conv-name { font-size: 0.95rem; font-weight: 600; color: #050505; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-time { color: #65676b; font-size: 0.72rem; flex-shrink: 0; margin-left: 8px; }
.conv-row-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 2px; }
.conv-preview { color: #65676b; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
.conv-preview.bold { color: #050505; font-weight: 600; }

.unread-badge { background: #0084ff; color: #fff; font-size: 0.7rem; font-weight: 700; min-width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 8px; }

/* ── MAIN ── */
.messenger-main { flex: 1; display: flex; flex-direction: column; min-width: 0; position: relative; }
.main-full { width: 100%; }

.chat-header { padding: 10px 16px; border-bottom: 1px solid #e4e6eb; display: flex; align-items: center; justify-content: space-between; background: #fff; min-height: 60px; }
.empty-header { color: #65676b; font-size: 0.95rem; }
.ch-left { display: flex; align-items: center; gap: 10px; }
.ch-info h6 { font-weight: 700; font-size: 0.95rem; color: #050505; }
.ch-status { font-size: 0.75rem; color: #65676b; display: flex; align-items: center; gap: 4px; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; display: inline-block; }
.status-dot.is-online { background: #31a24c; }
.ch-actions { display: flex; gap: 2px; align-items: center; }

.btn-icon { width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; color: #0084ff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: all 0.2s; position: relative; }
.btn-icon:hover { background: #f0f2f5; }
.btn-icon.active { background: #e7f3ff; color: #0084ff; }
.mobile-back { display: none; }

/* Dropdown */
.dropdown { position: relative; }
.dropdown-menu-custom { position: absolute; top: 100%; right: 0; background: #fff; border-radius: 10px; box-shadow: 0 4px 24px rgba(0,0,0,0.12); min-width: 220px; z-index: 100; padding: 6px 0; animation: dropIn 0.15s ease; }
.dropdown-menu-custom button { display: flex; align-items: center; width: 100%; padding: 10px 16px; border: none; background: transparent; color: #050505; font-size: 0.9rem; cursor: pointer; text-align: left; }
.dropdown-menu-custom button:hover { background: #f2f3f5; }
.dropdown-menu-custom button.danger { color: #e74c3c; }
@keyframes dropIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

/* ── MESSAGES ── */
.messages-area { flex: 1; overflow-y: auto; padding: 16px; background: #f0f2f5; display: flex; flex-direction: column; gap: 4px; position: relative; }
.messages-area::-webkit-scrollbar { width: 6px; }
.messages-area::-webkit-scrollbar-thumb { background: #bcc0c4; border-radius: 3px; }

.drop-overlay { position: absolute; inset: 0; background: rgba(0,132,255,0.1); border: 3px dashed #0084ff; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 50; color: #0084ff; }
.drop-overlay i { font-size: 3rem; }
.drop-overlay p { font-weight: 600; margin-top: 0.5rem; }

.msg-empty { text-align: center; color: #65676b; margin: auto; padding: 2rem; }
.msg-empty .empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.msg-empty .empty-icon i { font-size: 2.5rem; color: #65676b; }
.msg-empty h5 { color: #050505; font-weight: 700; }
.msg-empty p { color: #65676b; font-size: 0.9rem; }

.date-divider { text-align: center; margin: 12px 0; }
.date-divider span { background: #e4e6eb; color: #65676b; font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 12px; }

.msg-row { display: flex; gap: 8px; max-width: 70%; margin-bottom: 2px; }
.msg-row.mine { align-self: flex-end; flex-direction: row-reverse; }
.msg-row.theirs { align-self: flex-start; }

.msg-avatar { width: 28px; height: 28px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: 700; flex-shrink: 0; overflow: hidden; align-self: flex-end; }
.msg-avatar img { width: 100%; height: 100%; object-fit: cover; }

.msg-content { display: flex; flex-direction: column; }

.msg-bubble { padding: 8px 14px; border-radius: 18px; word-wrap: break-word; max-width: 100%; line-height: 1.35; }
.bubble-mine { background: #0084ff; color: #fff; border-bottom-right-radius: 4px; }
.bubble-theirs { background: #fff; color: #050505; border-bottom-left-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.08); }
.msg-text { font-size: 0.93rem; white-space: pre-wrap; }

.file-bubble { padding: 0; overflow: hidden; min-width: 260px; max-width: 320px; }
.file-preview { cursor: pointer; }
.file-preview img { width: 100%; max-height: 220px; object-fit: cover; display: block; }
.file-info { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; transition: background 0.15s; }
.bubble-mine .file-info:hover { background: rgba(255,255,255,0.1); }
.bubble-theirs .file-info:hover { background: #f2f3f5; }
.file-icon { font-size: 1.8rem; flex-shrink: 0; }
.bubble-mine .file-icon { color: #fff; }
.bubble-theirs .file-icon { color: #0084ff; }
.file-details { flex: 1; min-width: 0; }
.file-name { display: block; font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-size { display: block; font-size: 0.72rem; opacity: 0.7; }
.file-dl { font-size: 1.1rem; flex-shrink: 0; opacity: 0.6; }

.msg-meta { display: flex; align-items: center; gap: 4px; margin-top: 2px; padding: 0 4px; }
.meta-mine { justify-content: flex-end; }
.meta-theirs { justify-content: flex-start; }
.msg-time { font-size: 0.68rem; color: #65676b; }
.check-icon { font-size: 0.8rem; }
.check-icon.read { color: #0084ff; }

/* ── FILE PENDING ── */
.file-pending { padding: 8px 16px 0; background: #fff; border-top: 1px solid #e4e6eb; }
.fp-card { display: flex; align-items: center; gap: 10px; background: #f0f2f5; border-radius: 10px; padding: 8px 12px; animation: slideUp 0.2s ease; }
@keyframes slideUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.fp-preview { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #e4e6eb; display: flex; align-items: center; justify-content: center; }
.fp-preview img { width: 100%; height: 100%; object-fit: cover; }
.fp-icon { font-size: 1.4rem; color: #65676b; }
.fp-info { flex: 1; min-width: 0; }
.fp-name { display: block; font-size: 0.82rem; font-weight: 600; color: #050505; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fp-size { display: block; font-size: 0.72rem; color: #65676b; }
.fp-remove { width: 28px; height: 28px; border-radius: 50%; border: none; background: #e4e6eb; color: #65676b; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.75rem; flex-shrink: 0; transition: all 0.2s; }
.fp-remove:hover { background: #e74c3c; color: #fff; }

/* ── INPUT ── */
.input-area { padding: 10px 16px; background: #fff; border-top: 1px solid #e4e6eb; position: relative; }
.input-row { display: flex; align-items: flex-end; gap: 6px; background: #f0f2f5; border-radius: 24px; padding: 6px 8px 6px 12px; }
.input-row textarea { flex: 1; border: none; background: transparent; resize: none; font-size: 0.93rem; font-family: inherit; outline: none; max-height: 120px; padding: 6px 0; line-height: 1.35; color: #050505; }
.input-row textarea::placeholder { color: #65676b; }
.input-row textarea:disabled { opacity: 0.6; }

.send-btn { width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; color: #65676b; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.2rem; flex-shrink: 0; transition: all 0.2s; }
.send-btn.active { color: #0084ff; }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── EMOJI PICKER ── */
.emoji-picker { position: absolute; bottom: 100%; left: 12px; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); padding: 10px; width: 320px; max-height: 280px; overflow-y: auto; z-index: 50; }
.emoji-grid { display: grid; grid-template-columns: repeat(10, 1fr); gap: 2px; }
.emoji-btn { width: 30px; height: 30px; border: none; background: transparent; font-size: 1.2rem; cursor: pointer; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: background 0.1s; }
.emoji-btn:hover { background: #f0f2f5; }

/* ── CALL OVERLAY ── */
.call-overlay { position: absolute; inset: 0; z-index: 200; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; }
.call-card { text-align: center; color: #fff; }
.call-avatar { width: 100px; height: 100px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 2rem; margin: 0 auto 20px; overflow: hidden; position: relative; }
.call-avatar img { width: 100%; height: 100%; object-fit: cover; }
.call-avatar.pulse { animation: callPulse 1.5s infinite; }
@keyframes callPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(46,204,113,0.5); } 50% { box-shadow: 0 0 0 20px rgba(46,204,113,0); } }
.call-name { font-size: 1.3rem; font-weight: 700; margin-bottom: 6px; }
.call-status { font-size: 0.9rem; opacity: 0.8; margin-bottom: 30px; }
.call-status.connected { color: #2ecc71; }
.call-actions { display: flex; gap: 16px; justify-content: center; }
.call-btn { width: 56px; height: 56px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.3rem; transition: transform 0.15s; color: #fff; }
.call-btn:hover { transform: scale(1.1); }
.call-btn.mute { background: rgba(255,255,255,0.2); }
.call-btn.mute.active { background: #fff; color: #050505; }
.call-btn.end { background: #e74c3c; }
.call-btn.accept { background: #2ecc71; }

/* ── TRANSITIONS ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.fade-up-enter-active, .fade-up-leave-active { transition: all 0.2s ease; }
.fade-up-enter-from, .fade-up-leave-to { opacity: 0; transform: translateY(10px); }
.slide-right-enter-active, .slide-right-leave-active { transition: all 0.25s ease; }
.slide-right-enter-from { transform: translateX(-100%); opacity: 0; }
.slide-right-leave-to { transform: translateX(-100%); opacity: 0; }

/* ── DELETE CONFIRM DIALOG ── */
.modal-overlay {
  position: fixed; inset: 0; z-index: 2000;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
}
.confirm-dialog {
  background: #fff; border-radius: 12px; padding: 32px;
  max-width: 380px; width: 90%; text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.confirm-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: #ffebee; color: #e53935;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; margin: 0 auto 16px;
}
.confirm-dialog h5 { font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
.confirm-dialog p { color: #666; font-size: 0.9rem; margin-bottom: 24px; line-height: 1.5; }
.confirm-actions { display: flex; gap: 10px; justify-content: center; }
.confirm-actions .btn-cancel, .confirm-actions .btn-danger {
  padding: 10px 24px; border-radius: 8px; font-weight: 600;
  font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.2s;
}
.confirm-actions .btn-cancel { background: #f0f0f0; color: #333; }
.confirm-actions .btn-cancel:hover { background: #e0e0e0; }
.confirm-actions .btn-danger { background: #e53935; color: #fff; }
.confirm-actions .btn-danger:hover { background: #c62828; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .messenger-panel { border-radius: 0; height: calc(100vh - 60px); }
  .messenger-sidebar { width: 100%; position: absolute; inset: 0; z-index: 10; }
  .messenger-sidebar.sidebar-hidden { display: none; }
  .messenger-main { width: 100%; }
  .main-full { width: 100%; }
  .mobile-back { display: flex; }
  .msg-row { max-width: 85%; }
  .emoji-picker { width: 280px; }
  .emoji-grid { grid-template-columns: repeat(8, 1fr); }
}
</style>
