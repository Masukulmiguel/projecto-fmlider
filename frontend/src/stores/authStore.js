import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!token.value)

  const login = async (email, password) => {
    try {
      const response = await axios.post('http://localhost:8000/api/auth/login', {
        email,
        password
      })
      
      if (response.data.success) {
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('token', token.value)
        localStorage.setItem('user', JSON.stringify(user.value))
        return { success: true }
      }
    } catch (error) {
      return { success: false, error: error.message }
    }
  }

  const logout = () => {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  const register = async (name, email, password) => {
    try {
      const response = await axios.post('http://localhost:8000/api/auth/register', {
        name,
        email,
        password
      })
      
      if (response.data.success) {
        return { success: true }
      }
    } catch (error) {
      return { success: false, error: error.message }
    }
  }

  const resetPassword = async (email) => {
    try {
      const response = await axios.post('http://localhost:8000/api/auth/forgot-password', {
        email
      })
      
      if (response.data.success) {
        return { success: true }
      }
    } catch (error) {
      return { success: false, error: error.message }
    }
  }

  const getProfile = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/auth/profile', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      
      if (response.data.success) {
        user.value = response.data.user
        return { success: true, user: response.data.user }
      }
    } catch (error) {
      return { success: false, error: error.message }
    }
  }

  // Load user from localStorage on initialization
  if (localStorage.getItem('user')) {
    user.value = JSON.parse(localStorage.getItem('user'))
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout,
    register,
    resetPassword,
    getProfile
  }
})
