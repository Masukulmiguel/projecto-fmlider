<template>
  <div class="chatbot-widget" :class="{ open: isOpen }">
    <transition name="chatbot-window">
      <div v-if="isOpen" class="chatbot-window">
        <div class="chatbot-header">
          <div class="chatbot-avatar">
            <i class="bi bi-robot"></i>
          </div>
          <div class="chatbot-info">
            <h6>{{ t('chatbot.title') }}</h6>
            <span class="status">
              <span class="status-dot"></span>
              {{ t('chatbot.status') }}
            </span>
          </div>
          <button class="chatbot-close" @click="isOpen = false" :aria-label="t('chatbot.close')">
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

        <div v-if="showSuggestions" class="chatbot-suggestions">
          <button v-for="s in suggestions" :key="s" class="suggestion" @click="send(s)">
            {{ s }}
          </button>
        </div>

        <form class="chatbot-input" @submit.prevent="send()">
          <input
            v-model="input"
            type="text"
            :placeholder="inputPlaceholder"
            :disabled="loading"
            maxlength="1000"
          />
          <button type="submit" :disabled="!input.trim() || loading" :aria-label="t('chatbot.send')">
            <i class="bi bi-send-fill"></i>
          </button>
        </form>

        <div class="chatbot-footer">
          <i class="bi bi-shield-check"></i>
          {{ t('chatbot.footer_text') }}
        </div>
      </div>
    </transition>

    <button class="chatbot-toggle" :class="{ open: isOpen }" @click="isOpen = !isOpen" :aria-label="t('chatbot.open_chat')">
      <i v-if="!isOpen" class="bi bi-chat-dots-fill"></i>
      <i v-else class="bi bi-x-lg"></i>
      <span v-if="!isOpen" class="chatbot-pulse"></span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useI18n } from '@/composables/useI18n'
import { sanitize } from '@/utils/sanitize'

const { t, locale } = useI18n()

const isOpen = ref(false)
const input = ref('')
const loading = ref(false)
const messages = ref([])
const messagesRef = ref(null)

const API_URL = import.meta.env.VITE_API_URL || ''
const CHATBOT_URL = API_URL ? `${API_URL}/chatbot/chat` : '/api/chatbot/chat'
const VERIFY_URL = API_URL ? `${API_URL}/chatbot/verify-client` : '/api/chatbot/verify-client'
const BI_LOOKUP_URL = (bi) => API_URL ? `${API_URL}/bi-lookup/${bi}` : `/api/bi-lookup/${bi}`
const NIF_LOOKUP_URL = (nif) => API_URL ? `${API_URL}/nif-lookup/${nif}` : `/api/nif-lookup/${nif}`

const FLOW_STATES = {
  IDLE: 'idle',
  AWAITING_NAME: 'awaiting_name',
  AWAITING_BI: 'awaiting_bi',
  AWAITING_CLIENT_TYPE: 'awaiting_client_type',
  AWAITING_NIF: 'awaiting_nif',
  AWAITING_EMAIL: 'awaiting_email',
  AWAITING_USERNAME: 'awaiting_username',
  VERIFIED: 'verified',
  BLOCKED: 'blocked',
}

const flowState = ref(FLOW_STATES.IDLE)
const clientData = ref({
  fullName: '',
  bi: '',
  biNome: '',
  isClient: null,
  nif: '',
  nifData: null,
  email: '',
  username: '',
  verifiedUser: null,
  verifiedCompany: null,
})

const isValidBiFormat = (bi) => /^\d{9}[A-Z]{2}\d{3}$/i.test(bi)
const isValidNifFormat = (nif) => /^\d{10}$/.test(nif)

const showSuggestions = computed(() =>
  flowState.value === FLOW_STATES.IDLE && messages.value.length <= 1
)

const suggestions = computed(() => {
  if (flowState.value === FLOW_STATES.AWAITING_CLIENT_TYPE) {
    return ['Já sou cliente', 'Quero ser cliente']
  }
  return [t('chatbot.suggestion_1'), t('chatbot.suggestion_2'), t('chatbot.suggestion_3'), t('chatbot.suggestion_4')]
})

