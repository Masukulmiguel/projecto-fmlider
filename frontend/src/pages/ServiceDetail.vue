<template>
  <div class="service-detail-page py-5">
    <div class="container">
      <router-link to="/servicos" class="btn btn-outline-primary mb-4">← Voltar</router-link>
      <div class="row">
        <div class="col-lg-8">
          <img src="/assets/img/service-aduaneiro.jpg" class="img-fluid rounded mb-4" alt="Service">
          <h1 class="mb-4">{{ $route.params.slug }}</h1>
          <p>Conteúdo detalhado do serviço...</p>
        </div>
        <div class="col-lg-4">
          <div class="cta-box bg-light p-4 rounded">
            <h4>Interessado neste serviço?</h4>
            <p>Entre em contacto connosco para mais informações</p>
            <router-link to="/contacto" class="btn btn-primary w-100">Solicitar Cotação</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.service-detail-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.cta-box {
  border: 2px solid #007bff;
  border-radius: 8px;
  padding: 2rem;
  background: white;
}
</style>
