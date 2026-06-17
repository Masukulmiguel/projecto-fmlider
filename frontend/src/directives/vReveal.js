const REVEAL_INSTANCES = new Map()

function getObserver() {
  if (getObserver._instance) return getObserver._instance
  getObserver._instance = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const el = entry.target
          const delay = el.getAttribute('data-reveal-delay') || 0
          setTimeout(() => {
            el.classList.add('revealed')
          }, Number(delay))
          getObserver._instance.unobserve(el)
        }
      })
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
  )
  return getObserver._instance
}

export const vReveal = {
  mounted(el, binding) {
    const type = binding.value || 'up'
    el.classList.add('scroll-reveal', `reveal-${type}`)
    const existingDelay = el.getAttribute('data-reveal-delay')
    if (!existingDelay && binding.arg) {
      el.setAttribute('data-reveal-delay', binding.arg)
    }
    const observer = getObserver()
    observer.observe(el)
    REVEAL_INSTANCES.set(el, observer)
  },
  unmounted(el) {
    const observer = REVEAL_INSTANCES.get(el)
    if (observer) {
      observer.unobserve(el)
      REVEAL_INSTANCES.delete(el)
    }
  },
}
