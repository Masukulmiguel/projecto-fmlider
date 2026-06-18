import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { supabase } from '@/lib/supabase'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(sessionStorage.getItem('supabase_access_token'))
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
      sessionStorage.setItem('supabase_access_token', supabaseSession.access_token)
      sessionStorage.setItem('supabase_refresh_token', supabaseSession.refresh_token)
    } else {
      token.value = null
      sessionStorage.removeItem('supabase_access_token')
      sessionStorage.removeItem('supabase_refresh_token')
    }
  }

  const persistUser = () => {
    if (user.value) {
      sessionStorage.setItem('user', JSON.stringify(user.value))
    } else {
      sessionStorage.removeItem('user')
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
      approval_status: metadata.approval_status || 'pending',
      company_completed: metadata.company_completed ?? false,
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

      const metaUser = buildUserFromMetadata(data.user)

      const { data: dbUser, error: dbError } = await supabase
        .from('users')
        .select('*')
        .eq('auth_id', data.user.id)
        .single()

      if (dbError || !dbUser) {
        console.error('authStore: DB query failed on login:', dbError?.message)
        user.value = metaUser
      } else {
        user.value = {
          id: dbUser.id,
          auth_id: data.user.id,
          email: dbUser.email || data.user.email,
          username: dbUser.username || metaUser.username,
          name: dbUser.name || metaUser.name,
          phone: dbUser.phone || metaUser.phone,
          role: dbUser.role || metaUser.role,
          position: dbUser.position || metaUser.position,
          permissions: dbUser.permissions || metaUser.permissions,
          approval_status: dbUser.approval_status || metaUser.approval_status,
          company_completed: metaUser.company_completed,
          photo: dbUser.photo || metaUser.photo,
          must_change_password: dbUser.password_must_change || metaUser.must_change_password,
          password_changed_at: dbUser.password_changed_at || metaUser.password_changed_at,
          locked_at: dbUser.locked_at || metaUser.locked_at,
          locked_reason: dbUser.locked_reason || metaUser.locked_reason,
          created_at: dbUser.created_at || data.user.created_at,
        }
      }
      persistUser()

      return { success: true, user: user.value, company: null }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const logout = async () => {
    user.value = null
    token.value = null
    session.value = null
    photoHistory.value = []
    sessionStorage.removeItem('supabase_access_token')
    sessionStorage.removeItem('supabase_refresh_token')
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('fmlider_auth')
    localStorage.removeItem('fmlider_auth')
    localStorage.removeItem('supabase_access_token')
    localStorage.removeItem('supabase_refresh_token')
    localStorage.removeItem('user')
    try { await supabase.auth.signOut({ scope: 'local' }) } catch (e) {}
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

      if (data.user?.id) {
        await supabase.from('users').upsert({
          auth_id: data.user.id,
          username: payload.username,
          name: payload.name,
          email: payload.email,
          phone: payload.phone || '',
          role: 'cliente',
          approval_status: 'pending',
          password: 'supabase_auth_managed',
        }, { onConflict: 'auth_id', ignoreDuplicates: true })
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

      const metaUser = buildUserFromMetadata(supaUser)

      const { data: dbUser, error: dbError } = await supabase
        .from('users')
        .select('*')
        .eq('auth_id', supaUser.id)
        .single()

      if (dbError || !dbUser) {
        console.error('authStore: DB query failed on getProfile:', dbError?.message)
        user.value = metaUser
      } else {
        user.value = {
          id: dbUser.id,
          auth_id: supaUser.id,
          email: dbUser.email || supaUser.email,
          username: dbUser.username || metaUser.username,
          name: dbUser.name || metaUser.name,
          phone: dbUser.phone || metaUser.phone,
          role: dbUser.role || metaUser.role,
          position: dbUser.position || metaUser.position,
          permissions: dbUser.permissions || metaUser.permissions,
          approval_status: dbUser.approval_status || metaUser.approval_status,
          company_completed: metaUser.company_completed,
          photo: dbUser.photo || metaUser.photo,
          must_change_password: dbUser.password_must_change || metaUser.must_change_password,
          password_changed_at: dbUser.password_changed_at || metaUser.password_changed_at,
          locked_at: dbUser.locked_at || metaUser.locked_at,
          locked_reason: dbUser.locked_reason || metaUser.locked_reason,
          created_at: dbUser.created_at || supaUser.created_at,
        }
      }
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

      if (user.value?.auth_id) {
        await supabase.from('users').update({
          name: payload.name,
          phone: payload.phone,
        }).eq('auth_id', user.value.auth_id)
      }

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
        if (user.value.auth_id) {
          await supabase.from('users').update({
            password_must_change: false,
            password_changed_at: new Date().toISOString(),
          }).eq('auth_id', user.value.auth_id)
        }
      }
      return { success: true, message: 'Senha alterada com sucesso' }
    } catch (err) {
      return { success: false, error: err.message }
    }
  }

  const uploadPhoto = async (file) => {
    try {
      const ext = file.name.split('.').pop()
      const fileName = `photos/${user.value?.id || 'unknown'}_${Date.now()}.${ext}`
      const { error: uploadError } = await supabase.storage
        .from('photos')
        .upload(fileName, file, { upsert: true })
      if (uploadError) throw uploadError

      const { data: urlData } = supabase.storage.from('photos').getPublicUrl(fileName)
      const photoUrl = urlData.publicUrl

      if (user.value?.auth_id) {
        await supabase.from('users').update({ photo: photoUrl }).eq('auth_id', user.value.auth_id)
      }
      if (user.value) {
        user.value = { ...user.value, photo: photoUrl }
        persistUser()
      }
      return { success: true, photo: photoUrl, message: 'Foto atualizada' }
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
      const storedToken = sessionStorage.getItem('supabase_access_token')
      const storedUser = sessionStorage.getItem('user')

      if (!storedToken || !storedUser) {
        user.value = null
        token.value = null
        session.value = null
        sessionStorage.clear()
        localStorage.removeItem('fmlider_auth')
        return
      }

      const { data: { session: supaSession }, error } = await supabase.auth.getSession()
      if (error || !supaSession) {
        user.value = null
        token.value = null
        session.value = null
        sessionStorage.clear()
        localStorage.removeItem('fmlider_auth')
        return
      }

      session.value = supaSession
      persistSession(supaSession)

      const metaUser = buildUserFromMetadata(supaSession.user)

      const { data: dbUser, error: dbError } = await supabase
        .from('users')
        .select('*')
        .eq('auth_id', supaSession.user.id)
        .single()

      if (dbError || !dbUser) {
        console.error('authStore: DB query failed on initSession:', dbError?.message)
        user.value = metaUser
      } else {
        user.value = {
          id: dbUser.id,
          auth_id: supaSession.user.id,
          email: dbUser.email || supaSession.user.email,
          username: dbUser.username || metaUser.username,
          name: dbUser.name || metaUser.name,
          phone: dbUser.phone || metaUser.phone,
          role: dbUser.role || metaUser.role,
          position: dbUser.position || metaUser.position,
          permissions: dbUser.permissions || metaUser.permissions,
          approval_status: dbUser.approval_status || metaUser.approval_status,
          company_completed: metaUser.company_completed,
          photo: dbUser.photo || metaUser.photo,
          must_change_password: dbUser.password_must_change || metaUser.must_change_password,
          password_changed_at: dbUser.password_changed_at || metaUser.password_changed_at,
          locked_at: dbUser.locked_at || metaUser.locked_at,
          locked_reason: dbUser.locked_reason || metaUser.locked_reason,
          created_at: dbUser.created_at || supaSession.user.created_at,
        }
      }
      persistUser()
    } catch (err) {
      user.value = null
      token.value = null
      session.value = null
      sessionStorage.clear()
      localStorage.removeItem('fmlider_auth')
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
