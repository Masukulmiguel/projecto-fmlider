<template>
  <footer class="footer bg-dark text-white py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h5>FMLider</h5>
          <p>Transitário & Logística</p>
          <p>Somos especialistas em logística e transportes em Angola.</p>
        </div>
        <div class="col-md-4">
          <h5>Links Rápidos</h5>
          <ul class="list-unstyled">
            <li><router-link to="/sobre" class="text-white-50">Sobre Nós</router-link></li>
            <li><router-link to="/servicos" class="text-white-50">Serviços</router-link></li>
            <li><router-link to="/contacto" class="text-white-50">Contactos</router-link></li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5>Contactos</h5>
          <p>Telefone: +244 935141747</p>
          <p>Email: geral@fmlider.co.ao</p>
          <p>Redes Sociais: <a href="#" class="text-white">Facebook</a></p>
        </div>
      </div>
      <hr>
      <div class="text-center">
        <p>© 2024 FMLider Transitário & Logística. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: #1a1a1a !important;
  margin-top: 5rem;
}

.footer a {
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer a:hover {
  color: #007bff !important;
}
</style>
