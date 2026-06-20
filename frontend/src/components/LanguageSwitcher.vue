<template>
  <div class="lang-switcher" ref="switcherRef">
    <button class="lang-btn" @click="open = !open" :title="t('lang.' + locale)">
      <span class="lang-flag">{{ flags[locale] }}</span>
      <span class="lang-code">{{ locale.toUpperCase() }}</span>
      <i class="bi bi-chevron-down" :class="{ 'rotated': open }"></i>
    </button>
    <Transition name="dropdown">
      <div v-if="open" class="lang-dropdown">
        <button
          v-for="lang in languages"
          :key="lang.code"
          class="lang-option"
          :class="{ active: locale === lang.code }"
          @click="selectLang(lang.code)"
        >
          <span class="lang-flag">{{ lang.flag }}</span>
          <span>{{ lang.label }}</span>
          <i v-if="locale === lang.code" class="bi bi-check-lg"></i>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from '@/composables/useI18n.js'

const { locale, t, setLocale } = useI18n()
const open = ref(false)
const switcherRef = ref(null)

const flags = { pt: '🇦🇴', en: '🇬🇧', fr: '🇫🇷' }

const languages = [
  { code: 'pt', label: 'Português', flag: '🇦🇴' },
  { code: 'en', label: 'English', flag: '🇬🇧' },
  { code: 'fr', label: 'Français', flag: '🇫🇷' },
]

const selectLang = (code) => {
  setLocale(code)
  open.value = false
}

const handleClickOutside = (e) => {
  if (switcherRef.value && !switcherRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.lang-switcher { position: relative; }

.lang-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.75rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  color: var(--fml-700);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.lang-btn:hover {
  background: rgba(255, 255, 255, 0.14);
  border-color: var(--fml-gold);
}
.lang-btn i {
  font-size: 0.65rem;
  transition: transform 0.2s;
}
.lang-btn i.rotated { transform: rotate(180deg); }

.lang-flag { font-size: 1.1rem; line-height: 1; }
.lang-code { letter-spacing: 0.5px; }

.lang-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: #fff;
  border: 1px solid var(--fml-200);
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  min-width: 170px;
  z-index: 1050;
  overflow: hidden;
  padding: 0.35rem;
}

.lang-option {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: none;
  background: transparent;
  color: var(--fml-700);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.15s;
}
.lang-option:hover { background: var(--fml-50); color: var(--fml-navy); }
.lang-option.active {
  background: var(--fml-50);
  color: var(--fml-blue-2);
  font-weight: 600;
}
.lang-option i { margin-left: auto; color: var(--fml-blue-2); }

/* dropdown transition */
.dropdown-enter-active { transition: all 0.2s ease; }
.dropdown-leave-active { transition: all 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}

/* mobile: horizontal layout */
@media (max-width: 991px) {
  .lang-dropdown {
    right: auto;
    left: 0;
  }
}
</style>
