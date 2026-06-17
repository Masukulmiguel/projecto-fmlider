import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('fmlider_token'))
  const photoHistory = ref([])

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isFuncionario = computed(() => user.value?.role === 'funcionario')
  const isCliente = computed(() => user.value?.role === 'cliente')
  const isPending = computed(() => user.value?.approval_status === 'pending')
  const isRejected = computed(() => user.value?.approval_status === 'rejected')
  const companyCompleted = computed(() => !!user.value?.company_completed)
  const permissions = computed(() => user.value?.permissions || [])

  const can = (perm) => {
    if (isAdmin.value) return true
    return permissions.value.includes(perm)
  }

  const apiBase = import.meta.env.VITE_API_URL || ''
  const authFetch = async (path, opts = {}) => {
    const headers = { 'Content-Type': 'application/json', ...opts.headers }
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`
    const res = await fetch(`${apiBase}/api${path}`, { ...opts, headers })
    return res.json()
  }

  const persistUser = () => {
    if (user.value) {
      localStorage.setItem('user', JSON.stringify(user.value))
    } else {
      localStorage.removeItem('user')
    }
  }

  const setUserFromDB = (dbUser) => {
    user.value = {
      id: dbUser.id,
      username: dbUser.username,
      name: dbUser.name,
      email: dbUser.email,
      phone: dbUser.phone,
      role: dbUser.role,
      position: dbUser.position || null,
      permissions: dbUser.permissions || [],
      approval_status: dbUser.approval_status || 'pending',
      company_completed: dbUser.company_completed ?? true,
      photo: dbUser.photo || null,
      must_change_password: dbUser.password_must_change ?? false,
      password_changed_at: dbUser.password_changed_at || null,
      locked_at: dbUser.locked_at || null,
      locked_reason: dbUser.locked_reason || null,
      created_at: dbUser.created_at,
    }
    persistUser()
  }

  const login = async (email, password) => {
    try {
      const json = await authFetch('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      })
      if (!json.success) {
        return { success: false, error: json.message || 'Credenciais inválidas' }
      }
      const u = json.data.user
      token.value = json.data.token
      localStorage.setItem('fmlider_token', json.data.token)
      setUserFromDB(u)
      return { success: true }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const logout = async () => {
    try { await authFetch('/auth/logout', { method: 'POST' }) } catch (e) {}
    user.value = null
    token.value = null
    photoHistory.value = []
    localStorage.removeItem('fmlider_token')
    localStorage.removeItem('supabase_access_token')
    localStorage.removeItem('supabase_refresh_token')
    localStorage.removeItem('user')
  }

  const register = async (payload) => {
    try {
      const json = await authFetch('/auth/register', {
        method: 'POST',
        body: JSON.stringify({
          username: payload.username,
          name: payload.name,
          email: payload.email,
          phone: payload.phone || '',
          password: payload.password,
          password_confirm: payload.password,
        }),
      })
      if (!json.success) {
        return { success: false, error: json.message || 'Erro ao criar conta' }
      }
      return {
        success: true,
        data: { user_id: json.data?.user_id, email: payload.email },
        message: json.message || 'Conta criada. Aguarde aprovação do administrador para aceder ao dashboard.',
      }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const resetPassword = async (email) => {
    try {
      const json = await authFetch('/auth/forgot-password', {
        method: 'POST',
        body: JSON.stringify({ email }),
      })
      if (!json.success) return { success: false, error: json.message }
      return { success: true, message: json.message || 'Se o email existir, receberá instruções para redefinir a senha.' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const getProfile = async () => {
    try {
      const json = await authFetch('/auth/profile')
      if (!json.success || !json.data?.user) {
        return { success: false, error: json.message || 'Sessão expirada' }
      }
      const dbUser = json.data.user
      setUserFromDB(dbUser)
      if (json.data.company) {
        user.value.company_completed = true
      }
      photoHistory.value = json.data.photo_history || []
      return { success: true, user: user.value, company: json.data.company }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const updateProfile = async (payload) => {
    try {
      const json = await authFetch('/auth/profile', {
        method: 'PUT',
        body: JSON.stringify({ name: payload.name, phone: payload.phone }),
      })
      if (!json.success) return { success: false, error: json.message }
      await getProfile()
      return { success: true, message: 'Perfil atualizado' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const changePassword = async (payload) => {
    try {
      const json = await authFetch('/auth/change-password', {
        method: 'POST',
        body: JSON.stringify({
          current_password: payload.current_password || '',
          new_password: payload.new_password,
          new_password_confirmation: payload.new_password,
        }),
      })
      if (!json.success) return { success: false, error: json.message }
      if (user.value) {
        user.value = { ...user.value, must_change_password: false, password_changed_at: new Date().toISOString() }
        persistUser()
      }
      return { success: true, message: 'Senha alterada com sucesso' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const uploadPhoto = async (file) => {
    try {
      const fd = new FormData()
      fd.append('photo', file)
      const res = await fetch(`${apiBase}/api/auth/upload-photo`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: fd,
      })
      const json = await res.json()
      if (!json.success) return { success: false, error: json.message || 'Erro ao enviar foto' }

      const photoUrl = json.data.photo
      if (user.value) {
        user.value = { ...user.value, photo: photoUrl }
        persistUser()
      }
      return { success: true, photo: photoUrl, message: json.message || 'Foto atualizada' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const lockUser = async (id, payload = {}) => {
    return { success: false, error: 'Funcionalidade indisponível com Supabase Auth' }
  }

  const unlockUser = async (id) => {
    return { success: false, error: 'Funcionalidade indisponível com Supabase Auth' }
  }

  const setUser = (u) => {
    user.value = u
    persistUser()
  }

  const initSession = async () => {
    try {
      if (!token.value) {
        user.value = null
        return
      }
      const json = await authFetch('/auth/profile')
      if (!json.success || !json.data?.user) {
        user.value = null
        token.value = null
        localStorage.removeItem('fmlider_token')
        localStorage.removeItem('user')
        return
      }
      setUserFromDB(json.data.user)
      if (json.data.company) {
        user.value.company_completed = true
      }
      photoHistory.value = json.data.photo_history || []
    } catch (err) {
      user.value = null
      token.value = null
      localStorage.removeItem('fmlider_token')
      localStorage.removeItem('user')
    }
  }

  return {
    user,
    token,
    photoHistory,
    isAuthenticated,
    isAdmin,
    isFuncionario,
    isCliente,
    isPending,
    isRejected,
    companyCompleted,
    permissions,
    can,
    login,
    logout,
    register,
    resetPassword,
    getProfile,
    updateProfile,
    changePassword,
    uploadPhoto,
    lockUser,
    unlockUser,
    setUser,
    initSession,
  }
})
