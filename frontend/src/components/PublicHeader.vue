<template>
  <header class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container">
      <router-link to="/" class="navbar-brand">
        <img src="/assets/img/logo.png" alt="FMLider" height="40">
        FMLider
      </router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <router-link to="/" class="nav-link">Início</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/sobre" class="nav-link">Sobre Nós</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/servicos" class="nav-link">Serviços</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/frota" class="nav-link">Frota</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/noticias" class="nav-link">Notícias</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/galeria" class="nav-link">Galeria</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/contacto" class="nav-link">Contactos</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/login" class="btn btn-primary ms-2">Admin</router-link>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<style scoped>
.navbar-brand {
  font-weight: bold;
  color: #007bff !important;
  font-size: 1.5rem;
}

.nav-link {
  color: #333 !important;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #007bff !important;
}
</style>