const inputPlaceholder = computed(() => {
  switch (flowState.value) {
    case FLOW_STATES.AWAITING_NAME: return 'Digite o seu nome completo...'
    case FLOW_STATES.AWAITING_BI: return 'Digite o número do B.I. (ex: 006151112LA041)...'
    case FLOW_STATES.AWAITING_CLIENT_TYPE: return 'Seleccione uma opção...'
    case FLOW_STATES.AWAITING_NIF: return 'Digite o NIF da empresa (10 dígitos)...'
    case FLOW_STATES.AWAITING_EMAIL: return 'Digite o seu email...'
    case FLOW_STATES.AWAITING_USERNAME: return 'Digite o seu nome de utilizador...'
    default: return t('chatbot.input_placeholder')
  }
})

const now = () => new Date().toLocaleTimeString(locale.value === 'pt' ? 'pt-PT' : locale.value === 'fr' ? 'fr-FR' : 'en-GB', { hour: '2-digit', minute: '2-digit' })

const scrollDown = async () => {
  await nextTick()
  if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
}

const formatText = (t) => {
  const esc = String(t)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  const html = esc
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>')
  return sanitize(html)
}

const addBotMessage = async (text) => {
  messages.value.push({ role: 'bot', text, time: now() })
  await scrollDown()
}

const startValidationFlow = async () => {
  flowState.value = FLOW_STATES.AWAITING_NAME
  await addBotMessage(
    'Antes de prosseguir, preciso de validar os seus dados para garantir a segurança da sua conta. Por favor, digite o seu nome completo.'
  )
}

const validateBiName = async (name) => {
  flowState.value = FLOW_STATES.AWAITING_BI
  clientData.value.fullName = name
  await addBotMessage(
    `Obrigado, **${name}**. Agora preciso do seu número de Bilhete de Identidade (B.I.) para validar os seus dados no sistema da AGT.\n\nDigite o número do B.I. (14 caracteres).`
  )
}

const validateBi = async (bi) => {
  loading.value = true
  try {
    const res = await fetch(BI_LOOKUP_URL(bi))
    const data = await res.json()

    if (data.success && data.data && data.data.nome) {
      const biNome = data.data.nome
      const nomeUpper = clientData.value.fullName.toUpperCase().trim()
      const biNomeUpper = biNome.toUpperCase().trim()

      if (nomeUpper === biNomeUpper || nomeUpper.includes(biNomeUpper) || biNomeUpper.includes(nomeUpper)) {
        clientData.value.bi = bi
        clientData.value.biNome = biNome
        flowState.value = FLOW_STATES.AWAITING_CLIENT_TYPE
        await addBotMessage(
          `B.I. validado com sucesso! Titular: **${biNome}**. Agora diga-me: **Já é cliente da FMLider** ou **deseja ser cliente**?`
        )
      } else {
        flowState.value = FLOW_STATES.BLOCKED
        await addBotMessage(
          `O nome que forneceu (**${clientData.value.fullName}**) não corresponde ao titular do B.I. (**${biNome}**). Por favor, contacte o seu supervisor para obter as informações corretas. Não posso continuar com a verificação.`
        )
      }
    } else {
      await addBotMessage(
        'Não consegui validar o B.I. Verifique se o número está correto e tente novamente.\n\nFormato esperado: 14 caracteres (ex: 006151112LA041).'
      )
    }
  } catch (e) {
    console.error('BI lookup error:', e)
    await addBotMessage('Erro ao consultar o B.I. Tente novamente em alguns instantes.')
  } finally {
    loading.value = false
  }
}

const handleClientType = async (answer) => {
  const normalized = answer.toLowerCase().trim()
  if (normalized.includes('já sou') || normalized.includes('ja sou') || normalized.includes('sou cliente') || normalized.includes('1')) {
    clientData.value.isClient = true
    flowState.value = FLOW_STATES.AWAITING_NIF
    await addBotMessage(
      'Perfeito! Como cliente, preciso de verificar a sua empresa.\n\nDigite o **NIF da empresa** (10 dígitos).'
    )
  } else if (normalized.includes('quero ser') || normalized.includes('novo') || normalized.includes('2')) {
    clientData.value.isClient = false
    flowState.value = FLOW_STATES.AWAITING_EMAIL
    await addBotMessage(
      'Entendido! Para o registar como novo cliente, preciso do seu **email**.\n\nDigite o seu email.'
    )
  } else {
    await addBotMessage('Por favor, responda "Já sou cliente" ou "Quero ser cliente".')
  }
}

