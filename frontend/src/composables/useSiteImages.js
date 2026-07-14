import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const siteImagesCache = ref({})
const loaded = ref(false)
const loading = ref(false)

export function useSiteImages() {
  const fetchAll = async () => {
    if (loaded.value) return siteImagesCache.value
    loading.value = true
    try {
      const { data, error } = await supabase
        .from('site_images')
        .select('section, key, image_url, alt_text')
        .eq('status', 1)
      if (!error && data) {
        const map = {}
        data.forEach(row => {
          if (!map[row.section]) map[row.section] = {}
          map[row.section][row.key] = {
            url: row.image_url,
            alt: row.alt_text || '',
          }
        })
        siteImagesCache.value = map
        loaded.value = true
      }
    } catch (e) {
      // keep empty cache
    } finally {
      loading.value = false
    }
    return siteImagesCache.value
  }

  const getImage = (section, key, fallback = '') => {
    return siteImagesCache.value[section]?.[key]?.url || fallback
  }

  const getAlt = (section, key, fallback = '') => {
    return siteImagesCache.value[section]?.[key]?.alt || fallback
  }

  const invalidate = () => {
    loaded.value = false
    siteImagesCache.value = {}
  }

  return {
    siteImages: siteImagesCache,
    loaded,
    loading,
    fetchAll,
    getImage,
    getAlt,
    invalidate,
  }
}
