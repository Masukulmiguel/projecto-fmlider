<template>
  <Transition name="clock-fade">
    <div v-if="visible" class="inactivity-overlay" @click="dismiss" @mousemove="dismiss" @keydown="dismiss">
      <div class="clock-container">
        <div class="clock-glow"></div>
        <div class="clock-time">{{ time }}</div>
        <div class="clock-date">{{ date }}</div>
        <div class="clock-day">{{ day }}</div>
        <div class="clock-divider"></div>
        <div class="clock-message">
          <i class="bi bi-hand-index-thumb"></i>
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
  date.value = `${now.getDate()} ${months[lang][now.getMonth()]} ${now.getFullYear()}`
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
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  user-select: none;
}

.clock-container {
  text-align: center;
  position: relative;
}

.clock-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse-glow 4s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
  50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
}

.clock-time {
  font-size: 7rem;
  font-weight: 200;
  color: #f1f5f9;
  letter-spacing: 4px;
  line-height: 1;
  position: relative;
  z-index: 1;
  text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
  animation: clock-fade-in 1s ease-out;
}

.clock-date {
  font-size: 1.5rem;
  color: #94a3b8;
  margin-top: 1rem;
  font-weight: 300;
  letter-spacing: 2px;
  position: relative;
  z-index: 1;
  animation: clock-fade-in 1s ease-out 0.2s both;
}

.clock-day {
  font-size: 1rem;
  color: #3b82f6;
  margin-top: 0.5rem;
  font-weight: 500;
  letter-spacing: 3px;
  text-transform: uppercase;
  position: relative;
  z-index: 1;
  animation: clock-fade-in 1s ease-out 0.4s both;
}

.clock-divider {
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #3b82f6, transparent);
  margin: 2rem auto;
  position: relative;
  z-index: 1;
  animation: clock-fade-in 1s ease-out 0.6s both;
}

.clock-message {
  color: #64748b;
  font-size: 1rem;
  font-weight: 400;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  position: relative;
  z-index: 1;
  animation: clock-fade-in 1s ease-out 0.8s both, pulse-text 3s ease-in-out infinite 1.8s;
}

.clock-message i {
  font-size: 1.2rem;
  animation: hand-wave 2s ease-in-out infinite;
}

@keyframes clock-fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse-text {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

@keyframes hand-wave {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(15deg); }
  75% { transform: rotate(-10deg); }
}

.clock-fade-enter-active { transition: all 0.6s ease-out; }
.clock-fade-leave-active { transition: all 0.3s ease-in; }
.clock-fade-enter-from { opacity: 0; }
.clock-fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .clock-time { font-size: 4rem; }
  .clock-date { font-size: 1.1rem; }
}
</style>
