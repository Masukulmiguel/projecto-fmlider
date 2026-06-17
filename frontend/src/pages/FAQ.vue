<template>
  <div class="faq-page">
    <div class="faq-hero">
      <div class="container">
        <h1><i class="bi bi-question-circle-fill me-2"></i>Perguntas Frequentes</h1>
        <p>Encontre respostas às suas dúvidas mais comuns</p>
      </div>
    </div>

    <div class="container py-5">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 text-muted">A carregar perguntas...</p>
      </div>

      <div v-else-if="filteredFaqs.length === 0" class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #94a3b8;"></i>
        <p class="mt-3 text-muted">Nenhuma pergunta encontrada.</p>
      </div>

      <template v-else>
        <div class="faq-search">
          <i class="bi bi-search"></i>
          <input v-model="search" type="text" placeholder="Pesquisar pergunta..." />
        </div>

        <div v-if="categories.length > 1" class="faq-categories">
          <button
            v-for="cat in categories"
            :key="cat"
            class="cat-btn"
            :class="{ active: activeCategory === cat }"
            @click="activeCategory = cat"
          >
            {{ cat || 'Todas' }}
          </button>
        </div>

        <div class="faq-list">
          <div v-for="faq in filteredFaqs" :key="faq.id" class="faq-item" :class="{ open: openId === faq.id }">
            <button class="faq-question" @click="openId = openId === faq.id ? null : faq.id">
              <span>{{ faq.question }}</span>
              <i class="bi" :class="openId === faq.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
            <div v-if="openId === faq.id" class="faq-answer">
              <p>{{ faq.answer }}</p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const faqs = ref([])
const loading = ref(true)
const search = ref('')
const activeCategory = ref('')
const openId = ref(null)

const categories = computed(() => {
  const cats = [...new Set(faqs.value.map(f => f.category || ''))]
  return ['', ...cats.filter(Boolean)]
})

const filteredFaqs = computed(() => {
  let list = faqs.value
  if (activeCategory.value) {
    list = list.filter(f => (f.category || '') === activeCategory.value)
  }
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(f =>
      (f.question || '').toLowerCase().includes(q) ||
      (f.answer || '').toLowerCase().includes(q)
    )
  }
  return list
})

onMounted(async () => {
  try {
    const { data, error } = await supabase
      .from('faqs')
      .select('*')
      .order('order_by')
    if (!error) {
      faqs.value = (data || []).filter(f => f.status === 'published' || f.status === 1)
    }
  } catch (e) {
    faqs.value = []
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.faq-hero {
  background: linear-gradient(135deg, #1877f2, #0d5bbd);
  color: #fff;
  padding: 3rem 0;
  text-align: center;
}
.faq-hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
.faq-hero p { font-size: 1.1rem; opacity: 0.9; margin: 0; }

.faq-search {
  max-width: 600px;
  margin: 0 auto 1.5rem;
  position: relative;
}
.faq-search i {
  position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
  color: #94a3b8;
}
.faq-search input {
  width: 100%;
  padding: 0.85rem 1rem 0.85rem 2.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
}
.faq-search input:focus { border-color: #1877f2; }

.faq-categories {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 2rem;
}
.cat-btn {
  padding: 0.4rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 20px;
  background: #fff;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  color: #64748b;
}
.cat-btn.active { background: #1877f2; color: #fff; border-color: #1877f2; }
.cat-btn:hover:not(.active) { border-color: #1877f2; color: #1877f2; }

.faq-list { max-width: 800px; margin: 0 auto; }

.faq-item {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.faq-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.faq-item.open { border-color: #1877f2; }

.faq-question {
  width: 100%;
  padding: 1rem 1.25rem;
  border: none;
  background: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #1e293b;
  text-align: left;
}
.faq-question i { color: #1877f2; font-size: 0.9rem; flex-shrink: 0; }

.faq-answer {
  padding: 0 1.25rem 1rem;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.6;
}
.faq-answer p { margin: 0; }

@media (max-width: 768px) {
  .faq-hero { padding: 2rem 0; }
  .faq-hero h1 { font-size: 1.5rem; }
}
</style>
