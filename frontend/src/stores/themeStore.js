import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const theme = ref('system')
  const resolvedTheme = ref('blue')
  let mediaQuery = null
  let mediaHandler = null

  try {
    const saved = localStorage.getItem('fmlider_theme')
    if (saved && ['blue', 'dark', 'system'].includes(saved)) {
      theme.value = saved
    }
  } catch (e) {}

  const applyTheme = () => {
    try {
      let effective = theme.value
      if (effective === 'system') {
        effective = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'blue'
      }
      resolvedTheme.value = effective
      document.documentElement.setAttribute('data-theme', effective)
    } catch (e) {}
  }

  const setTheme = (t) => {
    theme.value = t
    try { localStorage.setItem('fmlider_theme', t) } catch (e) {}
    applyTheme()
    updateMediaListener()
  }

  const cycleTheme = () => {
    const order = ['blue', 'dark', 'system']
    const idx = order.indexOf(theme.value)
    setTheme(order[(idx + 1) % order.length])
  }

  const themeIcon = () => {
    if (theme.value === 'dark') return 'bi-moon-stars-fill'
    if (theme.value === 'blue') return 'bi-sun-fill'
    return 'bi-display'
  }

  const themeLabel = () => {
    if (theme.value === 'dark') return 'Escuro'
    if (theme.value === 'blue') return 'Claro'
    return 'Sistema'
  }

  const updateMediaListener = () => {
    if (typeof window === 'undefined') return
    if (mediaQuery && mediaHandler) {
      mediaQuery.removeEventListener('change', mediaHandler)
      mediaQuery = null
      mediaHandler = null
    }
    if (theme.value === 'system') {
      mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      mediaHandler = () => applyTheme()
      mediaQuery.addEventListener('change', mediaHandler)
    }
  }

  applyTheme()
  updateMediaListener()

  return { theme, resolvedTheme, applyTheme, setTheme, cycleTheme, themeIcon, themeLabel }
})
