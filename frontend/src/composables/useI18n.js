import { ref, computed } from 'vue'
import pt from '@/locales/pt.js'
import en from '@/locales/en.js'

const locale = ref(localStorage.getItem('fmlider_locale') || 'pt')
const messages = { pt, en }

export function useI18n() {
  const setLocale = (l) => {
    locale.value = l
    localStorage.setItem('fmlider_locale', l)
  }

  const toggleLocale = () => {
    setLocale(locale.value === 'pt' ? 'en' : 'pt')
  }

  const t = (path) => {
    const keys = path.split('.')
    let val = messages[locale.value]
    for (const k of keys) {
      if (val && typeof val === 'object') val = val[k]
      else return path
    }
    return val || path
  }

  return { locale, t, setLocale, toggleLocale }
}
