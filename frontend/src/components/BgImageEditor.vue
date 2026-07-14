<template>
  <div v-if="isAdmin" class="bg-editor">
    <input ref="fileInput" type="file" accept="image/*" class="d-none" @change="handleUpload">
    <button class="bg-edit-btn" @click="$refs.fileInput.click()" :disabled="uploading" :title="uploading ? 'A enviar...' : 'Trocar imagem de fundo'">
      <span v-if="uploading" class="spinner-border spinner-border-sm"></span>
      <i v-else class="bi bi-camera-fill"></i>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const props = defineProps({
  section: { type: String, default: 'auth' },
  imageKey: { type: String, default: 'login_bg_1' },
})

const authStore = useAuthStore()
const uploading = ref(false)
const isAdmin = computed(() => authStore.user?.role === 'admin')

const handleUpload = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  uploading.value = true
  try {
    const ext = file.name.split('.').pop()
    const path = `site/${props.section}_${props.imageKey}_${Date.now()}.${ext}`
    const { error: uploadErr } = await supabase.storage.from('uploads').upload(path, file, { contentType: file.type, upsert: true })
    if (uploadErr) throw uploadErr
    const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(path)
    const imageUrl = urlData?.publicUrl
    if (!imageUrl) throw new Error('No URL')
    const { error: upsertErr } = await supabase.from('site_images').upsert({
      section: props.section,
      key: props.imageKey,
      image_url: imageUrl,
      alt_text: `${props.section} ${props.imageKey}`,
      status: 1,
    }, { onConflict: 'section,key' })
    if (upsertErr) throw upsertErr
    window.location.reload()
  } catch (err) {
    console.error('Upload error:', err)
  } finally {
    uploading.value = false
    e.target.value = ''
  }
}
</script>

<style scoped>
.bg-editor {
  position: fixed;
  bottom: 90px;
  right: 20px;
  z-index: 9999;
}
.bg-edit-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
  transition: all 0.25s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.bg-edit-btn:hover:not(:disabled) {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 24px rgba(245, 158, 11, 0.5);
}
.bg-edit-btn:disabled { opacity: 0.6; cursor: wait; }
</style>
