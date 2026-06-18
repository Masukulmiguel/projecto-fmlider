import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from './authStore'

export const useCompanyStore = defineStore('company', () => {
  const company = ref(null)
  const isCompleted = ref(false)
  const loading = ref(false)

  const fetch = async () => {
    const auth = useAuthStore()
    if (!auth.user?.id) return
    loading.value = true
    try {
      const { data, error } = await supabase
        .from('companies')
        .select('*')
        .eq('user_id', auth.user.id)
        .single()

      if (error || !data) {
        company.value = null
        isCompleted.value = false
        return
      }
      company.value = data
      isCompleted.value = !!data.is_completed
    } catch (e) {
      company.value = null
      isCompleted.value = false
    } finally {
      loading.value = false
    }
  }

  const save = async (payload) => {
    const auth = useAuthStore()
    if (!auth.user?.id) return { success: false, error: 'Não autenticado' }

    try {
      const exists = !!company.value
      if (exists) {
        const updateData = { ...payload, is_completed: 1 }
        const { error } = await supabase
          .from('companies')
          .update(updateData)
          .eq('user_id', auth.user.id)
        if (error) throw error
      } else {
        const insertData = { ...payload, user_id: auth.user.id, is_completed: 1, is_published: 0 }
        const { error } = await supabase
          .from('companies')
          .insert(insertData)
        if (error) throw error
      }
      await fetch()
      return { success: true, message: exists ? 'Dados da empresa atualizados' : 'Empresa configurada. Bem-vindo ao seu dashboard.' }
    } catch (error) {
      return { success: false, error: error.message || 'Erro ao guardar' }
    }
  }

  const uploadLogo = async (file) => {
    const auth = useAuthStore()
    if (!auth.user?.id) return { success: false, error: 'Não autenticado' }
    try {
      const ext = file.name.split('.').pop()
      const fileName = `company-logos/${auth.user.id}_${Date.now()}.${ext}`
      const { error: uploadError } = await supabase.storage
        .from('uploads')
        .upload(fileName, file)
      if (uploadError) throw uploadError

      const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
      const logoUrl = urlData.publicUrl

      if (company.value) {
        const { error } = await supabase
          .from('companies')
          .update({ logo: logoUrl })
          .eq('user_id', auth.user.id)
        if (error) throw error
      }
      await fetch()
      return { success: true, logo: logoUrl }
    } catch (error) {
      return { success: false, error: error.message || 'Erro ao enviar logo' }
    }
  }

  const togglePublish = async (isPublished) => {
    const auth = useAuthStore()
    if (!auth.user?.id) return { success: false, error: 'Não autenticado' }
    try {
      const { error } = await supabase
        .from('companies')
        .update({ is_published: !!isPublished })
        .eq('user_id', auth.user.id)
      if (error) throw error
      await fetch()
      return { success: true, is_published: !!isPublished }
    } catch (error) {
      return { success: false, error: error.message || 'Erro ao atualizar' }
    }
  }

  const clear = () => {
    company.value = null
    isCompleted.value = false
  }

  return { company, isCompleted, loading, fetch, save, uploadLogo, togglePublish, clear }
})
