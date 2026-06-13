<template>
  <div class="admin-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title"><i class="bi bi-gear-wide-connected me-2"></i>Serviços</h1>
        <p class="text-muted mb-0">Gere os serviços apresentados no site.</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> Novo Serviço
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="items.length === 0" class="card empty-card">
      <div class="card-body text-center py-5">
        <i class="bi bi-gear" style="font-size: 3rem; color: #0f766e;"></i>
        <h5 class="mt-3">Sem serviços</h5>
        <p class="text-muted mb-3">Adicione o primeiro serviço para começar.</p>
        <button class="btn btn-primary" @click="openForm()">
          <i class="bi bi-plus-lg me-1"></i> Criar serviço
        </button>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Imagem</th>
                <th>Título</th>
                <th>Slug</th>
                <th>Estado</th>
                <th>Ordem</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in sortedItems" :key="s.id">
                <td>
                  <div class="service-thumb">
                    <img v-if="s.image" :src="s.image" :alt="s.title">
                    <div v-else class="thumb-placeholder">
                      <i class="bi bi-image"></i>
                    </div>
                  </div>
                </td>
                <td><strong>{{ s.title }}</strong></td>
                <td><code>{{ s.slug }}</code></td>
                <td>
                  <span v-if="s.status == 1" class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>Ativo
                  </span>
                  <span v-else class="badge bg-secondary">
                    <i class="bi bi-slash-circle me-1"></i>Inativo
                  </span>
                </td>
                <td>{{ s.order_by ?? 0 }}</td>
                <td class="text-end">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-outline-secondary" @click="openForm(s)" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(s)" title="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade show d-block" v-if="showForm" @click.self="closeForm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-gear-wide-connected me-2"></i>{{ editing ? 'Editar serviço' : 'Novo serviço' }}
            </h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Título *</label>
                  <input v-model="form.title" type="text" class="form-control" required
                    @input="autoSlug">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Slug *</label>
                  <input v-model="form.slug" type="text" class="form-control" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Descrição curta</label>
                  <input v-model="form.description" type="text" class="form-control"
                    placeholder="Breve descrição do serviço">
                </div>
                <div class="col-12">
                  <label class="form-label">Conteúdo</label>
                  <textarea v-model="form.content" class="form-control" rows="5"
                    placeholder="Descrição detalhada do serviço"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Imagem</label>
                  <input type="file" class="form-control" accept="image/*" @change="onFileChange" ref="fileInput">
                  <div v-if="form.image" class="mt-2 position-relative d-inline-block">
                    <img :src="form.image" class="img-thumbnail preview-img" alt="Preview">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0"
                      @click="removeImage" title="Remover imagem">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Ordem</label>
                  <input v-model.number="form.order_by" type="number" class="form-control" min="0">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Estado</label>
                  <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="statusToggle"
                      :checked="form.status == 1" @change="form.status = $event.target.checked ? 1 : 0">
                    <label class="form-check-label" for="statusToggle">
                      {{ form.status == 1 ? 'Ativo' : 'Inativo' }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeForm">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? 'Atualizar' : 'Criar serviço' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showForm"></div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editing = ref(null)
const errorMessage = ref('')
const fileInput = ref(null)
const imageFile = ref(null)

const defaultForm = () => ({
  title: '',
  slug: '',
  description: '',
  content: '',
  image: '',
  status: 1,
  order_by: 0,
})
const form = reactive(defaultForm())

const sortedItems = computed(() =>
  [...items.value].sort((a, b) => (a.order_by ?? 0) - (b.order_by ?? 0))
)

const toSlug = (str) =>
  str.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)+/g, '')

const autoSlug = () => {
  if (!editing.value) {
    form.slug = toSlug(form.title)
  }
}

const fetchList = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('services').select('*').order('order_by', { ascending: true })
    if (!error) items.value = data
  } catch (e) {
    console.error('Erro ao carregar serviços', e)
  } finally {
    loading.value = false
  }
}

const openForm = (item = null) => {
  editing.value = item
  imageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  if (item) {
    form.title = item.title || ''
    form.slug = item.slug || ''
    form.description = item.description || ''
    form.content = item.content || ''
    form.image = item.image || ''
    form.status = item.status ?? 1
    form.order_by = item.order_by ?? 0
  } else {
    Object.assign(form, defaultForm())
  }
  errorMessage.value = ''
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editing.value = null
  imageFile.value = null
}

const onFileChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  imageFile.value = file
  form.image = URL.createObjectURL(file)
}

const removeImage = () => {
  form.image = ''
  imageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const uploadImage = async (file) => {
  const fileExt = file.name.split('.').pop()
  const fileName = `services/${Date.now()}.${fileExt}`
  const { data, error } = await supabase.storage.from('uploads').upload(fileName, file)
  if (error) throw error
  const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
  return urlData.publicUrl
}

const handleSubmit = async () => {
  errorMessage.value = ''
  if (!form.title.trim()) {
    errorMessage.value = 'O título é obrigatório.'
    return
  }
  saving.value = true
  try {
    let imageUrl = form.image
    if (imageFile.value && !form.image.startsWith('http') && !form.image.startsWith('/')) {
      imageUrl = await uploadImage(imageFile.value)
    }
    const payload = {
      title: form.title.trim(),
      slug: form.slug || toSlug(form.title),
      description: form.description,
      content: form.content,
      image: imageUrl,
      status: form.status,
      order_by: form.order_by,
    }
    if (editing.value) {
      const { error } = await supabase.from('services').update(payload).eq('id', editing.value.id)
      if (error) throw error
    } else {
      const { error } = await supabase.from('services').insert(payload)
      if (error) throw error
    }
    closeForm()
    await fetchList()
  } catch (e) {
    errorMessage.value = e.message || 'Erro ao guardar.'
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (item) => {
  if (!confirm(`Eliminar o serviço "${item.title}"? Esta acção é irreversível.`)) return
  try {
    const { error } = await supabase.from('services').delete().eq('id', item.id)
    if (error) throw error
    await fetchList()
  } catch (e) {
    alert(e.message || 'Erro ao eliminar')
  }
}

onMounted(() => fetchList())
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.empty-card { border: none; }

.service-thumb {
  width: 50px; height: 50px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}
.service-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder {
  width: 100%; height: 100%;
  background: #e2e8f0;
  display: flex; align-items: center; justify-content: center;
  color: #94a3b8; font-size: 1.2rem;
}

.preview-img { max-width: 160px; max-height: 120px; object-fit: cover; }

.action-buttons { display: inline-flex; gap: 0.4rem; }

.modal-backdrop { z-index: 1040; }
.modal { z-index: 1050; }
</style>
