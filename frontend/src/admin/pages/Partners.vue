<template>
  <div class="admin-list-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1 fw-bold">Parceiros</h4>
        <small class="text-muted">Parceiros oficiais e operadores logísticos</small>
      </div>
      <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreate">
        <i class="bi bi-plus-lg"></i><span>Novo Parceiro</span>
      </button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary" style="width:2rem;height:2rem"></div></div>

    <div v-else-if="items.length === 0" class="empty-state">
      <i class="bi bi-handshake"></i><p>Nenhum parceiro registado</p>
    </div>

    <div v-else class="section-group">
      <div class="section-body">
        <div class="list-row" v-for="item in items" :key="item.id">
          <div class="list-row-left">
            <div class="list-thumb" :style="{ backgroundImage: item.logo ? `url(${item.logo})` : 'none' }">
              <i v-if="!item.logo" class="bi bi-building text-muted"></i>
            </div>
            <div class="list-info">
              <div class="list-title">{{ item.name }}</div>
              <div class="list-sub">
                <a v-if="item.website" :href="item.website" target="_blank" class="text-primary text-decoration-none">{{ item.website }}</a>
                <span v-else class="text-muted">Sem website</span>
              </div>
            </div>
          </div>
          <div class="list-row-right">
            <span class="status-dot" :class="item.status ? 'active' : 'inactive'"></span>
            <span class="order-badge">{{ item.order_by || 0 }}</span>
            <div class="action-btns">
              <button class="action-btn edit" title="Editar" @click="openEdit(item)"><i class="bi bi-pencil"></i></button>
              <button class="action-btn delete" title="Eliminar" @click="confirmDelete(item)"><i class="bi bi-trash3"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0"><h6 class="modal-title fw-bold">{{ editing ? 'Editar Parceiro' : 'Novo Parceiro' }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body pt-2">
            <div class="row g-3">
              <div class="col-md-8"><label class="form-label fw-medium small">Nome *</label><input v-model="form.name" type="text" class="form-control form-control-sm" placeholder="Nome do parceiro"></div>
              <div class="col-md-4"><label class="form-label fw-medium small">Ordem</label><input v-model.number="form.order_by" type="number" class="form-control form-control-sm" min="0"></div>
              <div class="col-12"><label class="form-label fw-medium small">Website</label><input v-model="form.website" type="url" class="form-control form-control-sm" placeholder="https://..."></div>
              <div class="col-12"><label class="form-label fw-medium small">Descrição</label><textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea></div>
              <div class="col-12"><label class="form-label fw-medium small">Logo</label>
                <div class="upload-zone" @click="$refs.fileInput.click()">
                  <input ref="fileInput" type="file" accept="image/*" class="d-none" @change="onFileChange">
                  <div v-if="imagePreview || form.logo" class="upload-preview"><img :src="imagePreview || form.logo" alt="Preview"/><button class="upload-remove" @click.stop="clearImage"><i class="bi bi-x-lg"></i></button></div>
                  <div v-else class="upload-placeholder"><i class="bi bi-cloud-arrow-up"></i><p>Clique ou arraste uma imagem</p></div>
                </div>
              </div>
              <div class="col-12"><div class="form-check form-switch"><input v-model="form.status" class="form-check-input" type="checkbox" id="pStatus"/><label class="form-check-label small" for="pStatus">{{ form.status ? 'Ativo' : 'Inativo' }}</label></div></div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" :disabled="submitting || !form.name.trim()" @click="submitForm"><span v-if="submitting" class="spinner-border spinner-border-sm"></span><i v-else class="bi bi-check-lg"></i>{{ editing ? 'Guardar' : 'Criar' }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" ref="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-body text-center py-4"><div class="delete-icon mb-3"><i class="bi bi-trash3"></i></div><h6 class="fw-bold mb-2">Eliminar parceiro?</h6><p class="text-muted small mb-0">{{ itemToDelete?.name }}</p></div>
          <div class="modal-footer border-0 justify-content-center pt-0 pb-3">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-2" :disabled="deleting" @click="deleteItem"><span v-if="deleting" class="spinner-border spinner-border-sm"></span><i v-else class="bi bi-trash3"></i>Eliminar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'
import { useToast } from '@/composables/useToast'
const toast = useToast()

const items = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const itemToDelete = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)
const formModal = ref(null)
const deleteModal = ref(null)
const fileInput = ref(null)
let formInst = null, delInst = null

