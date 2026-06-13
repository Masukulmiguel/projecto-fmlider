<template>
  <div class="legal-page">
    <div class="legal-hero">
      <div class="container">
        <h1>Política de Privacidade</h1>
        <p>Última actualização: {{ today }}</p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row">
        <aside class="col-lg-3 d-none d-lg-block">
          <nav class="legal-toc sticky-top">
            <h6 class="text-uppercase text-muted small fw-bold">Nesta página</h6>
            <ul class="list-unstyled">
              <li v-for="(s, i) in sections" :key="i"><a :href="`#p${i+1}`">{{ i+1 }}. {{ s.title }}</a></li>
            </ul>
          </nav>
        </aside>
        <div class="col-lg-9">
          <section v-for="(s, i) in sections" :key="i" :id="`p${i+1}`" class="legal-section">
            <h2>{{ i+1 }}. {{ s.title }}</h2>
            <p v-for="(p, j) in s.body" :key="j" v-html="p"></p>
          </section>

          <div class="alert alert-info mt-4">
            <strong>Encarregado de Protecção de Dados (DPO):</strong> Para exercer os seus direitos ou esclarecer dúvidas,
            contacte-nos em <a href="mailto:privacidade@fmlider.co.ao">privacidade@fmlider.co.ao</a> ou
            <a href="tel:+244935141747">+244 935 141 747</a>.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const today = new Date().toLocaleDateString('pt-PT', { year: 'numeric', month: 'long', day: 'numeric' })
const sections = [
  { title: 'Quem Somos', body: [
    'A FMLider — Logística, Transporte e Serviços, Lda., com sede em <strong>FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco, Luanda, Angola</strong>, NIF nº 5417 123 4567, é a entidade responsável pelo tratamento dos dados pessoais recolhidos através deste website e no âmbito da prestação dos nossos serviços.',
    'Para qualquer questão relacionada com protecção de dados, contacte o nosso Encarregado de Protecção de Dados (DPO) em privacidade@fmlider.co.ao.'
  ]},
  { title: 'Dados que Recolhemos', body: [
    'Recolhemos os seguintes dados pessoais:<ul><li><strong>Dados de identificação:</strong> nome completo, nome da empresa, NIF, número de identificação;</li><li><strong>Dados de contacto:</strong> email, telefone, morada;</li><li><strong>Dados de acesso:</strong> username, palavra-passe (armazenada de forma encriptada);</li><li><strong>Dados de navegação:</strong> endereço IP, tipo de navegador, páginas visitadas, duração da visita (através de cookies);</li><li><strong>Dados de transacção:</strong> histórico de embarques, cotações, documentos, contactos;</li><li><strong>Dados de pagamento:</strong> informações necessárias para processamento de pagamentos (dados bancários parciais).</li></ul>'
  ]},
  { title: 'Finalidades do Tratamento', body: [
    'Os seus dados pessoais são tratados para as seguintes finalidades:<ul><li>Prestação dos serviços de logística, transporte e transitário contratados;</li><li>Gestão da sua conta de cliente e autenticação no website;</li><li>Processamento de cotações, orçamentos e facturas;</li><li>Cumprimento de obrigações legais e fiscais (declarações à AGT, conservação de documentos de transporte);</li><li>Comunicação sobre serviços, alterações de horários, actualizações de embarques;</li><li>Melhoria contínua do website e dos nossos serviços;</li><li>Marketing directo dos nossos serviços (apenas com o seu consentimento explícito).</li></ul>'
  ]},
  { title: 'Base Legal do Tratamento', body: [
    'O tratamento dos seus dados pessoais é realizado com base nas seguintes alíneas do artigo 6º da Lei nº 22/11 (Lei de Protecção de Dados Pessoais de Angola):<ul><li><strong>Execução de contrato</strong> — para prestação dos serviços contratados;</li><li><strong>Cumprimento de obrigação legal</strong> — para conservação de documentos fiscais e de transporte;</li><li><strong>Consentimento</strong> — para marketing directo e cookies não essenciais;</li><li><strong>Interesse legítimo</strong> — para segurança do website, prevenção de fraude e melhoria dos serviços.</li></ul>'
  ]},
  { title: 'Partilha de Dados com Terceiros', body: [
    'A FMLider pode partilhar os seus dados pessoais com:<ul><li><strong>Autoridades públicas</strong> (AGT, autoridades portuárias, aduaneiras) — quando exigido por lei;</li><li><strong>Transportadores e agentes</strong> (companhias marítimas, aéreas, rodoviárias) — para execução do transporte;</li><li><strong>Seguradoras</strong> — quando contrata seguro de carga;</li><li><strong>Processadores de pagamento</strong> (bancos, Multicaixa) — para processar pagamentos;</li><li><strong>Fornecedores de TI</strong> (hosting, email, cloud) — sob contrato de confidencialidade.</li></ul>',
    'Nunca vendemos ou alugamos os seus dados pessoais a terceiros para fins de marketing.'
  ]},
  { title: 'Conservação dos Dados', body: [
    'Os seus dados pessoais são conservados durante o período necessário para cumprir as finalidades para que foram recolhidos, incluindo:<ul><li><strong>Dados de conta:</strong> enquanto a conta estiver activa e por mais 5 anos após o encerramento;</li><li><strong>Documentos fiscais:</strong> 10 anos (conforme legislação fiscal angolana);</li><li><strong>Documentos de transporte:</strong> 5 anos após conclusão do serviço;</li><li><strong>Dados de marketing:</strong> até retirar o consentimento.</li></ul>',
    'Após estes prazos, os dados são eliminados de forma segura ou anonimizados.'
  ]},
  { title: 'Os Seus Direitos', body: [
    'Nos termos da Lei de Protecção de Dados Pessoais de Angola, tem os seguintes direitos:<ul><li><strong>Direito de acesso</strong> — saber quais dados temos sobre si;</li><li><strong>Direito de rectificação</strong> — corrigir dados incorrectos ou incompletos;</li><li><strong>Direito de eliminação</strong> ("direito a ser esquecido") — solicitar a eliminação dos seus dados;</li><li><strong>Direito de limitação</strong> — solicitar a limitação do tratamento;</li><li><strong>Direito de portabilidade</strong> — receber os seus dados em formato estruturado;</li><li><strong>Direito de oposição</strong> — opor-se ao tratamento (incluindo marketing);</li><li><strong>Direito de retirar consentimento</strong> — a qualquer momento, sem afectar a licitude do tratamento anterior.</li></ul>',
    'Para exercer qualquer destes direitos, envie pedido por email para privacidade@fmlider.co.ao. Responderemos no prazo máximo de 30 dias. Tem também o direito de apresentar queixa junto da <strong>APDCAO</strong> (Autoridade de Protecção de Dados de Angola).'
  ]},
  { title: 'Segurança dos Dados', body: [
    'A FMLider implementa medidas técnicas e organizativas adequadas para proteger os seus dados pessoais, incluindo:<ul><li>Encriptação SSL/TLS em todas as comunicações;</li><li>Palavras-passe armazenadas com hash bcrypt;</li><li>Autenticação JWT com expiração;</li><li>Controlo de acesso baseado em funções (RBAC);</li><li>Backups regulares encriptados;</li><li>Firewalls e sistemas de detecção de intrusão;</li><li>Formação regular dos colaboradores sobre protecção de dados;</li><li>Auditorias de segurança periódicas.</li></ul>',
    'Em caso de violação de dados pessoais que possa afectar significativamente os seus direitos, será notificado no prazo de 72 horas, conforme exigido por lei.'
  ]},
  { title: 'Cookies e Tecnologias Semelhantes', body: [
    'O nosso website utiliza cookies para melhorar a sua experiência. Pode gerir as suas preferências no banner de consentimento ou nas definições do seu navegador.',
    '<strong>Cookies essenciais:</strong> necessários para o funcionamento do website (sessão, autenticação). Não podem ser desactivados.<br><strong>Cookies analíticos:</strong> ajudam-nos a entender como utiliza o website (Google Analytics, anonimizado).<br><strong>Cookies de marketing:</strong> utilizados para mostrar anúncios relevantes (apenas com consentimento).'
  ]},
  { title: 'Transferências Internacionais', body: [
    'Alguns dos nossos parceiros (companhias marítimas, aéreas, seguradoras) podem estar localizados fora de Angola. Nestes casos, os seus dados são transferidos apenas para países com nível adequado de protecção de dados ou ao abrigo de cláusulas contratuais tipo aprovadas.',
    'Para envios internacionais, partilhamos apenas os dados estritamente necessários (nome, contacto, descrição da mercadoria) com o transportador responsável pelo trajecto internacional.'
  ]},
  { title: 'Menores de Idade', body: [
    'O website e os serviços da FMLider não se destinam a menores de 18 anos. Não recolhemos intencionalmente dados pessoais de menores. Caso tenha conhecimento de que recolhemos dados de um menor, contacte-nos para procedermos à eliminação imediata.'
  ]},
  { title: 'Alterações a esta Política', body: [
    'A FMLider pode actualizar esta Política de Privacidade periodicamente. A versão actualizada será publicada nesta página, com a data da última actualização indicada no topo. Em caso de alterações substanciais, notificaremos os utilizadores afectados por email.',
    'Recomendamos a consulta regular desta página para se manter informado sobre como protegemos os seus dados.'
  ]}
]
</script>

