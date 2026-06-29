<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div v-if="confirmVisible" class="confirm-overlay" @click.self="handleCancel">
        <div class="confirm-dialog" :class="'confirm-' + confirmType">
          <div class="confirm-icon">
            <svg v-if="confirmType === 'danger'" width="48" height="48" fill="#dc3545" viewBox="0 0 16 16">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
            <svg v-else-if="confirmType === 'warning'" width="48" height="48" fill="#ffc107" viewBox="0 0 16 16">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
            <svg v-else width="48" height="48" fill="#0d6efd" viewBox="0 0 16 16">
              <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.399l-.244 0-.084-.418zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
            </svg>
          </div>
          <h5 class="confirm-title">{{ confirmTitle }}</h5>
          <p class="confirm-message">{{ confirmMessage }}</p>
          <div class="confirm-actions">
            <button class="btn btn-secondary" @click="handleCancel">
              {{ cancelText }}
            </button>
            <button class="btn" :class="confirmBtnClass" @click="handleConfirm">
              {{ confirmConfirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useConfirm } from '@/composables/useConfirm'

const {
  confirmVisible,
  confirmTitle,
  confirmMessage,
  confirmType,
  confirmConfirmText,
  cancelText,
  handleConfirm,
  handleCancel
} = useConfirm()

const confirmBtnClass = computed(() => {
  if (confirmType.value === 'danger') return 'btn-danger'
  if (confirmType.value === 'warning') return 'btn-warning'
  return 'btn-primary'
})
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1rem;
}

.confirm-dialog {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: confirm-bounce 0.3s ease;
}

@keyframes confirm-bounce {
  0% { transform: scale(0.9); opacity: 0; }
  50% { transform: scale(1.02); }
  100% { transform: scale(1); opacity: 1; }
}

.confirm-icon { margin-bottom: 1rem; }

.confirm-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}

.confirm-message {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.confirm-actions .btn {
  min-width: 120px;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.6rem 1.5rem;
}

.confirm-fade-enter-active { transition: all 0.25s ease; }
.confirm-fade-leave-active { transition: all 0.2s ease; }
.confirm-fade-enter-from,
.confirm-fade-leave-to { opacity: 0; }
.confirm-fade-enter-from .confirm-dialog { transform: scale(0.9); }
.confirm-fade-leave-to .confirm-dialog { transform: scale(0.9); }
</style>
