<template>
  <div class="admin-sidebar">
    <div class="sidebar-logo">
      <img src="/assets/img/logo.png" alt="FMLider" height="40">
    </div>
    <nav class="sidebar-menu">
      <router-link to="/admin" class="menu-item" active-class="active">
        <i class="icon">📊</i> Dashboard
      </router-link>
      <router-link to="/admin/utilizadores" class="menu-item" active-class="active">
        <i class="icon">👥</i> Utilizadores
      </router-link>
      <router-link to="/admin/servicos" class="menu-item" active-class="active">
        <i class="icon">🔧</i> Serviços
      </router-link>
      <router-link to="/admin/noticias" class="menu-item" active-class="active">
        <i class="icon">📰</i> Notícias
      </router-link>
      <router-link to="/admin/galeria" class="menu-item" active-class="active">
        <i class="icon">🖼️</i> Galeria
      </router-link>
      <router-link to="/admin/parceiros" class="menu-item" active-class="active">
        <i class="icon">🤝</i> Parceiros
      </router-link>
      <router-link to="/admin/contactos" class="menu-item" active-class="active">
        <i class="icon">✉️</i> Contactos
      </router-link>
      <router-link to="/admin/testemunhos" class="menu-item" active-class="active">
        <i class="icon">⭐</i> Testemunhos
      </router-link>
      <router-link to="/admin/faqs" class="menu-item" active-class="active">
        <i class="icon">❓</i> FAQs
      </router-link>
      <router-link to="/admin/banners" class="menu-item" active-class="active">
        <i class="icon">📢</i> Banners
      </router-link>
    </nav>
  </div>
</template>

<style scoped>
.admin-sidebar {
  width: 250px;
  background: #2c3e50;
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  padding: 1rem 0;
  z-index: 1000;
}

.sidebar-logo {
  padding: 1.5rem;
  text-align: center;
  border-bottom: 1px solid #34495e;
  margin-bottom: 1rem;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
}

.menu-item {
  padding: 1rem 1.5rem;
  color: #bdc3c7;
  text-decoration: none;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.menu-item:hover {
  background: #34495e;
  color: white;
}

.menu-item.active {
  background: #3498db;
  border-left-color: #3498db;
  color: white;
}

.icon {
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .admin-sidebar.show {
    transform: translateX(0);
  }
}
</style>
