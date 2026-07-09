<template>
  <div class="admin-page p-5">
    <div class="d-flex align-items-center mb-4">
      <button class="btn btn-outline-secondary me-3" @click="router.push('/admin/licenciamentos')">
        <i class="bi bi-arrow-left"></i>
      </button>
      <div>
        <h2 class="mb-0">Detalhe do Licenciamento</h2>
        <p class="text-muted mb-0">{{ item?.numero_processo || '' }}</p>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <template v-else-if="item">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Referência</div>
              <div class="info-value"><code>{{ item.referencia }}</code></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Nº Processo</div>
              <div class="info-value">{{ item.numero_processo || '' }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Estado</div>
              <div class="info-value">
                <span class="estado-badge" :style="{ background: estadoColor(item.estado), color: '#fff' }">
                  {{ estadoLabel(item.estado) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Cliente</div>
              <div class="info-value">{{ item.empresa || item.user_name || '' }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Shipper</div>
              <div class="info-value">{{ item.shipper || '' }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">NIF</div>
              <div class="info-value">{{ item.nif_empresa || '' }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card info-card">
            <div class="card-body">
              <div class="info-label">Tipo</div>
              <div class="info-value">{{ item.tipo_licenciamento || '' }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <div class="info-label mb-2">Descrição</div>
          <p class="mb-0">{{ item.descricao || '' }}</p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title mb-3">Datas</h6>
          <div class="row g-3">
            <div class="col-md-3">
              <div class="date-item">
                <span class="date-label">Data Submissão</span>
                <span class="date-value">{{ formatDate(item.data_submissao) }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="date-item">
                <span class="date-label">Data Aprovação</span>
                <span class="date-value">{{ formatDate(item.data_aprovacao) }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="date-item">
                <span class="date-label">Data Indeferimento</span>
                <span class="date-value">{{ formatDate(item.data_indeferimento) }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="date-item">
                <span class="date-label">Data Validade</span>
                <span class="date-value">{{ formatDate(item.data_validade) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title mb-3">Linha do Tempo do Estado</h6>
          <div v-if="estadosHistorico.length === 0" class="text-muted">
            Sem histórico de alterações de estado.
          </div>
          <div v-else class="timeline">
            <div v-for="(e, idx) in estadosHistorico" :key="idx" class="timeline-item">
              <div class="timeline-dot" :style="{ background: estadoColor(e.estado_novo) }"></div>
              <div class="timeline-content">
                <div class="d-flex align-items-center gap-2">
                  <span class="estado-badge estado-badge-sm" :style="{ background: estadoColor(e.estado_novo), color: '#fff' }">
                    {{ estadoLabel(e.estado_novo) }}
                  </span>
                  <small v-if="e.estado_anterior" class="text-muted">
                    de {{ estadoLabel(e.estado_anterior) }}
                  </small>
                </div>
                <div v-if="e.observacao" class="mt-1 text-muted small">{{ e.observacao }}</div>
                <small class="text-muted">{{ formatDateTime(e.created_at) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="section-title mb-0">Histórico de Alterações</h6>
          </div>
          <div v-if="historico.length === 0" class="text-muted">
            Sem histórico de alterações.
          </div>
          <div v-else class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Campo</th>
                  <th>Utilizador</th>
                  <th>Observação</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(h, idx) in historico" :key="idx">
                  <td><code>{{ formatCampo(h.campo) }}</code></td>
                  <td>
                    <span v-if="parseObsUser(h.valor_novo)" class="badge bg-info text-white">
                      <i class="bi bi-person-fill me-1"></i>{{ parseObsUser(h.valor_novo) }}
                    </span>
                    <span v-else class="text-muted">Sistema</span>
                  </td>
                  <td>{{ parseObsText(h.valor_novo) || h.valor_novo || '' }}</td>
                  <td>
                    <small class="text-muted">
                      <span v-if="parseObsDate(h.valor_novo)"><i class="bi bi-calendar3 me-1"></i>{{ parseObsDate(h.valor_novo) }}</span>
                      <span v-else>{{ formatDateTime(h.created_at) }}</span>
                    </small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title mb-3">Observações</h6>
          <p class="mb-0">{{ item.observacoes || '' }}</p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h6 class="section-title mb-3">Atualizar Estado</h6>
          <div class="d-flex gap-2 align-items-end flex-wrap">
            <div class="flex-grow-1" style="max-width: 280px;">
              <label class="form-label">Novo Estado</label>
              <select v-model="novoEstado" class="form-select">
                <option v-for="s in allEstados" :key="s.value" :value="s.value">{{ s.label }}</option>
              </select>
            </div>
            <div class="flex-grow-1" style="max-width: 400px;">
              <label class="form-label">Observação (opcional)</label>
              <input v-model="estadoObservacao" type="text" class="form-control" placeholder="Motivo da alteração...">
            </div>
            <button class="btn btn-primary" @click="updateEstado" :disabled="savingEstado || !novoEstado || novoEstado === item.estado">
              <span v-if="savingEstado" class="spinner-border spinner-border-sm me-1"></span>
              {{ t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-5 text-muted">
      Licenciamento não encontrado.
    </div>

    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()

const item = ref(null)
const loading = ref(true)
const estadosHistorico = ref([])
const historico = ref([])
const novoEstado = ref('')
const estadoObservacao = ref('')
const savingEstado = ref(false)

const toast = ref({ show: false, type: 'success', message: '' })
let toastTimer = null

const showToast = (type, message) => {
  toast.value = { show: true, type, message }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value.show = false }, 3000)
}

const parseObsUser = (text) => {
  if (!text) return ''
  const m = text.match(/\[([^\]]+)\]/)
  return m ? m[1] : ''
}

const parseObsDate = (text) => {
  if (!text) return ''
  const m = text.match(/^(\d{4}-\d{2}-\d{2})/)
  return m ? m[1] : ''
}

const parseObsText = (text) => {
  if (!text) return ''
  return text
    .replace(/^\d{4}-\d{2}-\d{2}\s*/, '')
    .replace(/\[[^\]]+\]\s*$/, '')
    .trim()
}

const formatCampo = (campo) => {
  const map = {
    'observacao_excel': 'Observação',
    'observacao': 'Observação',
    'estado': 'Estado',
    'cliente_nome': 'Cliente',
    'empresa': 'Empresa',
    'shipper': 'Shipper',
    'descricao': 'Descrição',
    'data_submissao': 'Data de Submissão',
    'data_validade': 'Data de Validade',
    'funcionario_responsavel': 'Funcionário Responsável',
    'tipo_licenciamento': 'Tipo de Licenciamento',
    'nif_empresa': 'NIF'
  }
  return map[campo] || campo
}

const allEstados = [
  { value: 'rascunho', label: 'Rascunho' },
  { value: 'pendente_cliente', label: 'Pendente Cliente' },
  { value: 'submetido', label: 'Submetido' },
  { value: 'em_analise', label: 'Em Análise' },
  { value: 'aprovado', label: 'Aprovado' },
  { value: 'indeferido', label: 'Indeferido' },
  { value: 'expira_brevemente', label: 'Expira Brevemente' },
  { value: 'expirado', label: 'Expirado' }
]

const estadoColor = (estado) => ({
  rascunho: '#6b7280',
  pendente_cliente: '#f59e0b',
  submetido: '#3b82f6',
  em_analise: '#06b6d4',
  aprovado: '#10b981',
  indeferido: '#ef4444',
  expira_brevemente: '#f97316',
  expirado: '#991b1b'
}[estado] || '#6b7280')

const estadoLabel = (estado) => ({
  rascunho: 'Rascunho',
  pendente_cliente: 'Pendente Cliente',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certificação - Solicitada',
  expira_brevemente: 'Expira Brevemente',
  expirado: 'Expirado'
}[estado] || estado)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : ''
const formatDateTime = (d) => d ? new Date(d).toLocaleString('pt-PT') : ''

const fetchData = async () => {
  loading.value = true
  try {
    const id = route.params.id
    const { data, error } = await supabase.from('licenciamentos').select('*').eq('id', id).single()
    if (error || !data) { item.value = null; return }
    item.value = data
    novoEstado.value = data.estado

    const [estadosRes, histRes] = await Promise.all([
      supabase.from('licenciamento_estados_historico').select('*').eq('licenciamento_id', id).order('created_at', { ascending: true }),
      supabase.from('licenciamento_historico').select('*').eq('licenciamento_id', id).order('created_at', { ascending: false })
    ])
    estadosHistorico.value = estadosRes.data || []
    historico.value = histRes.data || []
  } finally {
    loading.value = false
  }
}

const updateEstado = async () => {
  if (!novoEstado.value || novoEstado.value === item.value?.estado) return
  savingEstado.value = true
  try {
    const oldEstado = item.value.estado
    const { error } = await supabase.from('licenciamentos').update({ estado: novoEstado.value }).eq('id', item.value.id)
    if (error) throw error

    await supabase.from('licenciamento_estados_historico').insert({
      licenciamento_id: item.value.id,
      estado_anterior: oldEstado,
      estado_novo: novoEstado.value,
      observacao: estadoObservacao.value || null
    })

    item.value.estado = novoEstado.value
    estadoObservacao.value = ''
    showToast('success', 'Estado atualizado com sucesso!')

    const { data } = await supabase.from('licenciamento_estados_historico').select('*').eq('licenciamento_id', item.value.id).order('created_at', { ascending: true })
    estadosHistorico.value = data || []
  } catch (e) {
    showToast('error', 'Erro ao atualizar estado.')
  } finally {
    savingEstado.value = false
  }
}

onMounted(fetchData)
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; position: relative; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }

.info-card .info-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px; }
.info-card .info-value { font-size: 1rem; font-weight: 500; color: #1e293b; }
.info-card .info-value code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #334155; }

.section-title { font-weight: 600; color: #1e293b; font-size: 0.95rem; }

.date-item { display: flex; flex-direction: column; gap: 4px; }
.date-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
.date-value { font-size: 0.95rem; font-weight: 500; color: #1e293b; }

.estado-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.78rem; font-weight: 600; }
.estado-badge-sm { padding: 0.15rem 0.55rem; font-size: 0.72rem; }

.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 4px; bottom: 4px; width: 2px; background: #e2e8f0; }
.timeline-item { position: relative; padding-bottom: 1.25rem; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot { position: absolute; left: -20px; top: 4px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #e2e8f0; }
.timeline-content { display: flex; flex-direction: column; gap: 2px; }

.table { font-size: 0.88rem; }
.table th { font-weight: 600; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.3px; border-top: none; }
.table td { color: #334155; }
.table td code { background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }

.form-select { border: 2px solid #e2e8f0; border-radius: 8px; }
.form-control { border: 2px solid #e2e8f0; border-radius: 8px; }
.form-select:focus, .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 767px) {
  .admin-page { padding: 1rem !important; }
  .card-body { padding: 1rem; }
}
</style>
