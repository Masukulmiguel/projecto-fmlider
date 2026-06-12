<template>
  <div class="chatbot-widget" :class="{ open: isOpen }">
    <transition name="chatbot-window">
      <div v-if="isOpen" class="chatbot-window">
        <div class="chatbot-header">
          <div class="chatbot-avatar">
            <i class="bi bi-robot"></i>
          </div>
          <div class="chatbot-info">
            <h6>Assistente FMLider</h6>
            <span class="status">
              <span class="status-dot"></span>
              Online · responde em segundos
            </span>
          </div>
          <button class="chatbot-close" @click="isOpen = false" aria-label="Fechar">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="chatbot-messages" ref="messagesRef">
          <div v-for="(m, i) in messages" :key="i" class="msg" :class="m.role">
            <div class="msg-avatar" v-if="m.role === 'bot'">
              <i class="bi bi-robot"></i>
            </div>
            <div class="msg-bubble">
              <div class="msg-text" v-html="formatText(m.text)"></div>
              <div class="msg-time">{{ m.time }}</div>
            </div>
          </div>
          <div v-if="loading" class="msg bot">
            <div class="msg-avatar"><i class="bi bi-robot"></i></div>
            <div class="msg-bubble">
              <div class="typing">
                <span></span><span></span><span></span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="messages.length === 1" class="chatbot-suggestions">
          <button v-for="s in suggestions" :key="s" class="suggestion" @click="send(s)">
            {{ s }}
          </button>
        </div>

        <form class="chatbot-input" @submit.prevent="send()">
          <input
            v-model="input"
            type="text"
            placeholder="Escreve a tua mensagem…"
            :disabled="loading"
            maxlength="1000"
          />
          <button type="submit" :disabled="!input.trim() || loading" aria-label="Enviar">
            <i class="bi bi-send-fill"></i>
          </button>
        </form>

        <div class="chatbot-footer">
          <i class="bi bi-shield-check"></i>
          Alimentado por IA · pode cometer erros
        </div>
      </div>
    </transition>

    <button class="chatbot-toggle" :class="{ open: isOpen }" @click="isOpen = !isOpen" aria-label="Abrir chat">
      <i v-if="!isOpen" class="bi bi-chat-dots-fill"></i>
      <i v-else class="bi bi-x-lg"></i>
      <span v-if="!isOpen" class="chatbot-pulse"></span>
    </button>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'
import axios from 'axios'

const isOpen = ref(false)
const input = ref('')
const loading = ref(false)
const messages = ref([])
const messagesRef = ref(null)
const suggestions = [
  'Quais serviços oferecem?',
  'Como faço um embarque?',
  'Onde fica a sede?',
  'Têm FAQ?',
]

const now = () => new Date().toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' })

const scrollDown = async () => {
  await nextTick()
  if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
}

const formatText = (t) => {
  const esc = String(t)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  return esc
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>')
}

const send = async (text) => {
  const content = (text ?? input.value).trim()
  if (!content || loading.value) return
  input.value = ''
  messages.value.push({ role: 'user', text: content, time: now() })
  await scrollDown()
  loading.value = true
  try {
    const history = messages.value
      .filter(m => m.role === 'user' || m.role === 'bot')
      .slice(0, -1)
      .map(m => ({ role: m.role === 'bot' ? 'model' : 'user', text: m.text }))
    const r = await axios.post('/api/chatbot/chat', { message: content, history })
    if (r.data.success) {
      const mode = r.data.data.mode || 'ai'
      let prefix = ''
      if (mode === 'fallback') prefix = '🔎 '
      messages.value.push({ role: 'bot', text: prefix + r.data.data.reply, time: now() })
    } else {
      messages.value.push({ role: 'bot', text: r.data.message || 'Desculpa, ocorreu um erro.', time: now() })
    }
  } catch (e) {
    const msg = e.response?.data?.message || 'Sem ligação ao servidor. Tenta novamente.'
    messages.value.push({ role: 'bot', text: msg, time: now() })
  } finally {
    loading.value = false
    await scrollDown()
  }
}

