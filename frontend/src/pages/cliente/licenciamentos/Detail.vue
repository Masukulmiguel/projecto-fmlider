<template>
  <div class="crud-page">
    <div class="page-header mb-4">
      <button class="btn btn-outline-secondary" @click="router.back()">
        <i class="bi bi-arrow-left me-1"></i> Voltar
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else-if="!item" class="empty-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>Licenciamento não encontrado.</p>
    </div>

    <template v-else>
      <div class="detail-header mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <code class="ref-code-lg">{{ item.referencia }}</code>
          <span class="status-badge" :class="`status-${item.estado}`">{{ statusLabel(item.estado) }}</span>
        </div>
        <p v-if="item.numero_processo" class="text-muted mt-2 mb-0">
          Processo: {{ item.numero_processo }}
        </p>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Progresso do Licenciamento</h6>
        </div>
        <div class="card-body">
          <div class="timeline">
            <div
              v-for="(step, index) in timelineSteps"
              :key="step.key"
              class="timeline-item"
              :class="{ completed: step.status === 'completed', active: step.status === 'active', future: step.status === 'future' }"
            >
              <div class="timeline-connector" v-if="index > 0">
                <div class="connector-line" :class="{ filled: step.status === 'completed' || step.status === 'active' }"></div>
              </div>
              <div class="timeline-node">
                <div class="node-circle" :class="step.status">
                  <i v-if="step.status === 'completed'" class="bi bi-check-lg"></i>
                  <i v-else-if="step.status === 'active'" class="bi bi-arrow-repeat spin"></i>
                  <span v-else class="node-dash"></span>
                </div>
              </div>
              <div class="timeline-content">
                <div class="step-label">{{ step.label }}</div>
                <div v-if="step.date" class="step-date"><i class="bi bi-calendar3 me-1"></i>{{ step.date }}</div>
                <div v-else-if="step.status === 'completed'" class="step-date text-muted">—</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-building me-2"></i>Informações</h6>
            </div>
            <div class="card-body">
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Empresa</span>
                  <span class="info-value">{{ item.empresa || '—' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">NIF</span>
                  <span class="info-value">{{ item.nif || '—' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Tipo</span>
                  <span class="info-value">{{ tipoLabel(item.tipo) }}</span>
                </div>
                <div v-if="item.descricao" class="info-item full-width">
                  <span class="info-label">Descrição</span>
                  <span class="info-value">{{ item.descricao }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Datas</h6>
            </div>
            <div class="card-body">
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Criado em</span>
                  <span class="info-value">{{ formatDate(item.created_at) }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Atualizado em</span>
                  <span class="info-value">{{ formatDate(item.updated_at) }}</span>
                </div>
                <div v-if="item.data_submissao" class="info-item">
                  <span class="info-label">Data Submissão</span>
                  <span class="info-value">{{ formatDate(item.data_submissao) }}</span>
                </div>
                <div v-if="item.data_aprovacao" class="info-item">
                  <span class="info-label">Data Aprovação</span>
                  <span class="info-value">{{ formatDate(item.data_aprovacao) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="item.observacoes" class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Observações</h6>
        </div>
        <div class="card-body">
          <p class="mb-0 observacoes-text">{{ item.observacoes }}</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const item = ref(null)
const loading = ref(true)

const timelineSteps = computed(() => {
  if (!item.value) return []

  const statusOrder = ['pendente', 'documentacao_recebida', 'submetido', 'em_analise', 'aprovado', 'indeferido']
  const currentIdx = statusOrder.indexOf(item.value.estado)

  const isTerminal = item.value.estado === 'aprovado' || item.value.estado === 'indeferido'

  const steps = [
    { key: 'pendente', label: 'Criado', date: item.value.data_criacao || item.value.created_at },
    { key: 'documentacao_recebida', label: 'Documentação Recebida', date: item.value.data_documentacao },
    { key: 'submetido', label: 'Submetido', date: item.value.data_submissao },
    { key: 'em_analise', label: 'Em Análise', date: item.value.data_analise },
  ]

  if (isTerminal) {
    steps.push({
      key: item.value.estado,
      label: item.value.estado === 'aprovado' ? 'Aprovado' : 'Indeferido',
      date: item.value.data_aprovacao
    })
  }

  return steps.map((step, idx) => {
    let status = 'future'
    if (isTerminal) {
      if (item.value.estado === 'aprovado') {
        if (idx <= currentIdx || (step.key !== 'indeferido' && step.key !== 'aprovado')) {
          status = 'completed'
        }
        if (step.key === 'aprovado') status = 'completed'
      } else {
        if (step.key === 'aprovado') {
          status = 'future'
        } else if (step.key === 'indeferido') {
          status = 'completed'
        } else if (idx < currentIdx) {
          status = 'completed'
        }
      }
    } else {
      if (idx < currentIdx) {
        status = 'completed'
      } else if (idx === currentIdx) {
        status = 'active'
      }
    }
    return { ...step, status }
  })
})

const fetchItem = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase
      .from('licenciamentos')
      .select('*')
      .eq('id', route.params.id)
      .eq('user_id', authStore.user?.id)
      .single()
    if (!error && data) {
      item.value = data
    } else {
      const userName = (authStore.user?.full_name || authStore.user?.name || '').trim().toLowerCase()
      if (userName && userName.length >= 3) {
        const { data: byName } = await supabase
          .from('licenciamentos')
          .select('*')
          .eq('id', route.params.id)
          .ilike('cliente_nome', `%${userName}%`)
          .single()
        if (byName) item.value = byName
      }
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const statusLabel = (status) => ({
  pendente: 'Pendente',
  documentacao_recebida: 'Documentação Recebida',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certificação - Solicitada'
}[status] || status)

const tipoLabel = (tipo) => ({
  importacao: 'Importação',
  exportacao: 'Exportação',
  trânsito: 'Trânsito',
  tranzito: 'Trânsito',
  licenca_especial: 'Licença Especial',
  outro: 'Outro'
}[tipo] || tipo || '—')

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

onMounted(fetchItem)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { display: flex; align-items: center; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.card-header h6 { font-weight: 700; color: #0f172a; font-size: 0.9rem; }

.detail-header { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.ref-code-lg { background: #f1f5f9; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 1.1rem; color: #334155; font-weight: 700; }

.status-badge { display: inline-block; padding: 0.3rem 0.75rem; border-radius: 12px; font-size: 0.78rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-documentacao_recebida { background: #e0e7ff; color: #4338ca; }
.status-submetido { background: #cffafe; color: #155e75; }
.status-em_analise { background: #dbeafe; color: #1d4ed8; }
.status-aprovado { background: #d1fae5; color: #065f46; }
.status-indeferido { background: #fee2e2; color: #991b1b; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }

.timeline { position: relative; padding: 0.5rem 0; }
.timeline-item { display: flex; align-items: flex-start; gap: 1rem; position: relative; }
.timeline-connector { display: flex; justify-content: center; width: 40px; min-height: 20px; position: relative; }
.connector-line { width: 2px; background: #e2e8f0; position: absolute; top: 0; bottom: 0; left: 50%; transform: translateX(-50%); }
.connector-line.filled { background: #10b981; }

.timeline-node { flex-shrink: 0; position: relative; z-index: 1; }
.node-circle {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 600;
  transition: all 0.3s ease;
}
.node-circle.completed { background: #10b981; color: white; }
.node-circle.active { background: #2563eb; color: white; animation: pulse 2s infinite; }
.node-circle.future { background: #e2e8f0; color: #94a3b8; }

@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
  50% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
}

.spin { animation: spin 1.5s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.node-dash { width: 14px; height: 2px; background: #94a3b8; border-radius: 1px; }

.timeline-content { padding: 0.5rem 0 1.5rem; }
.step-label { font-weight: 600; color: #0f172a; font-size: 0.95rem; }
.timeline-item.future .step-label { color: #94a3b8; }
.step-date { font-size: 0.8rem; color: #64748b; margin-top: 0.2rem; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-item.full-width { grid-column: 1 / -1; }
.info-label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.info-value { font-size: 0.95rem; color: #0f172a; font-weight: 500; }

.observacoes-text { color: #334155; line-height: 1.7; white-space: pre-line; }

@media (max-width: 576px) {
  .info-grid { grid-template-columns: 1fr; }
  .node-circle { width: 34px; height: 34px; font-size: 0.95rem; }
  .timeline-connector { width: 34px; min-height: 16px; }
  .step-label { font-size: 0.88rem; }
  .ref-code-lg { font-size: 0.95rem; }
}
</style>
