<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0"><i class="bi bi-gear-fill me-2"></i>{{ t('admin.settings_title') }}</h4>
      <button class="btn btn-primary" @click="saveSettings" :disabled="saving">
        <i class="bi bi-check-lg me-1" v-if="!saving"></i>
        <span class="spinner-border spinner-border-sm me-1" v-else></span>
        {{ saving ? t('common.loading') : t('admin.settings_save') }}
      </button>
    </div>

    <div v-if="message" :class="`alert alert-${message.type} alert-dismissible fade show`">
      {{ message.text }}
      <button type="button" class="btn-close" @click="message = null"></button>
    </div>

    <div class="row g-4" v-if="!loading">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-building me-2"></i>{{ t('admin.settings_company') }}</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_company_name') }}</label>
              <input v-model="form.company_name" type="text" class="form-control" :placeholder="t('admin.settings_name_placeholder')">
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_description') }}</label>
              <textarea v-model="form.company_description" class="form-control" rows="3" :placeholder="t('admin.settings_desc_placeholder')"></textarea>
            </div>
            <div class="mb-0">
              <label class="form-label">NIF</label>
              <input v-model="form.nif" type="text" class="form-control" :placeholder="t('admin.settings_nif_placeholder')">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>{{ t('admin.settings_contacts') }}</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_phone') }}</label>
              <input v-model="form.phone" type="text" class="form-control" placeholder="+244 9XX XXX XXX">
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_email') }}</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="email@exemplo.com">
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_address') }}</label>
              <input v-model="form.address" type="text" class="form-control" :placeholder="t('admin.settings_address_placeholder')">
            </div>
            <div class="mb-0">
              <label class="form-label">{{ t('admin.settings_working_hours') }}</label>
              <input v-model="form.working_hours" type="text" class="form-control" placeholder="Seg–Sex 08:00–18:00">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-share me-2"></i>{{ t('admin.settings_social') }}</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Facebook</label>
              <input v-model="form.facebook" type="url" class="form-control" placeholder="https://facebook.com/...">
            </div>
            <div class="mb-3">
              <label class="form-label">Instagram</label>
              <input v-model="form.instagram" type="url" class="form-control" placeholder="https://instagram.com/...">
            </div>
            <div class="mb-3">
              <label class="form-label">LinkedIn</label>
              <input v-model="form.linkedin" type="url" class="form-control" placeholder="https://linkedin.com/...">
            </div>
            <div class="mb-0">
              <label class="form-label">WhatsApp</label>
              <input v-model="form.whatsapp" type="text" class="form-control" placeholder="244935141747">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-search me-2"></i>{{ t('admin.settings_seo') }}</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.settings_meta_title') }}</label>
              <input v-model="form.meta_title" type="text" class="form-control" :placeholder="t('admin.settings_meta_title_placeholder')">
            </div>
            <div class="mb-0">
              <label class="form-label">{{ t('admin.settings_meta_description') }}</label>
              <textarea v-model="form.meta_description" class="form-control" rows="3" :placeholder="t('admin.settings_meta_desc_placeholder')"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="mt-3 text-muted">{{ t('common.loading') }}</p>
    </div>

    <!-- Auth Background Images -->
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-image me-2"></i>Imagens de Fundo (Auth)</h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Troque as imagens de fundo das páginas de login, registo e redefinição de senha.</p>
        <div class="row g-3">
          <div v-for="item in authImages" :key="item.key" class="col-6 col-md-4 col-lg-3">
            <div class="auth-bg-card">
              <div class="auth-bg-preview" :style="{ backgroundImage: `url(${item.url})` }">
                <div class="auth-bg-label">{{ item.label }}</div>
              </div>
              <div class="auth-bg-actions">
                <input :ref="el => { if (el) fileRefs[item.key] = el }" type="file" accept="image/*" class="d-none" @change="e => uploadBg(item.key, 'auth', e)">
                <button class="btn btn-sm btn-outline-primary w-100" @click="fileRefs[item.key]?.click()" :disabled="uploadingKey === item.key">
                  <span v-if="uploadingKey === item.key" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-camera-fill me-1"></i>
                  {{ uploadingKey === item.key ? 'A enviar...' : 'Trocar Imagem' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Service Detail Hero Backgrounds -->
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Imagens de Fundo (Serviços)</h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Imagens de fundo do hero de cada página de detalhe do serviço.</p>
        <div class="row g-3">
          <div v-for="item in serviceImages" :key="item.key" class="col-6 col-md-4 col-lg-3">
            <div class="auth-bg-card">
              <div class="auth-bg-preview" :style="{ backgroundImage: `url(${item.url})` }">
                <div class="auth-bg-label">{{ item.label }}</div>
              </div>
              <div class="auth-bg-actions">
                <input :ref="el => { if (el) fileRefs[item.key] = el }" type="file" accept="image/*" class="d-none" @change="e => uploadBg(item.key, 'service_detail', e)">
                <button class="btn btn-sm btn-outline-primary w-100" @click="fileRefs[item.key]?.click()" :disabled="uploadingKey === item.key">
                  <span v-if="uploadingKey === item.key" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-camera-fill me-1"></i>
                  {{ uploadingKey === item.key ? 'A enviar...' : 'Trocar Imagem' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Page Background -->
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Imagem de Fundo (Contacto)</h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Imagem de fundo do hero da página de contacto.</p>
        <div class="row g-3">
          <div v-for="item in contactImages" :key="item.key" class="col-6 col-md-4 col-lg-3">
            <div class="auth-bg-card">
              <div class="auth-bg-preview" :style="{ backgroundImage: `url(${item.url})` }">
                <div class="auth-bg-label">{{ item.label }}</div>
              </div>
              <div class="auth-bg-actions">
                <input :ref="el => { if (el) fileRefs[item.key] = el }" type="file" accept="image/*" class="d-none" @change="e => uploadBg(item.key, 'contact', e)">
                <button class="btn btn-sm btn-outline-primary w-100" @click="fileRefs[item.key]?.click()" :disabled="uploadingKey === item.key">
                  <span v-if="uploadingKey === item.key" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-camera-fill me-1"></i>
                  {{ uploadingKey === item.key ? 'A enviar...' : 'Trocar Imagem' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useSiteImages } from '@/composables/useSiteImages'

const { t } = useI18n()
const { invalidate } = useSiteImages()

const loading = ref(true)
const saving = ref(false)
const message = ref(null)

const form = reactive({
  company_name: '',
  company_description: '',
  nif: '',
  phone: '',
  email: '',
  address: '',
  working_hours: '',
  facebook: '',
  instagram: '',
  linkedin: '',
  whatsapp: '',
  meta_title: '',
  meta_description: ''
})

const fileRefs = ref({})
const uploadingKey = ref(null)

const authImages = ref([
  { key: 'login_bg_1', label: 'Login 1', url: '/assets/img/auth/bg1.jpg' },
  { key: 'login_bg_2', label: 'Login 2', url: '/assets/img/auth/bg2.jpg' },
  { key: 'login_bg_3', label: 'Login 3', url: '/assets/img/auth/bg3.jpg' },
  { key: 'reset_bg', label: 'Redefinir Senha', url: '/assets/img/auth/reset_bg.jpg' },
  { key: 'forgot_bg', label: 'Esqueci Senha', url: '/assets/img/auth/bg3.jpg' },
])

const serviceImages = ref([
  { key: 'desembaraco-aduaneiro', label: 'Desembaraço Aduaneiro', url: '/assets/img/servico/Desembaraço Aduaneiro.jpeg' },
  { key: 'transportes', label: 'Transportes', url: '/assets/img/servico/Transportes.jpg' },
  { key: 'armazenagem', label: 'Armazenagem', url: '/assets/img/servico/service-storage.jpg' },
  { key: 'door-to-door', label: 'Door To Door', url: '/assets/img/servico/service-door.jpg' },
  { key: 'logistica-maritima', label: 'Logística Marítima', url: '/assets/img/servico/Logística Marítima-1.jpg' },
])

const contactImages = ref([
  { key: 'hero_bg', label: 'Hero Contacto', url: '/assets/img/contact/hero_bg.jpg' },
])

const loadImages = async () => {
  const { data, error } = await supabase.from('site_images').select('section, key, image_url')
  if (error) {
    console.error('loadImages error:', error)
    return
  }
  if (!data) return
  data.forEach(row => {
    if (row.section === 'auth') {
      const img = authImages.value.find(i => i.key === row.key)
      if (img && row.image_url) img.url = row.image_url
    }
    if (row.section === 'service_detail') {
      const img = serviceImages.value.find(i => i.key === row.key)
      if (img && row.image_url) img.url = row.image_url
    }
    if (row.section === 'contact') {
      const img = contactImages.value.find(i => i.key === row.key)
      if (img && row.image_url) img.url = row.image_url
    }
  })
}

const fileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

const uploadBg = async (key, section, e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    message.value = { type: 'danger', text: 'Imagem muito grande. Máximo 2MB.' }
    return
  }
  uploadingKey.value = key
  try {
    const base64 = await fileToBase64(file)
    const { error: upsertErr } = await supabase.from('site_images').upsert({
      section,
      key,
      image_url: base64,
      alt_text: key,
      status: 1,
    }, { onConflict: 'section,key' })
    if (upsertErr) throw upsertErr
    const allImages = [...authImages.value, ...serviceImages.value, ...contactImages.value]
    const img = allImages.find(i => i.key === key)
    if (img) img.url = base64
    invalidate()
    message.value = { type: 'success', text: `Imagem "${key}" atualizada com sucesso!` }
  } catch (err) {
    console.error('uploadBg error:', err)
    message.value = { type: 'danger', text: err.message || 'Erro ao enviar imagem.' }
  } finally {
    uploadingKey.value = null
    e.target.value = ''
  }
}

const fetchSettings = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('settings').select('*')
    if (!error && data) {
      data.forEach(row => {
        if (row.key in form) {
          form[row.key] = row.value || ''
        }
      })
    }
  } catch (e) {
    message.value = { type: 'danger', text: t('admin.settings_error_loading') }
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = null
  try {
    const rows = Object.entries(form).map(([key, value]) => ({ key, value }))
    const { error } = await supabase.from('settings').upsert(rows, { onConflict: 'key' })
    if (error) throw error
    message.value = { type: 'success', text: t('admin.settings_saved') }
  } catch (e) {
    message.value = { type: 'danger', text: e.message || t('admin.settings_error_saving') }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSettings()
  loadImages()
})
</script>

<style scoped>
.auth-bg-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
}
.auth-bg-card:hover {
  border-color: #93c5fd;
  box-shadow: 0 2px 12px rgba(59, 130, 246, 0.1);
}
.auth-bg-preview {
  height: 140px;
  background-size: cover;
  background-position: center;
  position: relative;
}
.auth-bg-label {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0.4rem 0.75rem;
  background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
}
.auth-bg-actions {
  padding: 0.5rem;
  background: #fff;
}
</style>
