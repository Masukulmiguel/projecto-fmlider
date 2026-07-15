<template>
  <Transition name="clock-fade">
    <div v-if="visible" class="inactivity-overlay" @click="dismiss" @mousemove="dismiss" @keydown="dismiss">
      <div class="clock-card">
        <div class="clock-brand">
          <img src="/assets/img/logo.png" alt="FMLider" class="clock-logo">
        </div>
        <div class="clock-time">{{ time }}</div>
        <div class="clock-date">{{ day }}, {{ date }}</div>
        <div class="clock-divider"></div>
        <div class="clock-message">
          <i class="bi bi-cursor"></i>
          {{ t('inactivity.subtitle') }}
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from '@/composables/useI18n.js'

const { t, locale } = useI18n()

const props = defineProps({
  timeout: { type: Number, default: 600000 },
})

const emit = defineEmits(['dismiss'])

const visible = ref(false)
const time = ref('')
const date = ref('')
const day = ref('')
let timer = null
let clockInterval = null
let lastActivity = Date.now()

const days = {
  pt: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
  en: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  fr: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
}
const months = {
  pt: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
  fr: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
}

const updateClock = () => {
  const now = new Date()
  const lang = locale.value
  const localeMap = { pt: 'pt-BR', en: 'en-US', fr: 'fr-FR' }
  time.value = now.toLocaleTimeString(localeMap[lang] || 'pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })
  date.value = `${now.getDate()} de ${months[lang][now.getMonth()]} ${now.getFullYear()}`
  day.value = days[lang][now.getDay()]
}

const startTimer = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    visible.value = true
    updateClock()
    clockInterval = setInterval(updateClock, 1000)
  }, props.timeout)
}

const dismiss = () => {
  visible.value = false
  clearInterval(clockInterval)
  lastActivity = Date.now()
  startTimer()
}

const resetActivity = () => {
  lastActivity = Date.now()
  if (!visible.value) {
    clearTimeout(timer)
    startTimer()
  }
}

onMounted(() => {
  updateClock()
  startTimer()
  const events = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart']
  events.forEach(e => document.addEventListener(e, resetActivity, { passive: true }))
})

onBeforeUnmount(() => {
  clearTimeout(timer)
  clearInterval(clockInterval)
  const events = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart']
  events.forEach(e => document.removeEventListener(e, resetActivity))
})
</script>

<style scoped>
.inactivity-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: #1a1a2e;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  user-select: none;
}

.clock-card {
  text-align: center;
  padding: 3rem 4rem;
}

.clock-brand {
  margin-bottom: 2rem;
}

.clock-logo {
  height: 50px;
  filter: brightness(0) invert(1);
  opacity: 0.9;
}

.clock-time {
  font-size: 6rem;
  font-weight: 300;
  color: #fff;
  letter-spacing: 4px;
  line-height: 1;
  font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
  margin-bottom: 0.75rem;
}

.clock-date {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 300;
  letter-spacing: 1px;
  text-transform: capitalize;
}

.clock-divider {
  width: 60px;
  height: 1px;
  background: rgba(255, 255, 255, 0.15);
  margin: 2rem auto;
}

.clock-message {
  color: rgba(255, 255, 255, 0.35);
  font-size: 0.9rem;
  font-weight: 400;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.clock-message i {
  font-size: 0.85rem;
}

.clock-fade-enter-active { transition: opacity 0.4s ease; }
.clock-fade-leave-active { transition: opacity 0.3s ease; }
.clock-fade-enter-from,
.clock-fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .clock-card { padding: 2rem 1.5rem; }
  .clock-time { font-size: 3.5rem; letter-spacing: 2px; }
  .clock-date { font-size: 0.95rem; }
}
</style>