<style scoped>
.legal-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
  color: #fff;
  padding: 4rem 0 3rem;
  position: relative;
  overflow: hidden;
}
.legal-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 50%, rgba(251, 191, 36, 0.15) 0%, transparent 50%);
  pointer-events: none;
}
.legal-hero h1 {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0;
  position: relative;
  z-index: 1;
}
.legal-hero p {
  opacity: 0.8;
  margin: 0.5rem 0 0;
  position: relative;
  z-index: 1;
}

.legal-toc {
  background: #f8fafc;
  padding: 1.25rem;
  border-radius: 12px;
  border-left: 4px solid #f59e0b;
}
.legal-toc h6 { margin-bottom: 0.75rem; }
.legal-toc ul li { margin-bottom: 0.4rem; }
.legal-toc a {
  color: #475569;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}
.legal-toc a:hover { color: #1e3a8a; }

.legal-section {
  margin-bottom: 2.5rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e2e8f0;
}
.legal-section:last-of-type { border-bottom: none; }
.legal-section h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #f59e0b;
  display: inline-block;
}
.legal-section p {
  color: #334155;
  line-height: 1.75;
  margin-bottom: 1rem;
}
.legal-section :deep(ul) {
  margin: 0.5rem 0 1rem 1.5rem;
  color: #334155;
}
.legal-section :deep(ul li) {
  margin-bottom: 0.4rem;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .legal-hero h1 { font-size: 1.75rem; }
  .legal-hero { padding: 2.5rem 0 2rem; }
}
</style>
