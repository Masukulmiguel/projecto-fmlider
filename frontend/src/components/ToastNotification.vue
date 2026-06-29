<template>
  <Teleport to="body">
    <TransitionGroup name="toast" tag="div" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
      <div v-for="t in toasts" :key="t.id" class="toast show d-flex align-items-center border-0 shadow-lg mb-2" :class="'toast-' + t.type" role="alert">
        <div class="toast-body d-flex align-items-center gap-2 py-2">
          <svg v-if="t.type === 'success'" class="flex-shrink-0" width="20" height="20" fill="#198754" viewBox="0 0 16 16">
            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
          </svg>
          <svg v-else-if="t.type === 'danger'" class="flex-shrink-0" width="20" height="20" fill="#dc3545" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
          </svg>
          <svg v-else-if="t.type === 'warning'" class="flex-shrink-0" width="20" height="20" fill="#ffc107" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
          </svg>
          <svg v-else class="flex-shrink-0" width="20" height="20" fill="#0d6efd" viewBox="0 0 16 16">
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.399l-.244 0-.084-.418zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
          </svg>
          <span class="small">{{ t.message }}</span>
        </div>
        <button type="button" class="btn-close btn-close-sm me-2 m-auto" @click="removeToast(t.id)"></button>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'
const { toasts, removeToast } = useToast()
</script>

<style scoped>
.toast-container {
  max-width: 380px;
  pointer-events: none;
}

.toast {
  pointer-events: auto;
  border-radius: 10px;
  min-width: 280px;
  background: #fff;
}

.toast-success {
  border-left: 4px solid #198754;
}
.toast-danger {
  border-left: 4px solid #dc3545;
}
.toast-warning {
  border-left: 4px solid #ffc107;
}
.toast-info {
  border-left: 4px solid #0d6efd;
}

.toast-enter-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(80px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(80px);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
