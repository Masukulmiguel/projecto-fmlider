<template>
  <div class="clients-section" v-if="items.length > 0">
    <div class="carousel-container">
      <button class="carousel-arrow carousel-arrow-left" @click="prevPage" :disabled="currentPage === 0">
        <i class="bi bi-chevron-left"></i>
      </button>

      <div class="carousel-viewport">
        <div class="carousel-track" :style="{ transform: `translateX(${-currentPage * pageSize * (cardWidth + gap)}px)` }">
          <div
            class="client-card"
            v-for="item in items"
            :key="item.id"
          >
            <div class="client-logo-wrapper">
              <img :src="resolveLogo(item.logo)" :alt="item.company_name" class="client-logo">
            </div>
            <div class="client-info">
              <span class="client-name" :title="item.company_name">{{ item.company_name }}</span>
            </div>
          </div>
        </div>
      </div>

      <button class="carousel-arrow carousel-arrow-right" @click="nextPage" :disabled="currentPage >= totalPages - 1">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <div class="carousel-dots" v-if="totalPages > 1">
      <button
        v-for="page in totalPages"
        :key="page"
        class="carousel-dot"
        :class="{ active: currentPage === page - 1 }"
        @click="currentPage = page - 1"
      ></button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const currentPage = ref(0)
const pageSize = 5
const cardWidth = 200
const gap = 32

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('data:')) return logo
  return logo
}

const fetchItems = async () => {
  try {
    const { data, error } = await supabase
      .from('companies')
      .select('id, company_name, logo')
      .eq('is_published', true)
      .order('company_name')
    if (!error && data) {
      items.value = data
    }
  } catch (e) {
    items.value = []
  }
}

const totalPages = computed(() => Math.ceil(items.value.length / pageSize))

const prevPage = () => {
  if (currentPage.value > 0) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value - 1) currentPage.value++
}

let autoInterval = null

onMounted(async () => {
  await fetchItems()
  autoInterval = setInterval(() => {
    if (currentPage.value < totalPages.value - 1) {
      currentPage.value++
    } else {
      currentPage.value = 0
    }
  }, 4000)
})

onBeforeUnmount(() => {
  if (autoInterval) clearInterval(autoInterval)
})
</script>

<style scoped>
.clients-section {
  width: 100%;
  padding: 1rem 0;
}

.carousel-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.carousel-viewport {
  flex: 1;
  overflow: hidden;
  border-radius: 16px;
}

.carousel-track {
  display: flex;
  align-items: center;
  gap: 2rem;
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  padding: 1rem 0;
}

.client-card {
  flex: 0 0 200px;
  min-width: 0;
  overflow: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  background: #ffffff;
  border: 1.5px solid #e5e7eb;
  border-radius: 14px;
  padding: 16px 14px 12px;
  cursor: pointer;
  transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              box-shadow 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              border-color 0.35s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.client-card:hover {
  transform: translateY(-6px) scale(1.03);
  border-color: var(--fml-gold);
  box-shadow: 0 12px 28px rgba(245, 158, 11, 0.18),
              0 4px 10px rgba(245, 158, 11, 0.08);
}

.client-logo-wrapper {
  width: 100%;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.client-logo {
  display: block;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: filter 0.35s ease, transform 0.35s ease;
  filter: grayscale(20%) opacity(0.9);
}

.client-card:hover .client-logo {
  filter: grayscale(0%) opacity(1);
  transform: scale(1.05);
}

.client-info {
  text-align: center;
  width: 100%;
}

.client-name {
  font-size: 0.7rem;
  font-weight: 600;
  color: #374151;
  letter-spacing: 0.3px;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.carousel-arrow {
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 2px solid #e2e8f0;
  background: #ffffff;
  color: var(--fml-gold);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.carousel-arrow:hover:not(:disabled) {
  background: var(--fml-gold);
  border-color: var(--fml-gold);
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
  transform: scale(1.08);
}

.carousel-arrow:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.carousel-dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 1rem;
}

.carousel-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: none;
  background: #d1d5db;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
}

.carousel-dot.active {
  background: var(--fml-gold);
  transform: scale(1.25);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

.carousel-dot:hover:not(.active) {
  background: #9ca3af;
}

@media (max-width: 768px) {
  .carousel-container {
    gap: 0.5rem;
    padding: 0 0.25rem;
  }
  .client-card {
    flex: 0 0 150px;
    padding: 12px 10px 10px;
  }
  .carousel-track {
    gap: 1rem;
  }
  .carousel-arrow {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
  }
  .client-name {
    font-size: 0.6rem;
  }
}

@media (max-width: 480px) {
  .client-card {
    flex: 0 0 130px;
  }
}
</style>
