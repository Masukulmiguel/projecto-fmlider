<template>
  <div class="partners-section" v-if="items.length > 0">
    <div class="carousel-container">
      <button class="carousel-arrow carousel-arrow-left" @click="prevPage" :disabled="currentPage === 0">
        <i class="bi bi-chevron-left"></i>
      </button>

      <div class="carousel-viewport">
        <div class="carousel-track" :style="{ transform: `translateX(${-currentPage * pageSize * (cardWidth + gap)}px)` }">
          <div
            class="logo-frame"
            v-for="item in items"
            :key="item.id"
          >
            <img :src="resolveLogo(item.logo)" :alt="item.name" class="partner-logo">
            <div class="logo-overlay">
              <span class="logo-name">{{ item.name }}</span>
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
const cardWidth = 180
const gap = 32

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('/')) return logo
  return `/backend/storage/uploads/partners/${logo}`
}

const fetchItems = async () => {
  try {
    const { data, error } = await supabase.from('partners').select('id, name, logo, description').eq('status', 1).order('order_by')
    if (!error && data) {
      items.value = data.map(p => ({ ...p, name: p.name }))
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
  }, 5000)
})

onBeforeUnmount(() => {
  if (autoInterval) clearInterval(autoInterval)
})
</script>

<style scoped>
.partners-section {
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

.logo-frame {
  flex: 0 0 200px;
  position: relative;
  height: 130px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.08);
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 14px;
  padding: 14px;
  cursor: pointer;
  transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              box-shadow 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94),
              border-color 0.35s ease,
              background 0.35s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.logo-frame:hover {
  transform: translateY(-6px) scale(1.03);
  border-color: var(--fml-gold);
  background: rgba(255, 255, 255, 0.12);
  box-shadow: 0 12px 28px rgba(245, 158, 11, 0.2),
              0 4px 10px rgba(245, 158, 11, 0.1);
}

.partner-logo {
  display: block;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: filter 0.35s ease, transform 0.35s ease;
  filter: brightness(1.1) grayscale(20%) opacity(0.9);
}

.logo-frame:hover .partner-logo {
  filter: brightness(1.2) grayscale(0%) opacity(1);
  transform: scale(1.05);
}

.logo-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 8px;
  background: linear-gradient(to top, rgba(212, 175, 55, 0.9), transparent);
  border-radius: 0 0 13px 13px;
  opacity: 0;
  transform: translateY(4px);
  transition: opacity 0.3s ease, transform 0.3s ease;
  pointer-events: none;
}

.logo-frame:hover .logo-overlay {
  opacity: 1;
  transform: translateY(0);
}

.logo-name {
  font-size: 0.65rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.carousel-arrow {
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.08);
  color: var(--fml-gold);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
  background: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
}

.carousel-dot.active {
  background: var(--fml-gold);
  transform: scale(1.25);
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
}

.carousel-dot:hover:not(.active) {
  background: rgba(255, 255, 255, 0.5);
}

@media (max-width: 768px) {
  .carousel-container {
    gap: 0.5rem;
    padding: 0 0.25rem;
  }
  .logo-frame {
    flex: 0 0 160px;
    height: 110px;
    padding: 12px;
  }
  .carousel-track {
    gap: 1rem;
  }
  .carousel-arrow {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
  }
  .logo-name {
    font-size: 0.55rem;
  }
}

@media (max-width: 480px) {
  .logo-frame {
    flex: 0 0 140px;
    height: 95px;
  }
}
</style>
