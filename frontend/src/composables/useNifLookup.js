import { ref } from 'vue'
import axios from 'axios'

export function useNifLookup() {
  const loading = ref(false)
  const error = ref('')
  const result = ref(null)
  const lookupMessage = ref('')

  let abortController = null
  let debounceTimer = null

  const lookupNif = (nif) => {
    return new Promise((resolve) => {
      if (debounceTimer) clearTimeout(debounceTimer)

      if (!nif || nif.length < 10) {
        result.value = null
        error.value = ''
        lookupMessage.value = ''
        resolve(null)
        return
      }

      if (!/^\d{10}$/.test(nif)) {
        result.value = null
        error.value = ''
        lookupMessage.value = ''
        resolve(null)
        return
      }

      debounceTimer = setTimeout(async () => {
        if (abortController) abortController.abort()
        abortController = new AbortController()

        loading.value = true
        error.value = ''
        lookupMessage.value = ''

        try {
          const { data } = await axios.get(`/api/nif-lookup/${nif}`, {
            signal: abortController.signal,
            timeout: 20000,
          })

          if (data.success && data.data) {
            result.value = data.data
            lookupMessage.value = data.data.nome || ''
            error.value = ''
            resolve(data.data)
          } else {
            result.value = null
            error.value = data.message || 'NIF não encontrado'
            lookupMessage.value = ''
            resolve(null)
          }
        } catch (err) {
          if (err.name === 'CanceledError' || err.code === 'ERR_CANCELED') {
            resolve(null)
            return
          }
          result.value = null
          if (err.response?.status === 404) {
            error.value = 'NIF não encontrado no portal da AGT'
          } else {
            error.value = 'Erro ao consultar NIF. Tente novamente.'
          }
          lookupMessage.value = ''
          resolve(null)
        } finally {
          loading.value = false
        }
      }, 800)
    })
  }

  const clearLookup = () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    if (abortController) abortController.abort()
    loading.value = false
    error.value = ''
    result.value = null
    lookupMessage.value = ''
  }

  return { loading, error, result, lookupMessage, lookupNif, clearLookup }
}
