<template>
  <Transition name="clock-fade">
    <div v-if="visible" class="inactivity-overlay" @click="dismiss" @mousemove="dismiss" @keydown="dismiss">
      <div class="clock-wrapper">
        <div class="clock-ring"></div>
        <div class="clock-card">
          <div class="clock-brand">
            <div class="brand-icon">
              <i class="bi bi-box-seam"></i>
            </div>
            <span class="brand-name">FMLider</span>
          </div>
          <div class="clock-time">{{ time }}</div>
          <div class="clock-date-line">
            <span class="clock-day">{{ day }}</span>
            <span class="clock-dot"></span>
            <span class="clock-date">{{ date }}</span>
          </div>
          <div class="clock-divider"></div>
          <div class="clock-message">
            <i class="bi bi-cursor-fill"></i>
            {{ t('inactivity.subtitle') }}
          </div>
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
  background: #0c1222;
  background-image:
    radial-gradient(ellipse at 20% 50%, rgba(26, 54, 93, 0.4) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 20%, rgba(212, 175, 55, 0.06) 0%, transparent 50%),
    radial-gradient(ellipse at 50% 80%, rgba(26, 54, 93, 0.25) 0%, transparent 50%);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  user-select: none;
}

.clock-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clock-ring {
  position: absolute;
  width: 340px;
  height: 340px;
  border-radius: 50%;
  border: 1px solid rgba(212, 175, 55, 0.12);
  animation: ring-rotate 20s linear infinite;
}
.clock-ring::before {
  content: '';
  position: absolute;
  top: -3px;
  left: 50%;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #d4af37;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.6);
}

@keyframes ring-rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.clock-card {
  text-align: center;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(212, 175, 55, 0.1);
  border-radius: 24px;
  padding: 2.5rem 3rem;
  position: relative;
  z-index: 1;
}

.clock-brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  margin-bottom: 1.5rem;
}
.brand-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #d4af37, #b8941f);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0c1222;
  font-size: 1rem;
}
.brand-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #d4af37;
  letter-spacing: 3px;
  text-transform: uppercase;
}

.clock-time {
  font-size: 5rem;
  font-weight: 200;
  color: #f1f5f9;
  letter-spacing: 2px;
  line-height: 1;
  font-variant-numeric: tabular-nums;
  text-shadow: 0 0 40px rgba(212, 175, 55, 0.15);
  animation: fade-up 0.8s ease-out;
}

.clock-date-line {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1rem;
  animation: fade-up 0.8s ease-out 0.15s both;
}
.clock-day {
  font-size: 0.8rem;
  color: #d4af37;
  font-weight: 500;
  letter-spacing: 2px;
  text-transform: uppercase;
}
.clock-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: rgba(212, 175, 55, 0.4);
}
.clock-date {
  font-size: 0.85rem;
  color: #94a3b8;
  font-weight: 300;
  letter-spacing: 1px;
}

.clock-divider {
  width: 40px;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
  margin: 1.5rem auto;
  animation: fade-up 0.8s ease-out 0.3s both;
}

.clock-message {
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 400;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  animation: fade-up 0.8s ease-out 0.45s both, pulse-text 3s ease-in-out infinite 1.5s;
}
.clock-message i {
  font-size: 0.9rem;
  color: #d4af37;
  animation: cursor-blink 1.5s ease-in-out infinite;
}

@keyframes fade-up {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse-text {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

@keyframes cursor-blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.clock-fade-enter-active { transition: all 0.5s ease-out; }
.clock-fade-leave-active { transition: all 0.3s ease-in; }
.clock-fade-enter-from { opacity: 0; }
.clock-fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .clock-card { padding: 1.75rem 1.5rem; border-radius: 18px; }
  .clock-time { font-size: 3rem; }
  .clock-ring { width: 240px; height: 240px; }
  .clock-date-line { flex-direction: column; gap: 0.3rem; }
  .clock-dot { display: none; }
}
</style>