onMounted(() => {
  messages.value.push({
    role: 'bot',
    text: 'Olá! Sou o assistente virtual da FMLider. Posso ajudar-te com informações sobre os nossos serviços de logística e transporte. Em que posso ajudar?',
    time: now(),
  })
})
</script>

<style scoped>
.chatbot-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  font-family: inherit;
}

.chatbot-toggle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  color: #fff;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: transform 0.2s, box-shadow 0.2s;
}
.chatbot-toggle:hover { transform: scale(1.08); box-shadow: 0 12px 32px rgba(37, 99, 235, 0.5); }
.chatbot-toggle.open { background: linear-gradient(135deg, #475569 0%, #1e293b 100%); }

.chatbot-pulse {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 2px solid #2563eb;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.6); opacity: 0; }
}

.chatbot-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 380px;
  max-width: calc(100vw - 32px);
  height: 560px;
  max-height: calc(100vh - 120px);
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chatbot-window-enter-active, .chatbot-window-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.chatbot-window-enter-from, .chatbot-window-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.chatbot-header {
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  color: #fff;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.chatbot-avatar {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}
.chatbot-info { flex: 1; }
.chatbot-info h6 { margin: 0; font-weight: 600; font-size: 0.95rem; }
.chatbot-info .status { font-size: 0.75rem; opacity: 0.9; display: flex; align-items: center; gap: 0.4rem; }
.status-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
  animation: blink 2s infinite;
}
@keyframes blink {
  0%, 100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
  50% { box-shadow: 0 0 0 6px rgba(74, 222, 128, 0); }
}
.chatbot-close {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #fff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.chatbot-close:hover { background: rgba(255, 255, 255, 0.25); }

.chatbot-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.chatbot-messages::-webkit-scrollbar { width: 6px; }
.chatbot-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

.msg { display: flex; gap: 0.5rem; align-items: flex-end; max-width: 90%; }
.msg.user { align-self: flex-end; flex-direction: row-reverse; }
.msg.bot { align-self: flex-start; }

.msg-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.msg-bubble {
  background: #fff;
  padding: 0.65rem 0.9rem;
  border-radius: 16px;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  max-width: 100%;
  word-wrap: break-word;
}
.msg.user .msg-bubble {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: #fff;
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 4px;
}
.msg-text { font-size: 0.9rem; line-height: 1.45; }
.msg-time { font-size: 0.65rem; opacity: 0.65; margin-top: 0.25rem; }
.msg.user .msg-time { color: rgba(255, 255, 255, 0.85); }

.typing { display: flex; gap: 0.25rem; padding: 0.25rem 0; }
.typing span {
  width: 8px;
  height: 8px;
  background: #94a3b8;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
  30% { transform: translateY(-6px); opacity: 1; }
}

.chatbot-suggestions {
  padding: 0.5rem 1.25rem 0.75rem;
  background: #f8fafc;
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  border-top: 1px solid #e2e8f0;
}
.suggestion {
  background: #fff;
  border: 1px solid #cbd5e1;
  border-radius: 20px;
  padding: 0.35rem 0.85rem;
  font-size: 0.78rem;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}
.suggestion:hover { background: #2563eb; color: #fff; border-color: #2563eb; }

.chatbot-input {
  padding: 0.75rem 1rem;
  background: #fff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 0.5rem;
}
.chatbot-input input {
  flex: 1;
  padding: 0.6rem 0.85rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 24px;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}
.chatbot-input input:focus { border-color: #2563eb; }
.chatbot-input input:disabled { background: #f1f5f9; }
.chatbot-input button {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, transform 0.2s;
}
.chatbot-input button:disabled { opacity: 0.5; cursor: not-allowed; }
.chatbot-input button:not(:disabled):hover { transform: scale(1.05); }

.chatbot-footer {
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  text-align: center;
  font-size: 0.7rem;
  color: #64748b;
  border-top: 1px solid #e2e8f0;
}
.chatbot-footer i { margin-right: 0.25rem; }

@media (max-width: 480px) {
  .chatbot-widget { bottom: 16px; right: 16px; }
  .chatbot-window {
    width: calc(100vw - 32px);
    height: calc(100vh - 110px);
    bottom: 76px;
  }
}
</style>
