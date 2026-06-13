<template>
  <div class="legal-page">
    <div class="legal-hero">
      <div class="container">
        <h1>Termos e Condições</h1>
        <p>Última actualização: {{ today }}</p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row">
        <aside class="col-lg-3 d-none d-lg-block">
          <nav class="legal-toc sticky-top">
            <h6 class="text-uppercase text-muted small fw-bold">Nesta página</h6>
            <ul class="list-unstyled">
              <li v-for="(s, i) in sections" :key="i"><a :href="`#s${i+1}`">{{ i+1 }}. {{ s.title }}</a></li>
            </ul>
          </nav>
        </aside>
        <div class="col-lg-9">
          <section v-for="(s, i) in sections" :key="i" :id="`s${i+1}`" class="legal-section">
            <h2>{{ i+1 }}. {{ s.title }}</h2>
            <p v-for="(p, j) in s.body" :key="j" v-html="p"></p>
          </section>

          <div class="alert alert-info mt-4">
            <strong>Contacto:</strong> Para questões sobre estes Termos, contacte-nos em
            <a href="mailto:geral@fmlider.co.ao">geral@fmlider.co.ao</a> ou
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
  { title: 'Aceitação dos Termos', body: [
    'Ao aceder e utilizar o website <strong>fmlider.co.ao</strong> e os serviços da FMLider — Logística, Transporte e Serviços, Lda. (adiante designada por "FMLider", "nós" ou "nosso"), o utilizador concorda em cumprir e estar vinculado aos presentes Termos e Condições. Se não concordar com qualquer parte destes termos, não deverá utilizar o nosso website ou contratar os nossos serviços.',
    'A FMLider reserva-se o direito de modificar estes Termos a qualquer momento, mediante publicação da versão actualizada no website. A continuação de utilização após alterações constitui aceitação dos novos termos.'
  ]},
  { title: 'Descrição dos Serviços', body: [
    'A FMLider é uma empresa angolana especializada em soluções integradas de logística, transporte de mercadorias, serviços de transitário, desalfandegamento, armazenagem e mudanças. Operamos em Luanda, com cobertura nacional e internacional.',
    'Os serviços específicos contratados serão detalhados em proposta comercial, conhecimento de embarque (B/L), conhecimento de transporte aéreo (AWB) ou contrato de prestação de serviços celebrado entre as partes.'
  ]},
  { title: 'Cadastro e Conta de Utilizador', body: [
    'Para aceder à área de cliente, o utilizador deve criar uma conta fornecendo informações verdadeiras, exactas e completas. O utilizador é responsável por manter a confidencialidade das suas credenciais de acesso (username e password) e por todas as actividades realizadas na sua conta.',
    'A FMLider reserva-se o direito de suspender ou encerrar contas que violem estes Termos, apresentem informações falsas ou permaneçam inactivas por mais de 12 meses. A aprovação de novos cadastros de clientes está sujeita à análise interna da FMLider.'
  ]},
  { title: 'Orçamentos e Pagamentos', body: [
    'Todos os orçamentos apresentados pela FMLider têm validade de <strong>30 dias</strong> a contar da data de emissão, salvo indicação em contrário. Os preços podem ser expressos em Kwanzas (AOA), Dólares Americanos (USD) ou Euros (EUR) e estão sujeitos às condições de pagamento acordadas.',
    'O pagamento pode ser realizado por transferência bancária, Multicaixa Express, TPA ou numerário (dentro dos limites legais). Para clientes com contrato, podem ser acordados prazos de pagamento a 15 ou 30 dias. A FMLider pode suspender a prestação de serviços em caso de atraso de pagamento superior a 30 dias.'
  ]},
  { title: 'Obrigações do Cliente', body: [
    'O cliente compromete-se a:<ul><li>Fornecer informações verdadeiras e completas sobre a mercadoria (natureza, peso, dimensões, valor);</li><li>Declarar correctamente a mercadoria junto das autoridades aduaneiras;</li><li>Embalar adequadamente a mercadoria para transporte;</li><li>Não expedir mercadorias proibidas por lei ou que violem regulamentos internacionais;</li><li>Proceder ao pagamento nos prazos acordados.</li></ul>',
    'A FMLider reserva-se o direito de recusar mercadorias que considere perigosas, mal embaladas, com declaração inadequada ou que violem qualquer lei aplicável.'
  ]},
  { title: 'Responsabilidade e Seguro', body: [
    'A FMLider actua com a diligência de um bom profissional e em conformidade com os usos e costumes do sector. A nossa responsabilidade está limitada aos termos da Convenção de Bruxelas, Convenção de Montreal, Convenção de Hamburgo ou outras convenções internacionais aplicáveis, bem como aos limites estabelecidos no contrato de transporte.',
    'Recomendamos vivamente a contratação de <strong>seguro de carga All Risks</strong> para cobrir o valor total da mercadoria durante o transporte. A FMLider pode intermediar a contratação do seguro junto de seguradoras parceiras, mediante solicitação do cliente.'
  ]},
  { title: 'Força Maior', body: [
    'A FMLider não será responsável por atrasos ou incumplimentos causados por eventos de força maior, incluindo mas não se limitando a: condições climatéricas adversas, guerras, greves, perturbações da ordem pública, decisões governamentais, pandemias, falhas de fornecedores de serviços (portos, companhias aéreas, transportadores), ou qualquer outro evento fora do nosso controlo razoável.'
  ]},
  { title: 'Propriedade Intelectual', body: [
    'Todo o conteúdo do website — incluindo textos, gráficos, logótipos, imagens, fotografias, vídeos, ícones, código-fonte e design — é propriedade da FMLider ou dos seus licenciadores e está protegido pelas leis angolanas e internacionais de direitos de autor e propriedade intelectual.',
    'É proibida a reprodução, distribuição, modificação ou uso comercial do conteúdo sem autorização prévia por escrito da FMLider.'
  ]},
  { title: 'Limitação de Responsabilidade do Website', body: [
    'O website e o seu conteúdo são fornecidos "tal como estão" e "conforme disponibilidade". A FMLider não garante que o website estará sempre disponível, livre de erros, vírus ou outros componentes nocivos.',
    'A FMLider não se responsabiliza por danos directos, indirectos, incidentais ou consequenciais resultantes da utilização ou impossibilidade de utilização do website, incluindo perda de dados, lucros ou oportunidades de negócio.'
  ]},
  { title: 'Links para Terceiros', body: [
    'O website pode conter links para websites de terceiros (parceiros, transportadoras, seguradoras, portos, etc.). A FMLider não controla nem se responsabiliza pelo conteúdo, políticas de privacidade ou práticas desses websites. A inclusão de qualquer link não implica endosso ou garantia.'
  ]},
  { title: 'Lei Aplicável e Foro', body: [
    'Os presentes Termos são regidos pela <strong>Lei Angolana</strong>. Para a resolução de qualquer litígio emergente da interpretação ou execução destes Termos ou dos serviços prestados pela FMLider, é competente o <strong>Tribunal Provincial de Luanda</strong>, com renúncia expressa a qualquer outro.',
    'Em caso de conflito entre a versão portuguesa e qualquer tradução, prevalece a versão em português.'
  ]},
  { title: 'Disposições Finais', body: [
    'Se alguma disposição destes Termos for considerada inválida ou inaplicável por um tribunal competente, as restantes disposições permanecerão em pleno vigor. A falha da FMLider em exercer qualquer direito previsto nestes Termos não constitui renúncia a esse direito.',
    'Para questões, reclamações ou pedidos de informação, contacte-nos:'
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
