<template>
  <div class="admin-visitors p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
      <div>
        <h1 class="page-title"><i class="bi bi-globe2 me-2"></i>Visitantes</h1>
        <p class="text-muted mb-0">Todos os acessos ao site público.</p>
      </div>
      <div class="d-flex gap-2">
        <select v-model="deviceFilter" class="form-select form-select-sm" style="width: 140px;">
          <option value="">Todos os dispositivos</option>
          <option value="desktop">Desktop</option>
          <option value="mobile">Mobile</option>
          <option value="tablet">Tablet</option>
        </select>
        <input v-model="search" type="text" class="form-control form-control-sm" placeholder="Pesquisar IP, país, cidade..." style="width: 250px;" />
        <button class="btn btn-sm btn-outline-secondary" @click="load" :disabled="loading">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
    </div>

    <div class="row g-3 mb-4" v-if="data">
      <div class="col-md-3">
        <div class="mini-card">
          <i class="bi bi-eye-fill text-primary"></i>
          <div>
            <div class="mini-label">Total de visitas</div>
            <div class="mini-value">{{ data.total }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mini-card">
          <i class="bi bi-people-fill text-info"></i>
          <div>
            <div class="mini-label">Sessões únicas</div>
            <div class="mini-value">{{ data.unique_sessions }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mini-card">
          <i class="bi bi-calendar-day-fill text-success"></i>
          <div>
            <div class="mini-label">Hoje</div>
            <div class="mini-value">{{ data.today }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mini-card">
          <i class="bi bi-person-check-fill text-warning"></i>
          <div>
            <div class="mini-label">Utilizadores autenticados</div>
            <div class="mini-value">{{ data.logged_users }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Acessos recentes</h6>
        <small class="text-muted">{{ filteredRecent.length }} de {{ data?.recent?.length || 0 }}</small>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead>
            <tr>
              <th>IP</th>
              <th>Localização</th>
              <th>Sistema</th>
              <th>Browser</th>
              <th>Dispositivo</th>
              <th>Utilizador</th>
              <th>Página</th>
              <th>Quando</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && !data">
              <td colspan="8" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
              </td>
            </tr>
            <tr v-else-if="filteredRecent.length === 0">
              <td colspan="8" class="text-center text-muted py-4">Sem visitas registadas.</td>
            </tr>
            <tr v-for="v in filteredRecent" :key="v.id">
              <td><code class="ip">{{ v.ip_address || '—' }}</code></td>
              <td>
                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                <strong>{{ v.country || 'Desconhecido' }}</strong>
                <small v-if="v.city" class="text-muted d-block">{{ v.city }}</small>
              </td>
              <td><i :class="osIcon(v.os)" class="me-1"></i>{{ v.os || '—' }}</td>
              <td>{{ v.browser || '—' }}</td>
              <td>
                <span class="device-pill" :class="`device-${v.device_type}`">
                  <i :class="deviceIcon(v.device_type)"></i> {{ v.device_type || '—' }}
                </span>
              </td>
              <td>
                <span v-if="v.user_name" class="badge bg-success">{{ v.user_name }}</span>
                <span v-else class="text-muted small">Anónimo</span>
              </td>
              <td><small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" :title="v.page_url">{{ v.page_url || '—' }}</small></td>
              <td><small class="text-muted">{{ formatTime(v.visited_at) }}</small></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const data = ref(null)
const loading = ref(false)
const search = ref('')
const deviceFilter = ref('')

const load = async () => {
  loading.value = true
  try {
    const { data: visitors, error } = await supabase.from('visitors').select('*').order('visited_at', { ascending: false })
    if (!error && visitors) {
      const today = new Date().toISOString().split('T')[0]
      const todayCount = visitors.filter(v => v.visited_at && v.visited_at.startsWith(today)).length
      const uniqueSessions = new Set(visitors.map(v => v.session_id || v.ip_address)).size
      const loggedUsers = new Set(visitors.filter(v => v.user_id).map(v => v.user_id)).size

      data.value = {
        total: visitors.length,
        unique_sessions: uniqueSessions,
        today: todayCount,
        logged_users: loggedUsers,
        recent: visitors.slice(0, 100)
      }
    }
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const filteredRecent = computed(() => {
  if (!data.value?.recent) return []
  const q = search.value.trim().toLowerCase()
  return data.value.recent.filter(v => {
    if (deviceFilter.value && v.device_type !== deviceFilter.value) return false
    if (!q) return true
    return (
      (v.ip_address || '').toLowerCase().includes(q) ||
      (v.country || '').toLowerCase().includes(q) ||
      (v.city || '').toLowerCase().includes(q) ||
      (v.browser || '').toLowerCase().includes(q) ||
      (v.os || '').toLowerCase().includes(q)
    )
  })
})

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const diff = (now - d) / 1000
  if (diff < 60) return 'agora'
  if (diff < 3600) return `há ${Math.floor(diff/60)}min`
  if (diff < 86400) return `há ${Math.floor(diff/3600)}h`
  if (diff < 7 * 86400) return `há ${Math.floor(diff/86400)}d`
  return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const osIcon = (os) => {
  if (!os) return 'bi bi-question-circle'
  if (os.startsWith('Windows')) return 'bi bi-windows'
  if (os.startsWith('macOS') || os.startsWith('iOS')) return 'bi bi-apple'
  if (os.startsWith('Android')) return 'bi bi-android2'
  if (os.startsWith('Linux')) return 'bi bi-ubuntu'
  return 'bi bi-question-circle'
}

const deviceIcon = (d) => ({
  desktop: 'bi bi-display',
  mobile: 'bi bi-phone',
  tablet: 'bi bi-tablet'
}[d] || 'bi bi-question-circle')

onMounted(load)
</script>

<style scoped>
.admin-visitors { background: #f5f7fa; min-height: 100vh; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.mini-card {
  background: #fff;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  display: flex; align-items: center; gap: 1rem;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
  height: 100%;
}
.mini-card i { font-size: 1.75rem; }
.mini-label { color: #64748b; font-size: 0.78rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
.mini-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1.1; }

.card { border: none; border-radius: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); }
.card-header { background: #fff; border-bottom: 1px solid #eef0f3; padding: 0.85rem 1.25rem; }
.card-header h6 { font-weight: 700; color: #0f172a; }

.ip { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; color: #334155; }

.device-pill {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 0.75rem; font-weight: 600;
  padding: 3px 10px; border-radius: 12px;
}
.device-desktop { background: #dbeafe; color: #1d4ed8; }
.device-mobile { background: #d1fae5; color: #065f46; }
.device-tablet { background: #fef3c7; color: #92400e; }
</style>
