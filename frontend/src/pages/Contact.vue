<template>
  <div class="contact-page py-5">
    <div class="container">
      <h1 class="mb-5">Entre em Contacto</h1>
      <div class="row">
        <div class="col-lg-6">
          <div class="contact-info">
            <div class="info-item mb-4">
              <h5>Telefone</h5>
              <p>+244 935141747</p>
            </div>
            <div class="info-item mb-4">
              <h5>Email</h5>
              <p>geral@fmlider.co.ao</p>
            </div>
            <div class="info-item mb-4">
              <h5>Morada</h5>
              <p>FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco</p>
            </div>
          </div>
          <div class="mt-4">
            <h5>Localização</h5>
            <div class="map-wrapper">
              <iframe
                src="https://maps.google.com/maps?q=FMLider+Base+Cacuaco+Luanda+Angola&hl=pt&z=15&output=embed"
                width="100%"
                height="320"
                style="border:0; border-radius: 12px;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Localização FMLider"
              ></iframe>
            </div>
            <a href="https://www.google.com/maps/place/FMLider+-+Base/@-8.7693538,13.3973228,359m/data=!3m1!1e3!4m6!3m5!1s0x1a51e5684ed42f1b:0x5630ab6f53efd403!8m2!3d-8.769266!4d13.3984122"
               target="_blank"
               rel="noopener"
               class="btn btn-outline-primary btn-sm mt-2 w-100">
              <i class="bi bi-geo-alt-fill me-1"></i> Abrir no Google Maps
            </a>
          </div>
        </div>
        <div class="col-lg-6">
          <form @submit.prevent="submitForm" class="contact-form">
            <div class="mb-3">
              <label for="name" class="form-label">Nome</label>
              <input type="text" class="form-control" id="name" v-model="form.name" required>
            </div>
            <div class="mb-3">
              <label for="company" class="form-label">Empresa</label>
              <input type="text" class="form-control" id="company" v-model="form.company">
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Telefone</label>
              <input type="tel" class="form-control" id="phone" v-model="form.phone">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" v-model="form.email" required>
            </div>
            <div class="mb-3">
              <label for="subject" class="form-label">Assunto</label>
              <input type="text" class="form-control" id="subject" v-model="form.subject" required>
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">Mensagem</label>
              <textarea class="form-control" id="message" rows="5" v-model="form.message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">Enviar Mensagem</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  name: '',
  company: '',
  phone: '',
  email: '',
  subject: '',
  message: ''
})

const submitForm = async () => {
  try {
    const response = await fetch('/api/contacts', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      alert('Mensagem enviada com sucesso!')
      form.value = { name: '', company: '', phone: '', email: '', subject: '', message: '' }
    }
  } catch (error) {
    alert('Erro ao enviar mensagem')
  }
}
</script>

<style scoped>
.contact-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.contact-info {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.contact-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
