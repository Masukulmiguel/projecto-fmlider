<template>
  <div class="contact-page">
    <!-- Hero -->
    <section class="contact-hero">
      <div class="container">
        <span class="hero-eyebrow">{{ t('contact.page_title') }}</span>
        <h1 class="hero-title">{{ t('contact.page_title') }}</h1>
        <p class="hero-subtitle">{{ t('contact.hero_subtitle') || 'Estamos prontos para ajudar. Entre em contacto connosco.' }}</p>
      </div>
    </section>

    <!-- Contact Cards + Map -->
    <section class="contact-section">
      <div class="container">
        <div class="row g-4">
          <!-- Info Cards -->
          <div class="col-lg-4">
            <div class="info-cards">
              <div class="info-card">
                <div class="info-icon">
                  <i class="bi bi-telephone-fill"></i>
                </div>
                <div class="info-content">
                  <h6>{{ t('contact.info_phone') }}</h6>
                  <a href="tel:+244935141747">+244 935 141 747</a>
                </div>
              </div>
              <div class="info-card">
                <div class="info-icon icon-email">
                  <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="info-content">
                  <h6>{{ t('contact.info_email') }}</h6>
                  <a href="mailto:geral@fmlider.co.ao">geral@fmlider.co.ao</a>
                </div>
              </div>
              <div class="info-card">
                <div class="info-icon icon-location">
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="info-content">
                  <h6>{{ t('contact.info_address') }}</h6>
                  <p>{{ t('contact.info_address_text') }}</p>
                </div>
              </div>
              <div class="info-card">
                <div class="info-icon icon-clock">
                  <i class="bi bi-clock-fill"></i>
                </div>
                <div class="info-content">
                  <h6>{{ t('contact.hours_title') || 'Horário' }}</h6>
                  <p>{{ t('contact.hours_text') || 'Seg - Sex: 08:00 - 17:00' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Map -->
          <div class="col-lg-8">
            <div class="map-card">
              <iframe
                src="https://maps.google.com/maps?q=FMLider+Base+Cacuaco+Luanda+Angola&hl=pt&z=15&output=embed"
                width="100%"
                height="420"
                style="border:0; border-radius: 16px;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                :title="t('contact.map_title')"
              ></iframe>
              <a href="https://www.google.com/maps/place/FMLider+-+Base/@-8.7693538,13.3973228,359m/data=!3m1!1e3!4m6!3m5!1s0x1a51e5684ed42f1b:0x5630ab6f53efd403!8m2!3d-8.769266!4d13.3984122"
                 target="_blank" rel="noopener" class="map-link">
                <i class="bi bi-box-arrow-up-right"></i> {{ t('contact.map_cta') }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form -->
    <section class="form-section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="form-card">
              <div class="form-header">
                <h3>{{ t('contact.form_title') || 'Envie-nos uma mensagem' }}</h3>
                <p>{{ t('contact.form_subtitle') || 'Preencha o formulário e entraremos em contacto brevemente.' }}</p>
              </div>
              <form @submit.prevent="submitForm" class="contact-form">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="name" v-model="form.name" placeholder=" " required>
                      <label for="name"><i class="bi bi-person me-1"></i> {{ t('contact.form_name') }} *</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="company" v-model="form.company" placeholder=" ">
                      <label for="company"><i class="bi bi-building me-1"></i> {{ t('contact.form_company') }}</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="tel" class="form-control" id="phone" v-model="form.phone" placeholder=" ">
                      <label for="phone"><i class="bi bi-telephone me-1"></i> {{ t('contact.form_phone') }}</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="email" class="form-control" id="email" v-model="form.email" placeholder=" " required>
                      <label for="email"><i class="bi bi-envelope me-1"></i> {{ t('contact.form_email') }} *</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="subject" v-model="form.subject" placeholder=" " required>
                      <label for="subject"><i class="bi bi-chat-left-text me-1"></i> {{ t('contact.form_subject') }} *</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating">
                      <textarea class="form-control" id="message" v-model="form.message" placeholder=" " rows="5" required style="min-height: 140px;"></textarea>
                      <label for="message"><i class="bi bi-pencil me-1"></i> {{ t('contact.form_message') }} *</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-gold btn-lg w-100 submit-btn" :disabled="submitting">
                      <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-send me-2"></i>
                      {{ submitting ? (t('contact.form_sending') || 'A enviar...') : t('contact.form_submit') }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const form = ref({ name: '', company: '', phone: '', email: '', subject: '', message: '' })
const submitting = ref(false)

const submitForm = async () => {
  submitting.value = true
  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const response = await fetch(`${apiBase}/api/contacts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })
    if (response.ok) {
      toast.success(t('contact.form_success'))
      form.value = { name: '', company: '', phone: '', email: '', subject: '', message: '' }
    } else {
      toast.error(t('contact.form_error'))
    }
  } catch (error) {
    toast.error(t('contact.form_error'))
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.contact-page {
  background: var(--content-bg, #f8fafc);
}

/* Hero */
.contact-hero {
  background: linear-gradient(135deg, var(--fml-navy, #0f172a) 0%, var(--fml-blue, #1e3a8a) 100%);
  padding: 8rem 0 4rem;
  text-align: center;
}
.hero-eyebrow {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.75rem;
}
.hero-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fff;
  margin: 0 0 0.75rem;
}
.hero-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  max-width: 560px;
  margin: 0 auto;
}

/* Contact Section */
.contact-section {
  padding: 4rem 0;
}

.info-cards {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-card {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem;
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  transition: all 0.25s ease;
}
.info-card:hover {
  border-color: var(--fml-blue-2, #2563eb);
  box-shadow: 0 4px 20px rgba(37, 99, 235, 0.08);
  transform: translateY(-2px);
}

.info-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
  color: var(--fml-blue-2, #2563eb);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}
.info-icon.icon-email { background: linear-gradient(135deg, #d1fae5, #ecfdf5); color: #047857; }
.info-icon.icon-location { background: linear-gradient(135deg, #fef3c7, #fffbeb); color: #b45309; }
.info-icon.icon-clock { background: linear-gradient(135deg, #ede9fe, #f5f3ff); color: #7c3aed; }

.info-content h6 {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
  margin: 0 0 0.25rem;
}
.info-content p, .info-content a {
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  text-decoration: none;
}
.info-content a:hover {
  color: var(--fml-blue-2, #2563eb);
}

/* Map */
.map-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}
.map-card iframe {
  display: block;
}
.map-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  background: var(--fml-blue-2, #2563eb);
  color: #fff;
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  transition: background 0.2s;
}
.map-link:hover { background: var(--fml-blue, #1e3a8a); color: #fff; }

/* Form Section */
.form-section {
  padding: 0 0 5rem;
}

.form-card {
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.form-header {
  padding: 2rem 2rem 0;
}
.form-header h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem;
}
.form-header p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
}

.contact-form {
  padding: 1.5rem 2rem 2rem;
}

.form-floating > .form-control {
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  font-size: 0.95rem;
  transition: all 0.2s;
}
.form-floating > .form-control:focus {
  border-color: var(--fml-blue-2, #2563eb);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  background: #fff;
}
.form-floating > label {
  color: #94a3b8;
  font-size: 0.9rem;
}

.submit-btn {
  padding: 0.85rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.25s;
}

/* Responsive */
@media (max-width: 768px) {
  .contact-hero { padding: 6rem 0 2.5rem; }
  .hero-title { font-size: 1.8rem; }
  .hero-subtitle { font-size: 0.95rem; }
  .contact-section { padding: 2rem 0; }
  .info-cards { gap: 0.75rem; }
  .info-card { padding: 1rem; }
  .info-icon { width: 42px; height: 42px; font-size: 1rem; }
  .map-card iframe { height: 280px; }
  .form-card { border-radius: 16px; }
  .form-header { padding: 1.5rem 1.25rem 0; }
  .contact-form { padding: 1.25rem; }
  .form-section { padding: 0 0 3rem; }
}

@media (max-width: 576px) {
  .contact-hero { padding: 5rem 0 2rem; }
  .hero-title { font-size: 1.5rem; }
  .info-card { gap: 0.75rem; }
}
</style>
