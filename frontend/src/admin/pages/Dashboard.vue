<template>
  <div class="admin-dashboard p-5">
    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="stat-card">
          <h6>Utilizadores</h6>
          <h3>12</h3>
          <small>Usuários registados</small>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-card">
          <h6>Serviços</h6>
          <h3>4</h3>
          <small>Serviços ativos</small>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-card">
          <h6>Notícias</h6>
          <h3>8</h3>
          <small>Notícias publicadas</small>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="stat-card">
          <h6>Contactos</h6>
          <h3>15</h3>
          <small>Mensagens pendentes</small>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Últimos Contactos</h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tbody>
                <tr v-for="i in 5" :key="i">
                  <td>Contacto {{ i }}</td>
                  <td><small class="text-muted">user@example.com</small></td>
                  <td><span class="badge bg-warning">Pendente</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Atividades Recentes</h5>
          </div>
          <div class="card-body">
            <div class="activity-item" v-for="i in 5" :key="i">
              <p class="mb-1">Atividade {{ i }}</p>
              <small class="text-muted">Há alguns minutos</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-dashboard {
  background: #f8f9fa;
  min-height: 100vh;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-card h3 {
  color: #007bff;
  font-size: 2rem;
  margin: 0.5rem 0;
}

.card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

.card-header {
  background: white;
  border-bottom: 1px solid #ddd;
  padding: 1rem;
}

.activity-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #eee;
}

.activity-item:last-child {
  border-bottom: none;
}
</style>
