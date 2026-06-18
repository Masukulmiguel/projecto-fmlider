import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import './assets/styles.css'
import { useAuthStore } from './stores/authStore'
import { vReveal } from './directives/vReveal'

axios.defaults.baseURL = import.meta.env.VITE_API_URL || ''
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Accept'] = 'application/json'

axios.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('supabase_access_token')
  if (token && !config.headers.Authorization) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    return Promise.reject(error)
  }
)

const app = createApp(App)
app.directive('reveal', vReveal)
app.config.globalProperties.$axios = axios
const pinia = createPinia()
app.use(pinia)

const authStore = useAuthStore()
authStore.initSession().then(() => {
  app.use(router)
  app.mount('#app')

  window.addEventListener('beforeunload', () => {
    sessionStorage.removeItem('supabase_access_token')
    sessionStorage.removeItem('supabase_refresh_token')
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('fmlider_auth')
    try { authStore.logout() } catch (e) {}
  })
})
