import { ref } from 'vue'
import pt from '@/locales/pt.js'
import en from '@/locales/en.js'
import fr from '@/locales/fr.js'

const locale = ref(localStorage.getItem('fmlider_locale') || 'pt')
const messages = { pt, en, fr }

export function useI18n() {
  const setLocale = (l) => {
    if (messages[l]) {
      locale.value = l
      localStorage.setItem('fmlider_locale', l)
      document.documentElement.lang = l
    }
  }

  const cycleLocale = () => {
    const langs = ['pt', 'en', 'fr']
    const idx = langs.indexOf(locale.value)
    setLocale(langs[(idx + 1) % langs.length])
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

  return { locale, t, setLocale, cycleLocale }
}
