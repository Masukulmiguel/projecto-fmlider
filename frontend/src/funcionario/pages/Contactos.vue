<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Contactos</h1>
        <p class="text-muted mb-0">Contactos de todos os clientes.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-person-rolodex"></i>
          <p>Sem contactos.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Empresa</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Cargo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in items" :key="c.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm">{{ initials(c.name) }}</div>
                    <span>{{ c.name }}</span>
                  </div>
                </td>
                <td>{{ c.company || '—' }}</td>
                <td>{{ c.email || '—' }}</td>
                <td>{{ c.phone || '—' }}</td>
                <td>{{ c.position || '—' }}</td>
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
const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const load = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('contactos').select('*')
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
.avatar-sm {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.7rem; font-weight: 600;
}
</style>
