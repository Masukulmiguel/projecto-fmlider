import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from './authStore'

export const useCompanyStore = defineStore('company', () => {
  const company = ref(null)
  const isCompleted = ref(false)
  const loading = ref(false)

  const authHeader = () => {
    const auth = useAuthStore()
    return { headers: { Authorization: `Bearer ${auth.token}` } }
  }

  const fetch = async () => {
    const auth = useAuthStore()
    if (!auth.token) return
    loading.value = true
    try {
      const response = await axios.get('/api/company', authHeader())
      if (response.data.success) {
        company.value = response.data.data.company
        isCompleted.value = !!response.data.data.is_completed
      }
    } catch (error) {
      company.value = null
      isCompleted.value = false
    } finally {
      loading.value = false
    }
  }

  const save = async (payload) => {
    try {
      const exists = !!company.value
      const method = exists ? 'put' : 'post'
      const response = await axios[method]('/api/company', payload, authHeader())
      if (response.data.success) {
        await fetch()
        return { success: true, message: response.data.message }
      }
    } catch (error) {
      const data = error.response?.data?.data || {}
      return { success: false, error: error.response?.data?.message || error.message, fields: data }
    }
  }

  const uploadLogo = async (file) => {
    try {
      const fd = new FormData()
      fd.append('logo', file)
      const response = await axios.post('/api/company/logo', fd, {
        ...authHeader(),
        headers: { ...authHeader().headers, 'Content-Type': 'multipart/form-data' }
      })
      if (response.data.success) {
        await fetch()
        return { success: true, logo: response.data.data.logo }
      }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || error.message }
    }
  }

  const togglePublish = async (isPublished) => {
    try {
      const response = await axios.post('/api/company/publish', { is_published: !!isPublished }, authHeader())
      if (response.data.success) {
        await fetch()
        return { success: true, is_published: response.data.data.is_published }
      }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || error.message }
    }
  }

  const clear = () => {
    company.value = null
    isCompleted.value = false
  }

  return { company, isCompleted, loading, fetch, save, uploadLogo, togglePublish, clear }
})