const form = reactive({ name: '', logo: '', website: '', description: '', status: true, order_by: 0 })

async function fetchData() {
  loading.value = true
  try { const { data, error } = await supabase.from('partners').select('*').order('order_by'); if (!error) items.value = data || [] } catch(e) { console.error(e) }
  finally { loading.value = false }
}

function resetForm() { Object.assign(form, { name: '', logo: '', website: '', description: '', status: true, order_by: 0 }); imageFile.value = null; imagePreview.value = null; editing.value = false; editingId.value = null }
function openCreate() { resetForm(); formInst.show() }
function openEdit(item) { editing.value = true; editingId.value = item.id; Object.assign(form, { name: item.name || '', logo: item.logo || '', website: item.website || '', description: item.description || '', status: item.status !== 0, order_by: item.order_by || 0 }); imageFile.value = null; imagePreview.value = null; formInst.show() }

function onFileChange(e) { const f = e.target.files?.[0]; if (f) { imageFile.value = f; imagePreview.value = URL.createObjectURL(f) } }
function clearImage() { imageFile.value = null; imagePreview.value = null; form.logo = '' }

function fileToBase64(f) { return new Promise((res, rej) => { const r = new FileReader(); r.onload = () => res(r.result); r.onerror = rej; r.readAsDataURL(f) }) }

async function submitForm() {
  if (!form.name.trim()) return
  submitting.value = true
  try {
    let logo = form.logo; if (imageFile.value) logo = await fileToBase64(imageFile.value)
    const p = { name: form.name.trim(), logo, website: form.website, description: form.description, status: form.status ? 1 : 0, order_by: form.order_by }
    if (editing.value) { const { error } = await supabase.from('partners').update(p).eq('id', editingId.value); if (error) throw error }
    else { const { error } = await supabase.from('partners').insert(p); if (error) throw error }
    formInst.hide(); toast.success(editing.value ? 'Parceiro atualizado!' : 'Parceiro criado!'); await fetchData()
  } catch(e) { toast.error('Erro: ' + (e.message || '')) }
  finally { submitting.value = false }
}

function confirmDelete(item) { itemToDelete.value = item; delInst.show() }
async function deleteItem() {
  if (!itemToDelete.value) return; deleting.value = true
  try { const { error } = await supabase.from('partners').delete().eq('id', itemToDelete.value.id); if (error) throw error; delInst.hide(); toast.success('Parceiro eliminado!'); await fetchData() }
  catch(e) { toast.error('Erro ao eliminar') }
  finally { deleting.value = false }
}

onMounted(() => { formInst = new Modal(formModal.value); delInst = new Modal(deleteModal.value); fetchData() })
onBeforeUnmount(() => { formInst?.dispose(); delInst?.dispose() })
</script>

<style scoped>
.admin-list-page { padding: 1.5rem; }
.section-group { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.section-body { padding: 0.25rem 0; }
.list-row { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.25rem; transition: background 0.15s; }
.list-row:hover { background: #f8fafc; }
.list-row-left { display: flex; align-items: center; gap: 0.875rem; min-width: 0; flex: 1; }
.list-thumb { width: 48px; height: 48px; border-radius: 10px; background: #f1f5f9; background-size: contain; background-repeat: no-repeat; background-position: center; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e2e8f0; }
.list-info { min-width: 0; }
.list-title { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
.list-sub { font-size: 0.75rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px; }
.list-row-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; }
.status-dot.active { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.15); }
.status-dot.inactive { background: #cbd5e1; }
.order-badge { font-size: 0.7rem; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 0.15rem 0.5rem; border-radius: 6px; }
.action-btns { display: flex; gap: 0.25rem; }
.action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; background: transparent; font-size: 0.85rem; }
.action-btn.edit { color: #3b82f6; }
.action-btn.edit:hover { background: #eff6ff; }
.action-btn.delete { color: #ef4444; }
.action-btn.delete:hover { background: #fef2f2; }
.upload-zone { border: 2px dashed #e2e8f0; border-radius: 12px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s; }
.upload-zone:hover { border-color: #3b82f6; background: #f8fafc; }
.upload-placeholder { color: #94a3b8; }
.upload-placeholder i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
.upload-preview { position: relative; display: inline-block; }
.upload-preview img { max-width: 100%; max-height: 120px; border-radius: 8px; }
.upload-remove { position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; border-radius: 50%; border: none; background: #ef4444; color: #fff; font-size: 0.65rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.delete-icon { width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto; }
.empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
</style>
