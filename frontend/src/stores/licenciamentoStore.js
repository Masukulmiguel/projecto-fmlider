import { ref } from 'vue'
import { supabase } from '@/lib/supabase'

export function useLicenciamentoStore() {
  const licenciamentos = ref([])
  const current = ref(null)
  const stats = ref(null)
  const loading = ref(false)

  const fetchAll = async (filters = {}) => {
    loading.value = true
    try {
      let query = supabase.from('licenciamentos').select('*')
      if (filters.estado) query = query.eq('estado', filters.estado)
      if (filters.tipo) query = query.eq('tipo_licenciamento', filters.tipo)
      if (filters.user_id) query = query.eq('user_id', filters.user_id)
      if (filters.q) {
        query = query.or(`numero_processo.ilike.%${filters.q}%,referencia.ilike.%${filters.q}%,empresa.ilike.%${filters.q}%,nif_empresa.ilike.%${filters.q}%`)
      }
      const { data, error } = await query.order('created_at', { ascending: false })
      if (!error) licenciamentos.value = data || []
    } finally { loading.value = false }
  }

  const fetchStats = async () => {
    const { data } = await supabase.rpc('').select('*').single()
    const rows = licenciamentos.value
    const s = { total: rows.length, rascunho: 0, pendente_cliente: 0, submetido: 0, em_analise: 0, aprovado: 0, indeferido: 0, expira_brevemente: 0, expirado: 0 }
    rows.forEach(r => { if (s[r.estado] !== undefined) s[r.estado]++ })
    stats.value = s
  }

  const fetchOne = async (id) => {
    const { data, error } = await supabase.from('licenciamentos').select('*').eq('id', id).single()
    if (!error) current.value = data
    return { data, error }
  }

  const create = async (payload) => {
    const { data, error } = await supabase.from('licenciamentos').insert(payload).select().single()
    return { data, error }
  }

  const update = async (id, payload) => {
    const { error } = await supabase.from('licenciamentos').update(payload).eq('id', id)
    return { error }
  }

  const updateEstado = async (id, estado, observacao = '') => {
    const old = licenciamentos.value.find(l => l.id === id)
    const { error } = await supabase.from('licenciamentos').update({ estado }).eq('id', id)
    if (!error) {
      await supabase.from('licenciamento_estados_historico').insert({
        licenciamento_id: id,
        estado_anterior: old?.estado || null,
        estado_novo: estado,
        observacao
      })
    }
    return { error }
  }

  const remove = async (id) => {
    const { error } = await supabase.from('licenciamentos').delete().eq('id', id)
    return { error }
  }

  const importExcel = async (records, defaultUserId = null) => {
    const results = { importados: 0, atualizados: 0, novos: 0, erros: 0, detalhes_erros: [] }
    for (const rec of records) {
      try {
        const numero = rec.numero_processo || rec['Numero do Processo'] || rec.numero || null
        if (!numero) { results.erros++; results.detalhes_erros.push({ erro: 'Nº processo em falta' }); continue }

        const empresa = rec.empresa || rec.Empresa || rec.nome_empresa || null
        const nif = rec.nif_empresa || rec.NIF || rec.nif || null
        const tipo = rec.tipo_licenciamento || rec.Tipo || rec.tipo || 'Outro'
        const estadoExcel = rec.estado || rec.Estado || rec.status || null
        const dataSub = rec.data_submissao || rec['Data de Submissão'] || null
        const dataAprov = rec.data_aprovacao || rec['Data de Aprovação'] || null
        const dataIndef = rec.data_indeferimento || rec['Data de Indeferimento'] || null
        const dataValid = rec.data_validade || rec['Data de Validade'] || null
        const desc = rec.descricao || rec.Descricao || rec.descrição || null
        const obs = rec.observacoes || rec['Observações'] || rec.notas || null

        const estado = determinarEstado(estadoExcel, dataAprov, dataIndef, dataSub, dataValid)

        const { data: existing } = await supabase.from('licenciamentos').select('id, estado').eq('numero_processo', numero).maybeSingle()

        if (existing) {
          const updates = {}
          if (empresa) updates.empresa = empresa
          if (nif) updates.nif_empresa = nif
          if (tipo) updates.tipo_licenciamento = tipo
          if (desc) updates.descricao = desc
          if (obs) updates.observacoes = obs
          if (dataSub) updates.data_submissao = dataSub
          if (dataAprov) updates.data_aprovacao = dataAprov
          if (dataIndef) updates.data_indeferimento = dataIndef
          if (dataValid) updates.data_validade = dataValid
          if (estado && estado !== existing.estado) {
            updates.estado = estado
            await supabase.from('licenciamento_estados_historico').insert({
              licenciamento_id: existing.id, estado_anterior: existing.estado, estado_novo: estado, observacao: 'Atualização via Excel'
            })
          }
          if (Object.keys(updates).length > 0) {
            await supabase.from('licenciamentos').update(updates).eq('id', existing.id)
            results.atualizados++
          }
          results.importados++
        } else {
          const referencia = `LIC-${new Date().toISOString().slice(0,10).replace(/-/g,'')}-${Math.random().toString(36).slice(2,6).toUpperCase()}`
          const insert = {
            user_id: defaultUserId || (await supabase.auth.getUser()).data.user?.id,
            numero_processo: numero,
            referencia,
            tipo_licenciamento: tipo,
            descricao: desc,
            empresa,
            nif_empresa: nif,
            estado,
            data_submissao: dataSub || null,
            data_aprovacao: dataAprov || null,
            data_indeferimento: dataIndef || null,
            data_validade: dataValid || null,
            observacoes: obs,
            fonte: 'excel'
          }
          const { data: created, error } = await supabase.from('licenciamentos').insert(insert).select('id').single()
          if (error) throw error
          await supabase.from('licenciamento_estados_historico').insert({
            licenciamento_id: created.id, estado_novo: estado, observacao: 'Importado via Excel'
          })
          results.novos++
          results.importados++
        }
      } catch (e) {
        results.erros++
        results.detalhes_erros.push({ erro: e.message })
      }
    }
    return results
  }

  const determinarEstado = (estadoExcel, dataAprov, dataIndef, dataSub, dataValid) => {
    if (dataAprov) return 'aprovado'
    if (dataIndef) return 'indeferido'
    if (dataValid && new Date(dataValid) < new Date()) return 'expirado'
    if (dataValid && new Date(dataValid) < new Date(Date.now() + 30 * 86400000)) return 'expira_brevemente'
    if (dataSub) return 'em_analise'
    if (estadoExcel) {
      const map = { 'aprovado': 'aprovado', 'aprovada': 'aprovado', 'indeferido': 'indeferido', 'indeferida': 'indeferido', 'em análise': 'em_analise', 'submetido': 'submetido', 'pendente': 'pendente_cliente', 'expirado': 'expirado' }
      return map[estadoExcel.toLowerCase().trim()] || 'submetido'
    }
    return 'submetido'
  }

  return { licenciamentos, current, stats, loading, fetchAll, fetchStats, fetchOne, create, update, updateEstado, remove, importExcel }
}
