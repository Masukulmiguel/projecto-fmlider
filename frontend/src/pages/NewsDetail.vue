<template>
  <div class="news-detail-page py-5">
    <div class="container">
      <router-link to="/noticias" class="btn btn-outline-primary mb-4">← Voltar</router-link>
      <div class="row">
        <div class="col-lg-8">
          <img src="/assets/img/banner1.jpg" class="img-fluid rounded mb-4" alt="Notícia">
          <h1 class="mb-4">{{ $route.params.slug }}</h1>
          <p class="text-muted">01/01/2024</p>
          <p>Conteúdo detalhado da notícia...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.news-detail-page {
  background: #f9f9f9;
  min-height: 100vh;
}
</style>
