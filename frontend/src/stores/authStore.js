import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { supabase } from '@/lib/supabase'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('supabase_access_token'))
  const photoHistory = ref([])
  const session = ref(null)

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

  const authHeader = () => ({ headers: { Authorization: `Bearer ${token.value}` } })

  const persistSession = (supabaseSession) => {
    if (supabaseSession) {
      token.value = supabaseSession.access_token
      localStorage.setItem('supabase_access_token', supabaseSession.access_token)
      localStorage.setItem('supabase_refresh_token', supabaseSession.refresh_token)
    } else {
      token.value = null
      localStorage.removeItem('supabase_access_token')
      localStorage.removeItem('supabase_refresh_token')
    }
  }

  const persistUser = () => {
    if (user.value) {
      localStorage.setItem('user', JSON.stringify(user.value))
    } else {
      localStorage.removeItem('user')
    }
  }

  const buildUserFromMetadata = (supaUser) => {
    const metadata = supaUser.user_metadata || {}
    return {
      id: supaUser.id,
      email: supaUser.email,
      username: metadata.username || null,
      name: metadata.name || supaUser.email,
      phone: metadata.phone || null,
      role: metadata.role || 'cliente',
      position: metadata.position || null,
      permissions: metadata.permissions || [],
      approval_status: metadata.approval_status || 'approved',
      company_completed: metadata.company_completed ?? true,
      photo: metadata.photo || null,
      must_change_password: metadata.must_change_password ?? false,
      password_changed_at: metadata.password_changed_at || null,
      locked_at: metadata.locked_at || null,
      locked_reason: metadata.locked_reason || null,
      created_at: supaUser.created_at,
    }
  }

  const login = async (email, password) => {
    try {
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password,
      })

      if (error) {
        const msg = error.message === 'Invalid login credentials'
          ? 'Credenciais inválidas'
          : error.message
        return { success: false, error: msg }
      }

      session.value = data.session
      persistSession(data.session)

      user.value = buildUserFromMetadata(data.user)
      persistUser()

      return {
        success: true,
        user: user.value,
        mustChangePassword: !!user.value.must_change_password,
      }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const logout = async () => {
    user.value = null
    token.value = null
    session.value = null
    photoHistory.value = []
    localStorage.removeItem('supabase_access_token')
    localStorage.removeItem('supabase_refresh_token')
    localStorage.removeItem('user')
    try { supabase.auth.signOut({ scope: 'local' }) } catch (e) {}
  }

  const register = async (payload) => {
    try {
      const { data, error } = await supabase.auth.signUp({
        email: payload.email,
        password: payload.password,
        options: {
          data: {
            username: payload.username,
            name: payload.name,
            phone: payload.phone || '',
            role: 'cliente',
            approval_status: 'pending',
            company_completed: false,
          },
        },
      })

      if (error) {
        const msg = error.message.includes('already registered')
          ? 'Já existe uma conta com este email'
          : error.message
        return { success: false, error: msg }
      }

      return {
        success: true,
        data: { user_id: data.user?.id, email: payload.email },
        message: 'Conta criada. Aguarde aprovação do administrador para aceder ao dashboard.',
      }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const resetPassword = async (email) => {
    try {
      const { error } = await supabase.auth.resetPasswordForEmail(email, {
        redirectTo: `${window.location.origin}/redefinir-senha`,
      })
      if (error) return { success: false, error: error.message }
      return { success: true, message: 'Se o email existir, receberá instruções para redefinir a senha.' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const getProfile = async () => {
    try {
      const { data: { user: supaUser }, error } = await supabase.auth.getUser()

      if (error || !supaUser) {
        return { success: false, error: error?.message || 'Sessão expirada' }
      }

      user.value = buildUserFromMetadata(supaUser)
      persistUser()

      return { success: true, user: user.value, company: null }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const updateProfile = async (payload) => {
    try {
      const { error } = await supabase.auth.updateUser({
        data: { name: payload.name, phone: payload.phone },
      })
      if (error) return { success: false, error: error.message }
      await getProfile()
      return { success: true, message: 'Perfil atualizado' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const changePassword = async (payload) => {
    try {
      const { error } = await supabase.auth.updateUser({ password: payload.new_password })
      if (error) return { success: false, error: error.message }
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
      const apiBase = import.meta.env.VITE_API_URL || ''
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
      const { data: { session: supaSession }, error } = await supabase.auth.getSession()
      if (error || !supaSession) {
        user.value = null
        token.value = null
        session.value = null
        localStorage.removeItem('supabase_access_token')
        localStorage.removeItem('supabase_refresh_token')
        localStorage.removeItem('user')
        return
      }
      session.value = supaSession
      persistSession(supaSession)
      user.value = buildUserFromMetadata(supaSession.user)
      persistUser()
    } catch (err) {
      user.value = null
      token.value = null
      session.value = null
      localStorage.removeItem('supabase_access_token')
      localStorage.removeItem('supabase_refresh_token')
      localStorage.removeItem('user')
    }
  }

  return {
    user,
    token,
    session,
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
