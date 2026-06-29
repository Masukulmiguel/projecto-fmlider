import { ref } from 'vue'

const confirmVisible = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmType = ref('warning')
const confirmConfirmText = ref('Confirmar')
const cancelText = ref('Cancelar')
let resolvePromise = null

export function useConfirm() {
  function openConfirm(options = {}) {
    confirmTitle.value = options.title || 'Confirmação'
    confirmMessage.value = options.message || 'Tem certeza?'
    confirmType.value = options.type || 'warning'
    confirmConfirmText.value = options.confirmText || 'Confirmar'
    cancelText.value = options.cancelText || 'Cancelar'
    confirmVisible.value = true

    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  function handleConfirm() {
    confirmVisible.value = false
    if (resolvePromise) resolvePromise(true)
    resolvePromise = null
  }

  function handleCancel() {
    confirmVisible.value = false
    if (resolvePromise) resolvePromise(false)
    resolvePromise = null
  }

  return {
    confirmVisible,
    confirmTitle,
    confirmMessage,
    confirmType,
    confirmConfirmText,
    cancelText,
    confirm: openConfirm,
    handleConfirm,
    handleCancel
  }
}
