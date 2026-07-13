<template>
  <div class="tracking-page py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h1 class="mb-3">{{ t('tracking.page_title') }}</h1>
        <p class="text-muted">{{ t('tracking.page_subtitle') }}</p>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="tracking-card">
            <form @submit.prevent="track" class="tracking-form">
              <div class="mb-3">
                <label for="trackingInput" class="form-label fw-bold">{{ t('tracking.input_label') }}</label>
                <div class="input-group input-group-lg">
                  <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                  <input
                    id="trackingInput"
                    type="text"
                    class="form-control"
                    v-model="trackingInput"
                    :placeholder="t('tracking.input_placeholder')"
                    :disabled="loading"
                    autocomplete="off"
                    autofocus
                  />
                  <button
                    type="submit"
                    class="btn btn-primary px-4"
                    :disabled="loading || !trackingInput.trim()"
                  >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ loading ? t('tracking.btn_tracking') : t('tracking.btn_track') }}
                  </button>
                </div>
                <div class="form-text">{{ t('tracking.help_text') }}</div>
              </div>
            </form>

            <div class="carrier-examples mt-3">
              <small class="text-muted">{{ t('tracking.example_container') }}:</small>
              <code class="ms-1">HLCU4123456</code> (Hapag) &middot;
              <code>MSCU1234567</code> (MSC) &middot;
              <code>CMAU8765432</code> (CMA CGM) &middot;
              <code>MEDU9091004</code> (MSC)
            </div>
            <div class="carrier-examples mt-1">
              <small class="text-muted">BL:</small>
              <code class="ms-1">GGZ2993831</code> (CMA CGM) &middot;
              <code>266800124</code> (Maersk) &middot;
              <code>S329753028</code> (Grimaldi) &middot;
              <code>LISLAD260393</code> (PSL)
            </div>
          </div>
        </div>
      </div>

      <div v-if="result" class="row justify-content-center mt-4">
        <div class="col-lg-8">
          <div class="result-card">
            <div class="result-header">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <h5 class="mb-1">{{ t('tracking.detected_carrier') }}: <strong>{{ result.carrier }}</strong></h5>
                  <span class="text-muted">{{ t('tracking.input_label') }}: <code>{{ result.input }}</code></span>
                </div>
                <div v-if="result.cached" class="cached-badge">
                  <i class="bi bi-clock-history me-1"></i>
                  {{ t('tracking.cached_info') }}: {{ formatDate(result.cachedAt) }}
                </div>
              </div>
            </div>

            <div v-if="result.redirect" class="result-body text-center py-4">
              <i class="bi bi-box-arrow-up-right display-4 text-primary"></i>
              <p class="mt-3 mb-3">{{ result.message || t('tracking.no_events') }}</p>
              <a :href="result.redirect" target="_blank" rel="noopener" class="btn btn-primary">
                <i class="bi bi-box-arrow-up-right me-2"></i>
                {{ t('tracking.open_carrier') || 'Abrir site do transportador' }}
              </a>
            </div>

            <div v-else-if="result.events && result.events.length > 0" class="result-body">
              <h6 class="mb-3">{{ t('tracking.events_timeline') }}</h6>
              <div class="timeline">
                <div
                  v-for="(event, idx) in result.events"
                  :key="idx"
                  class="timeline-item"
                  :class="{ 'timeline-item--first': idx === 0 }"
                >
                  <div class="timeline-dot"></div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                      <div>
                        <strong class="event-status">{{ event.status }}</strong>
                        <div v-if="event.location" class="event-location text-muted">
                          <i class="bi bi-geo-alt me-1"></i>{{ event.location }}
                        </div>
                      </div>
                      <div class="event-meta text-end">
                        <div v-if="event.date" class="event-date">{{ formatDate(event.date) }}</div>
                        <div v-if="event.vessel" class="event-vessel text-muted">
                          <i class="bi bi-water me-1"></i>{{ event.vessel }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="result-body text-center py-4">
              <i class="bi bi-inbox display-4 text-muted"></i>
              <p class="mt-3 text-muted">{{ result.message || t('tracking.no_events') }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="error" class="row justify-content-center mt-4">
        <div class="col-lg-8">
          <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ error }}
          </div>
        </div>
      </div>

      <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
          <div class="supported-carriers">
            <h6 class="text-center mb-3">{{ t('tracking.carriers_supported') }}</h6>
            <div class="d-flex justify-content-center flex-wrap gap-3">
              <div class="carrier-chip" v-for="c in carriers" :key="c.id">
                <span class="carrier-prefix">{{ c.prefix }}</span>
                <span>{{ c.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

const trackingInput = ref('')
const loading = ref(false)
const result = ref(null)
const error = ref(null)

const carriers = [
  { id: 'hapag', name: 'Hapag-Lloyd', prefix: 'HLCU' },
  { id: 'msc', name: 'MSC', prefix: 'MSCU/MEDU' },
  { id: 'maersk', name: 'Maersk', prefix: 'MSDU/MSKU' },
  { id: 'cmacgm', name: 'CMA CGM', prefix: 'CMAU' },
  { id: 'grimaldi', name: 'Grimaldi', prefix: 'S+9d' },
  { id: 'orey', name: 'OREY', prefix: '10d' },
  { id: 'naiber', name: 'PSL/Naiber', prefix: 'LISLAD' },
]

async function track() {
  const value = trackingInput.value.trim()
  if (!value) return

  loading.value = true
  error.value = null
  result.value = null

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await fetch(`${apiBase}/api/tracking/${encodeURIComponent(value)}`)
    const data = await res.json()

    if (data.success && data.data) {
      result.value = data.data
    } else {
      error.value = data.message || t('tracking.error')
    }
  } catch (e) {
    error.value = t('tracking.error')
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('pt-AO', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateStr
  }
}
</script>

<style scoped>
.tracking-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.tracking-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.tracking-form .input-group-text {
  background: #1a365d;
  color: white;
  border-color: #1a365d;
}

.tracking-form .form-control:focus {
  border-color: #1a365d;
  box-shadow: 0 0 0 0.2rem rgba(26, 54, 93, 0.15);
}

.carrier-examples {
  font-size: 0.85rem;
}

.carrier-examples code {
  background: #e9ecef;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.8rem;
}

.result-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.result-header {
  background: #1a365d;
  color: white;
  padding: 1.25rem 1.5rem;
}

.result-header h5 {
  color: white;
  margin-bottom: 0.25rem;
}

.result-header code {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
}

.cached-badge {
  background: rgba(255, 255, 255, 0.15);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
}

.result-body {
  padding: 1.5rem;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #dee2e6;
}

.timeline-item {
  position: relative;
  padding-bottom: 1.25rem;
}

.timeline-item--last {
  padding-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: -2rem;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #dee2e6;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item--first .timeline-dot {
  background: #d4af37;
  box-shadow: 0 0 0 2px #d4af37;
}

.timeline-content {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 0.85rem 1rem;
}

.timeline-item--first .timeline-content {
  background: #fff8e1;
  border: 1px solid #ffe082;
}

.event-status {
  font-size: 0.95rem;
}

.event-location,
.event-date,
.event-vessel {
  font-size: 0.85rem;
}

.supported-carriers {
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.carrier-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0f4f8;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
}

.carrier-prefix {
  background: #1a365d;
  color: white;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
}

@media (max-width: 576px) {
  .tracking-card {
    padding: 1.25rem;
  }

  .timeline {
    padding-left: 1.5rem;
  }

  .timeline-dot {
    left: -1.5rem;
    width: 12px;
    height: 12px;
  }
}
</style>
