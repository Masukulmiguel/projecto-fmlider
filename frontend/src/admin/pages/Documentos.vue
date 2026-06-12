<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>Documentos</h2>
        <p class="text-muted mb-0">Todos os documentos carregados pelos clientes.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar..." @input="debounceSearch">
          </div>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum documento encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Documento</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Embarque</th>
                <th>Tamanho</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <i :class="fileIcon(item.mime_type)" class="me-2"></i>
                  {{ item.name }}
                </td>
                <td>
                  <div class="fw-medium">{{ item.company_name || item.client_name }}</div>
                </td>
                <td>{{ typeLabel(item.type) }}</td>
                <td><code v-if="item.tracking_number" class="tracking-code">{{ item.tracking_number }}</code><span v-else>—</span></td>
                <td>{{ formatSize(item.file_size) }}</td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '' })
let searchTimer = null

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const fetchData = async () => {
  loading.value = true
  try {
    const r = await axios.get('/api/documentos', { ...authHeader(), params: { q: filters.q } })
    if (r.data.success) items.value = r.data.data.documentos
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const typeLabel = (t) => ({ fatura: 'Fatura', conhecimento_carga: 'B/L', certificado: 'Certificado', contrato: 'Contrato', outro: 'Outro' }[t] || t)
const fileIcon = (m) => {
  if (!m) return 'bi bi-file-earmark'
  if (m.includes('pdf')) return 'bi bi-file-earmark-pdf text-danger'
  if (m.includes('image')) return 'bi bi-file-earmark-image text-primary'
  return 'bi bi-file-earmark'
}
const formatSize = (b) => !b ? '—' : b < 1024 * 1024 ? (b/1024).toFixed(1)+' KB' : (b/1024/1024).toFixed(2)+' MB'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

onMounted(fetchData)
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
</style>