const validateNif = async (nif) => {
  loading.value = true
  try {
    const res = await fetch(NIF_LOOKUP_URL(nif))
    const data = await res.json()

    if (data.success && data.data) {
      clientData.value.nif = nif
      clientData.value.nifData = data.data
      flowState.value = FLOW_STATES.AWAITING_EMAIL
      await addBotMessage(
        `NIF validado! Empresa: **${data.data.nome}**. Estado: ${data.data.estado || 'Activo'}. Agora digite o seu **email** associado à conta.`
      )
    } else {
      await addBotMessage(
        'NIF não encontrado no portal da AGT. Verifique o número e tente novamente.'
      )
    }
  } catch (e) {
    console.error('NIF lookup error:', e)
    await addBotMessage('Erro ao consultar o NIF. Tente novamente em alguns instantes.')
  } finally {
    loading.value = false
  }
}

const handleEmail = async (email) => {
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    await addBotMessage('Email inválido. Por favor, digite um email válido.')
    return
  }
  clientData.value.email = email
  flowState.value = FLOW_STATES.AWAITING_USERNAME
  await addBotMessage('Agora digite o seu **nome de utilizador** (username).')
}

const verifyClient = async (username) => {
  loading.value = true
  clientData.value.username = username

  try {
    const res = await fetch(VERIFY_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: clientData.value.email,
        username: username,
      }),
    })

    const data = await res.json()

    if (data.success && data.data) {
      clientData.value.verifiedUser = data.data.user
      clientData.value.verifiedCompany = data.data.company
      flowState.value = FLOW_STATES.VERIFIED

      const companyName = data.data.company?.company_name || 'sua empresa'
      await addBotMessage(
        `Cliente verificado com sucesso! Bem-vindo(a), **${data.data.user.name}**. Empresa: **${companyName}**. Agora posso ajudá-lo com informações sobre os seus processos. O que deseja saber?`
      )
    } else {
      flowState.value = FLOW_STATES.BLOCKED
      const errorMsg = data.message || 'Dados não encontrados.'
      await addBotMessage(
        `Verificação falhou: ${errorMsg}. Não posso continuar a responder às suas perguntas porque os dados fornecidos não foram encontrados no sistema. Por favor, contacte o seu supervisor para obter as informações corretas.`
      )
    }
  } catch (e) {
    console.error('Verify client error:', e)
    flowState.value = FLOW_STATES.BLOCKED
    await addBotMessage(
      'Erro ao verificar os seus dados. Não posso continuar. Por favor, contacte o seu supervisor para obter assistência.'
    )
  } finally {
    loading.value = false
  }
}

const callAI = async (message, history) => {
  const contextPayload = {
    message,
    history,
    verified: flowState.value === FLOW_STATES.VERIFIED,
    client: flowState.value === FLOW_STATES.VERIFIED ? {
      name: clientData.value.verifiedUser?.name,
      company: clientData.value.verifiedCompany?.company_name,
      nif: clientData.value.nif,
    } : null,
  }

  const res = await fetch(CHATBOT_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(contextPayload),
  })

  const data = await res.json()
  if (!data.success) throw new Error(data.message || 'Chatbot error')
  return data.data.reply
}

