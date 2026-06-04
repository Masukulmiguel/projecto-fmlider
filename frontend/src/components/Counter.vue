<template>
  <div class="counter">
    <h3 class="counter-value">+{{ animatedValue }}</h3>
    <p class="counter-label">{{ label }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  endValue: {
    type: Number,
    required: true
  },
  label: {
    type: String,
    required: true
  }
})

const animatedValue = ref(0)

onMounted(() => {
  let current = 0
  const increment = props.endValue / 30
  
  const interval = setInterval(() => {
    current += increment
    if (current >= props.endValue) {
      animatedValue.value = props.endValue
      clearInterval(interval)
    } else {
      animatedValue.value = Math.floor(current)
    }
  }, 50)
})
</script>

<style scoped>
.counter {
  text-align: center;
  color: white;
  padding: 2rem;
}

.counter-value {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.counter-label {
  font-size: 1rem;
  margin: 0;
}
</style>
