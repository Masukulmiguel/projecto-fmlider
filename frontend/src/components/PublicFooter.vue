<template>
  <footer class="fml-footer">
    <div class="footer-top">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-4 col-md-6">
            <div class="footer-brand">
              <img src="/assets/img/logo.png" alt="FMLider" class="footer-logo">
              <h4 class="footer-title">{{ settings.company_name || 'FMLider' }}</h4>
            </div>
            <p class="footer-desc">
              {{ settings.company_description || 'Soluções integradas de logística, transporte e serviços de transitário em Angola. Ligamos o seu negócio ao mundo desde 2017.' }}
            </p>
            <div class="social-links">
              <a v-if="settings.facebook" :href="settings.facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
              <a v-if="settings.instagram" :href="settings.instagram" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
              <a v-if="settings.linkedin" :href="settings.linkedin" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
              <a v-if="settings.whatsapp" :href="whatsappUrl" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
              <template v-if="!settings.facebook && !settings.instagram && !settings.linkedin && !settings.whatsapp">
                <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a href="https://wa.me/244935141747" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
              </template>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 col-6">
            <h6 class="footer-heading">Empresa</h6>
            <ul class="footer-links">
              <li><router-link to="/sobre">Sobre Nós</router-link></li>
              <li><router-link to="/servicos">Serviços</router-link></li>
              <li><router-link to="/frota">Frota</router-link></li>
              <li><router-link to="/noticias">Notícias</router-link></li>
              <li><router-link to="/galeria">Galeria</router-link></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 col-6">
            <h6 class="footer-heading">Áreas de Cliente</h6>
            <ul class="footer-links">
              <li><router-link to="/login">Iniciar Sessão</router-link></li>
              <li><router-link to="/registro">Criar Conta</router-link></li>
              <li><router-link to="/dashboard">Dashboard</router-link></li>
              <li><router-link to="/embarques">Rastrear Envio</router-link></li>
              <li><router-link to="/cotacoes/novo">Pedir Cotação</router-link></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6">
            <h6 class="footer-heading">Contacto</h6>
            <ul class="footer-contact">
              <li><i class="bi bi-geo-alt-fill"></i> {{ settings.address || 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco, Luanda' }}</li>
              <li><i class="bi bi-telephone-fill"></i> <a :href="phoneUrl">{{ settings.phone || '+244 935 141 747' }}</a></li>
              <li><i class="bi bi-envelope-fill"></i> <a :href="emailUrl">{{ settings.email || 'geral@fmlider.co.ao' }}</a></li>
              <li><i class="bi bi-clock-fill"></i> {{ settings.working_hours || 'Seg–Sex 08:00–18:00 · Sáb 08:00–13:00' }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <p class="mb-0">© {{ year }} {{ settings.company_name || 'FMLider' }} — Logística, Transporte e Serviços, Lda. Todos os direitos reservados.</p>
          <ul class="legal-links">
            <li><router-link to="/termos">Termos e Condições</router-link></li>
            <li><router-link to="/politicas">Política de Privacidade</router-link></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const year = new Date().getFullYear()

const defaults = {
  company_name: 'FMLider',
  company_description: 'Soluções integradas de logística, transporte e serviços de transitário em Angola. Ligamos o seu negócio ao mundo desde 2017.',
  phone: '+244 935 141 747',
  email: 'geral@fmlider.co.ao',
  address: 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco, Luanda',
  working_hours: 'Seg–Sex 08:00–18:00 · Sáb 08:00–13:00',
  facebook: '',
  instagram: '',
  linkedin: '',
  whatsapp: ''
}

const settings = ref({ ...defaults })

const whatsappUrl = computed(() => {
  const num = (settings.value.whatsapp || '').replace(/[^0-9]/g, '')
  return num ? `https://wa.me/${num}` : '#'
})

const phoneUrl = computed(() => `tel:${(settings.value.phone || '').replace(/\s/g, '')}`)
const emailUrl = computed(() => `mailto:${settings.value.email || 'geral@fmlider.co.ao'}`)

onMounted(async () => {
  try {
    const { data, error } = await supabase.from('settings').select('key, value')
    if (!error && data) {
      const settingsMap = {}
      data.forEach(s => { settingsMap[s.key] = s.value })
      settings.value = { ...defaults, ...settingsMap }
    }
  } catch (e) {
    // keep defaults
  }
})
</script>

<style scoped>
.fml-footer {
  background: var(--fml-navy);
  color: var(--fml-300);
  margin-top: 5rem;
}

.footer-top { padding: 4rem 0 3rem; }

.footer-brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.footer-logo { height: 44px; width: auto; filter: brightness(0) invert(1); }
.footer-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #fff;
  margin: 0;
  letter-spacing: -0.02em;
}

.footer-desc { color: var(--fml-400); line-height: 1.7; margin-bottom: 1.5rem; }

.social-links { display: flex; gap: 0.5rem; }
.social-links a {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 50%;
  color: var(--fml-300);
  font-size: 1.05rem;
  transition: all 0.2s var(--fml-ease);
}
.social-links a:hover {
  background: var(--fml-gold);
  border-color: var(--fml-gold);
  color: var(--fml-900);
  transform: translateY(-2px);
}

.footer-heading {
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 1.25rem;
}

.footer-links, .footer-contact { list-style: none; padding: 0; margin: 0; }
.footer-links li { margin-bottom: 0.6rem; }
.footer-links a {
  color: var(--fml-400);
  font-size: 0.95rem;
  transition: color 0.2s var(--fml-ease), padding 0.2s var(--fml-ease);
  display: inline-block;
}
.footer-links a:hover {
  color: var(--fml-gold);
  padding-left: 4px;
}

.footer-contact li {
  display: flex;
  gap: 0.6rem;
  margin-bottom: 0.85rem;
  color: var(--fml-400);
  font-size: 0.9rem;
  line-height: 1.5;
}
.footer-contact i { color: var(--fml-gold); flex-shrink: 0; margin-top: 2px; }
.footer-contact a { color: var(--fml-300); }
.footer-contact a:hover { color: var(--fml-gold); }

.footer-bottom {
  background: rgba(0, 0, 0, 0.25);
  padding: 1.25rem 0;
  font-size: 0.85rem;
  color: var(--fml-500);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.legal-links { list-style: none; padding: 0; margin: 0; display: flex; gap: 1.5rem; flex-wrap: wrap; }
.legal-links a { color: var(--fml-400); transition: color 0.2s; }
.legal-links a:hover { color: var(--fml-gold); }

@media (max-width: 768px) {
  .footer-top { padding: 2.5rem 0 2rem; }
  .legal-links { justify-content: center; width: 100%; }
}
</style>