const handleFlowInput = async (content) => {
  switch (flowState.value) {
    case FLOW_STATES.AWAITING_NAME:
      await validateBiName(content)
      return true

    case FLOW_STATES.AWAITING_BI: {
      const bi = content.toUpperCase().replace(/\s/g, '')
      if (!isValidBiFormat(bi)) {
        await addBotMessage('Formato de B.I. inválido. Deve conter 14 caracteres (ex: 006151112LA041).')
        return true
      }
      await validateBi(bi)
      return true
    }

    case FLOW_STATES.AWAITING_CLIENT_TYPE:
      await handleClientType(content)
      return true

    case FLOW_STATES.AWAITING_NIF: {
      const nif = content.replace(/\s/g, '')
      if (!isValidNifFormat(nif)) {
        await addBotMessage('NIF inválido. Deve conter exactamente 10 dígitos.')
        return true
      }
      await validateNif(nif)
      return true
    }

    case FLOW_STATES.AWAITING_EMAIL:
      await handleEmail(content)
      return true

    case FLOW_STATES.AWAITING_USERNAME:
      await verifyClient(content)
      return true

    case FLOW_STATES.BLOCKED:
      await addBotMessage(
        'Não posso responder a esta pergunta. Os seus dados não foram verificados com sucesso.\n\nPor favor, contacte o seu supervisor para obter as informações corretas.'
      )
      return true

    default:
      return false
  }
}

const containsSensitiveData = (text) => {
  const lower = text.toLowerCase()
  const patterns = [
    /senha/i,
    /password/i,
    /pwd/i,
    /token/i,
    /secret/i,
    /api[_\s]?key/i,
    /credential/i,
    /admin.*email/i,
    /email.*admin/i,
  ]
  return patterns.some(p => p.test(lower))
}

const isAboutProcesses = (text) => {
  const lower = text.toLowerCase()
  const patterns = [
    /processo/i,
    /embarque/i,
    /contentor/i,
    /container/i,
    /carga/i,
    /mercadoria/i,
    /entrega/i,
    /transporte/i,
    /rastre/i,
    /tracking/i,
    /status.*processo/i,
    /estado.*processo/i,
    /onde.*carga/i,
    /quando.*cheg/i,
    /prazo/i,
    /despacho/i,
    /desembaraco/i,
    /aduana/i,
    /alfandega/i,
    /factura/i,
    /documento/i,
    /cotacao/i,
    / orcamento/i,
    /preco/i,
    /valor/i,
  ]
  return patterns.some(p => p.test(lower))
}

const isAboutClientData = (text) => {
  const lower = text.toLowerCase()
  const patterns = [
    /meu.*dado/i,
    /meu.*email/i,
    /minha.*senha/i,
    /minha.*conta/i,
    /meu.*perfil/i,
    /meu.*nif/i,
    /meu.*bi/i,
    /dados.*pessoais/i,
    /account/i,
    /password/i,
    /senha/i,
    /login/i,
  ]
  return patterns.some(p => p.test(lower))
}

const send = async (text) => {
  const content = (text ?? input.value).trim()
  if (!content || loading.value) return
  input.value = ''

  messages.value.push({ role: 'user', text: content, time: now() })
  await scrollDown()

  if (flowState.value === FLOW_STATES.IDLE) {
    if (isAboutProcesses(content) || isAboutClientData(content)) {
      await startValidationFlow()
      return
    }
  }

  const handled = await handleFlowInput(content)
  if (handled) return

  if (flowState.value !== FLOW_STATES.VERIFIED && flowState.value !== FLOW_STATES.IDLE) {
    await addBotMessage(
      'Ainda não verifiquei a sua identidade. Por favor, responda às perguntas anteriores para que eu possa ajudá-lo.'
    )
    return
  }

  if (containsSensitiveData(content)) {
    await addBotMessage(
      'Não posso fornecer informações sobre senhas, credenciais ou dados pessoais de outros utilizadores. Essa informação é confidencial.'
    )
    return
  }

  loading.value = true
  try {
    const history = messages.value.slice(0, -1).map(m => ({ role: m.role, text: m.text }))
    const reply = await callAI(content, history)
    messages.value.push({ role: 'bot', text: reply, time: now() })
  } catch (e) {
    console.error('Chatbot error:', e)
    messages.value.push({
      role: 'bot',
      text: `${t('chatbot.error')} +244 935 141 747.`,
      time: now(),
    })
  } finally {
    loading.value = false
    await scrollDown()
  }
}

onMounted(() => {
  messages.value.push({
    role: 'bot',
    text: t('chatbot.welcome'),
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
