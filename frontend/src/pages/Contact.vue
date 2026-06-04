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
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3939.0908405305397!2d13.2345!3d-8.8383!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMA1MC0xNSDCsDUwJzEwLjQi!5e0!3m2!1spt!2sao!4v1234567890" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
    const response = await fetch('http://localhost:8000/api/contacts', {
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
