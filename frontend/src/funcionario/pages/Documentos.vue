<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Documentos</h1>
        <p class="text-muted mb-0">Documentos carregados pelos clientes.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-file-earmark-text"></i>
          <p>Sem documentos.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Tamanho</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in items" :key="d.id">
                <td><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>{{ d.name }}</td>
                <td><span class="text-capitalize">{{ d.type.replace('_', ' ') }}</span></td>
                <td>{{ d.client_name || '—' }}</td>
                <td>{{ formatSize(d.file_size) }}</td>
                <td><small class="text-muted">{{ formatDate(d.created_at) }}</small></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const loading = ref(false)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'
const formatSize = (b) => {
  if (!b) return '—'
  if (b < 1024) return b + ' B'
  if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB'
  return (b/1024/1024).toFixed(2) + ' MB'
}

const load = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('documentos').select('*')
    if (!error) items.value = data || []
  } finally { loading.value = false }
}

onMounted(load)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
</style>
