<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ t('funcionario.clients_title') }}</h1>
        <p class="text-muted mb-0">{{ t('funcionario.clients_subtitle') }}</p>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input v-model="search" type="text" :placeholder="t('funcionario.clients_search')">
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="filtered.length === 0" class="empty-state">
          <i class="bi bi-people"></i>
          <p>{{ t('funcionario.clients_empty') }}</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>{{ t('funcionario.clients_col_client') }}</th>
                <th>{{ t('funcionario.clients_col_email') }}</th>
                <th>{{ t('funcionario.clients_col_phone') }}</th>
                <th>{{ t('funcionario.clients_col_status') }}</th>
                <th>{{ t('funcionario.clients_registered') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in paginatedItems" :key="u.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm">{{ initials(u.name) }}</div>
                    <div>
                      <strong>{{ u.name }}</strong>
                      <small class="d-block text-muted">@{{ u.username }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ u.email }}</td>
                <td>{{ u.phone || '—' }}</td>
                <td>
                  <span v-if="u.approval_status === 'pending'" class="badge bg-warning text-dark">{{ t('funcionario.clients_status_pending') }}</span>
                  <span v-else-if="u.approval_status === 'approved'" class="badge bg-success">{{ t('funcionario.clients_status_approved') }}</span>
                  <span v-else class="badge bg-danger">{{ t('funcionario.clients_status_rejected') }}</span>
                </td>
                <td><small class="text-muted">{{ formatDate(u.created_at) }}</small></td>
              </tr>
            </tbody>
          </table>
          <div v-if="totalPages > 1" class="pagination-bar">
            <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
            <div class="page-btns">
              <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const authStore = useAuthStore()
const items = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = 20

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return items.value
  return items.value.filter(u =>
    (u.name || '').toLowerCase().includes(q) ||
    (u.email || '').toLowerCase().includes(q) ||
    (u.username || '').toLowerCase().includes(q)
  )
})

const totalPages = computed(() => Math.ceil(filtered.value.length / perPage))

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filtered.value.slice(start, start + perPage)
})

const load = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('users').select('*').eq('role', 'cliente')
    if (!error) items.value = data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

watch(search, () => { currentPage.value = 1 })

onMounted(load)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.search-box { position: relative; max-width: 400px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #0f766e; outline: none; }
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.avatar-sm {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem; font-weight: 600;
  flex-shrink: 0;
}
.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #f1f5f9;
}
.page-info {
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 500;
}
.page-btns {
  display: flex;
  gap: 6px;
}
.page-btn {
  width: 34px;
  height: 34px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.page-btn:hover:not(:disabled) { border-color: #0f766e; color: #0f766e; background: #f0fdfa; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
