const pptxgen = require('pptxgenjs');
const path = require('path');
const fs = require('fs');

const pptx = new pptxgen();
pptx.defineLayout({ name: 'WIDE', width: 13.33, height: 7.5 });
pptx.layout = 'WIDE';

const BLUE_DARK = '1a365d';
const BLUE_MED = '2a4a7f';
const BLUE_LIGHT = '3b6cb4';
const GOLD = 'd4af37';
const GOLD_LIGHT = 'f0d060';
const WHITE = 'ffffff';
const GRAY_LIGHT = 'f5f7fa';
const GRAY_MED = 'e2e8f0';
const GRAY_DARK = '4a5568';
const DARK_BG = '0f1b2d';

const logoPath = path.join(__dirname, '..', 'frontend', 'public', 'assets', 'img', 'logo.png');

function addFooter(slide) {
  slide.addShape(pptx.ShapeType.rect, { x: 0, y: 7.0, w: 13.33, h: 0.5, fill: { color: BLUE_DARK } });
  slide.addText('FMLider Transitário & Logística', {
    x: 0.5, y: 7.05, w: 5, h: 0.4,
    fontSize: 9, color: GOLD, fontFace: 'Calibri', bold: true
  });
  slide.addText('Proposta Comercial | ' + new Date().toLocaleDateString('pt-PT'), {
    x: 8, y: 7.05, w: 5, h: 0.4,
    fontSize: 9, color: WHITE, fontFace: 'Calibri', align: 'right'
  });
}

function addSectionHeader(slide, title, subtitle) {
  slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: 13.33, h: 1.4, fill: { color: BLUE_DARK } });
  slide.addShape(pptx.ShapeType.rect, { x: 0.5, y: 1.35, w: 2.5, h: 0.06, fill: { color: GOLD } });
  slide.addText(title, {
    x: 0.5, y: 0.3, w: 12, h: 0.7,
    fontSize: 30, color: WHITE, fontFace: 'Calibri', bold: true
  });
  if (subtitle) {
    slide.addText(subtitle, {
      x: 0.5, y: 0.9, w: 12, h: 0.4,
      fontSize: 14, color: GOLD_LIGHT, fontFace: 'Calibri'
    });
  }
}

function addCard(slide, x, y, w, h, icon, title, bullets, opts = {}) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x, y, w, h,
    fill: { color: opts.fill || WHITE },
    rectRadius: 0.1,
    shadow: { type: 'outer', blur: 4, offset: 2, color: '888888', opacity: 0.2 }
  });

  slide.addShape(pptx.ShapeType.rect, {
    x: x + 0.15, y, w: w - 0.3, h: 0.06, fill: { color: opts.accent || GOLD }
  });

  slide.addText(icon, {
    x: x + 0.2, y: y + 0.15, w: 0.5, h: 0.5,
    fontSize: 22, color: opts.accent || GOLD, fontFace: 'Calibri', align: 'center', valign: 'middle'
  });

  slide.addText(title, {
    x: x + 0.7, y: y + 0.15, w: w - 1, h: 0.45,
    fontSize: 14, color: BLUE_DARK, fontFace: 'Calibri', bold: true, valign: 'middle'
  });

  if (bullets && bullets.length) {
    const bulletText = bullets.map(b => ({ text: b, options: { bullet: { code: '2022' }, color: GRAY_DARK, fontSize: 10.5, fontFace: 'Calibri', lineSpacingMultiple: 1.15 } }));
    slide.addText(bulletText, {
      x: x + 0.3, y: y + 0.65, w: w - 0.6, h: h - 0.8,
      valign: 'top', paraSpaceAfter: 3
    });
  }
}

async function generate() {
  // ========== SLIDE 1: CAPA ==========
  {
    const slide = pptx.addSlide();
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: 13.33, h: 7.5, fill: { color: DARK_BG } });

    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 2.8, w: 13.33, h: 2.2, fill: { color: BLUE_DARK }, transparency: 30 });

    slide.addShape(pptx.ShapeType.rect, { x: 0.5, y: 4.9, w: 3, h: 0.06, fill: { color: GOLD } });

    if (fs.existsSync(logoPath)) {
      slide.addImage({ path: logoPath, x: 5.42, y: 0.6, w: 2.5, h: 2.0 });
    }

    slide.addText('PROPOSTA COMERCIAL', {
      x: 0.5, y: 2.9, w: 12.33, h: 0.7,
      fontSize: 16, color: GOLD, fontFace: 'Calibri', align: 'center', letterSpacing: 8
    });

    slide.addText('Sistema Web Integrado\nde Gestão e Rastreamento', {
      x: 0.5, y: 3.5, w: 12.33, h: 1.2,
      fontSize: 32, color: WHITE, fontFace: 'Calibri', bold: true, align: 'center',
      lineSpacingMultiple: 1.2
    });

    slide.addText('FMLider Transitário & Logística', {
      x: 0.5, y: 5.1, w: 12.33, h: 0.5,
      fontSize: 20, color: GOLD, fontFace: 'Calibri', bold: true, align: 'center'
    });

    slide.addText('fmlider.co.ao', {
      x: 0.5, y: 5.6, w: 12.33, h: 0.4,
      fontSize: 13, color: GRAY_MED, fontFace: 'Calibri', align: 'center'
    });

    slide.addText('Preparado por:', {
      x: 0.5, y: 5.8, w: 12.33, h: 0.3,
      fontSize: 10, color: GRAY_DARK, fontFace: 'Calibri', align: 'center'
    });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 5.42, y: 6.0, w: 2.5, h: 0.6,
      fill: { color: BLUE_MED },
      rectRadius: 0.08,
      line: { color: GOLD, width: 1.5 }
    });

    slide.addText('CODINGLIFEDEV', {
      x: 5.42, y: 6.0, w: 2.5, h: 0.6,
      fontSize: 14, color: GOLD, fontFace: 'Calibri', bold: true, align: 'center', valign: 'middle', letterSpacing: 3
    });

    slide.addText('Masukulu Miguel', {
      x: 0.5, y: 6.6, w: 12.33, h: 0.3,
      fontSize: 10, color: GRAY_MED, fontFace: 'Calibri', align: 'center'
    });

    slide.addText(new Date().toLocaleDateString('pt-PT', { year: 'numeric', month: 'long', day: 'numeric' }), {
      x: 0.5, y: 6.6, w: 12.33, h: 0.3,
      fontSize: 10, color: GRAY_DARK, fontFace: 'Calibri', align: 'center'
    });
  }

  // ========== SLIDE 2: SOBRE A FMLIDER ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Sobre a FMLider', 'Quem somos e o que fazemos');

    addCard(slide, 0.5, 1.8, 3.8, 2.5, '🏭', 'A Empresa', [
      'Fundada em Fevereiro 2017 em Luanda',
      '60+ colaboradores',
      'Instalações próprias na Vidrul - Cacuaco',
      'Frota de camiões própria',
      'Base de operações em Luanda'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.5, '🌍', 'Presença Internacional', [
      'Agentes em 32 países',
      'Parcerias com operadores de referência',
      'Cobertura global de logística',
      'Experiência em mercados internacionais'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.5, '🏆', 'Reconhecimento', [
      'Bom nome no mercado angolano',
      'Enfrentamento das dificuldades económicas',
      'Investimento contínuo em infraestruturas',
      'Credibilidade comprovada'
    ]);

    addCard(slide, 0.5, 4.6, 5.9, 2.0, '🔧', 'Novo Investimento - Reachstacker Kalmar', [
      'Adquirido em 2022 para manuseio de contentores',
      'Resposta mais célere e eficaz aos clientes',
      'Mais-valia para a estrutura da empresa'
    ]);

    addCard(slide, 6.8, 4.6, 6.1, 2.0, '🏗️', 'Novas Instalações', [
      'Novo escritório na Base Vidrul - Cacuaco',
      'Escritório em Luanda para documentos',
      'Espaços de armazém personalizados',
      'Armazenagem de contentores e cargas a granel'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 3: VISÃO GERAL DO SISTEMA ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Visão Geral do Sistema', 'Plataforma completa para gestão logística');

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.5, y: 1.8, w: 12.33, h: 1.2,
      fill: { color: BLUE_LIGHT }, rectRadius: 0.1
    });

    slide.addText([
      { text: 'Stack Tecnológica:  ', options: { color: GOLD, fontSize: 14, bold: true, fontFace: 'Calibri' } },
      { text: 'Vue.js 3  •  Vite  •  Bootstrap 5  •  Supabase  •  PHP  •  MySQL  •  Groq AI', options: { color: WHITE, fontSize: 13, fontFace: 'Calibri' } }
    ], { x: 0.8, y: 1.85, w: 11.7, h: 1.1, valign: 'middle', align: 'center' });

    addCard(slide, 0.5, 3.3, 3.8, 1.8, '🌐', 'Frontend (Vue 3)', [
      'Site público responsivo',
      'Páginas: Home, Serviços, Contactos...',
      'Internacionalização PT/EN/FR',
      'Design mobile-first premium'
    ]);

    addCard(slide, 4.8, 3.3, 3.8, 1.8, '⚡', 'Backend (API)', [
      'REST API em PHP',
      'Endpoints para gestão completa',
      'Serverless na Vercel',
      'Integração com Supabase'
    ]);

    addCard(slide, 9.1, 3.3, 3.8, 1.8, '🗄️', 'Base de Dados', [
      'MySQL via Supabase (PostgreSQL)',
      'Tabelas: empresas, contentores...',
      'RLS (Row Level Security)',
      'Backups automáticos'
    ]);

    addCard(slide, 0.5, 5.4, 5.9, 1.2, '🤖', 'Integrações IA', [
      'Chatbot com Groq API (Llama 3.3 70B)',
      'Lookup automático de BI/NIF',
      'Rastreamento de contentores em tempo real'
    ]);

    addCard(slide, 6.8, 5.4, 6.1, 1.2, '🔐', 'Segurança', [
      'Autenticação Supabase com JWT',
      'Row Level Security em todas as tabelas',
      'Auto-logout ao fechar o browser',
      'Variáveis de ambiente protegidas'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 4: SITE PÚBLICO ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Site Público', 'Páginas e funcionalidades para visitantes');

    addCard(slide, 0.5, 1.8, 3.8, 2.4, '🏠', 'Páginas Principais', [
      'Home - Apresentação e destaques',
      'Serviços - Descrição completa dos serviços',
      'Contactos - Formulário funcional + mapa',
      'Sobre Nós - História da empresa',
      'Notícias - Blog e actualizações',
      'Galeria - Fotos e vídeos'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.4, '🚛', 'Áreas Especializadas', [
      'Frota - Veículos e equipamentos',
      'FAQ - Perguntas frequentes com pesquisa',
      'Parceiros - Rede de parceiros globais',
      'Armazenagem - Serviços de armazém',
      'Transportes - Frota própria e parceiros',
      'Serviços Aduaneiros - Desembaraço completo'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.4, '✨', 'Funcionalidades', [
      'Internacionalização PT/EN/FR',
      'Design responsivo mobile-first',
      'Animações profissionais',
      'SEO otimizado',
      'Header sticky/fixed',
      'Imagens de fundo configuráveis'
    ]);

    addCard(slide, 0.5, 4.5, 5.9, 2.0, '🔍', 'Rastreamento de Contentores', [
      'Lookup por número de BL ou contentor',
      'Estado em tempo real: Aguardando → Em Trânsito → Entregue',
      'Notificações automáticas por email',
      'Histórico completo de movimentação'
    ]);

    addCard(slide, 6.8, 4.5, 6.1, 2.0, '📋', 'Lookup de BI/NIF', [
      'Verificação automática de documentos',
      'Integração com base de dados nacional',
      'Validação em tempo real',
      'Resultado instantâneo no formulário de contacto'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 5: PAINEL DO CLIENTE ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Painel do Cliente', 'Dashboard personalizado para cada cliente');

    addCard(slide, 0.5, 1.8, 3.8, 2.4, '📊', 'Dashboard', [
      'Visão geral de embarques',
      'KPIs em tempo real',
      'Gráficos de desempenho',
      'Notificações pendentes',
      'Atalhos para acções rápidas'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.4, '📦', 'Gestão de Embarques', [
      'Lista de todos os embarques',
      'Filtros por estado e data',
      'Detalhe completo de cada embarque',
      'Documentos anexos',
      'Timeline de movimentação'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.4, '💬', 'Comunicação', [
      'Chat integrado com IA',
      'Mensagens directas com a equipa',
      'Notificações push',
      'Email automático de actualizações',
      'Suporte técnico integrado'
    ]);

    addCard(slide, 0.5, 4.5, 6.1, 2.0, '📄', 'Documentos', [
      'Cotações e orçamentos',
      'Guias de remessa',
      'Facturas e recibos',
      'Certificados de origem',
      'Download em PDF'
    ]);

    addCard(slide, 6.8, 4.5, 6.1, 2.0, '📈', 'Relatórios', [
      'Histórico de embarques',
      'Análise de custos',
      'Tempos médios de trânsito',
      'Exportação para Excel/PDF',
      'Gráficos interactivos'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 6: PAINEL ADMIN ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Painel de Administração', 'Controlo total sobre o sistema');

    addCard(slide, 0.5, 1.8, 2.9, 2.2, '👥', 'Utilizadores', [
      'CRUD completo de users',
      'Roles: Admin, Funcionário, Cliente',
      'Atribuição de empresas',
      'Activar/desactivar contas',
      'Reset de password'
    ]);

    addCard(slide, 3.8, 1.8, 2.9, 2.2, '🏢', 'Empresas', [
      'Gestão de clientes',
      'Dados fiscais (BI/NIF)',
      'Contactos e morada',
      'Histórico de embarques',
      'Documentos associados'
    ]);

    addCard(slide, 7.1, 1.8, 2.9, 2.2, '📦', 'Contentores', [
      'Registo de contentores',
      'Estado e localização',
      'Histórico de movimentação',
      'Alertas automáticos',
      'Vinculação a BL'
    ]);

    addCard(slide, 10.4, 1.8, 2.5, 2.2, '🚢', 'Embarques', [
      'Criar/editar embarques',
      'Atribuir contentores',
      'Actualizar estado',
      'Timeline automática',
      'Notificações'
    ]);

    addCard(slide, 0.5, 4.3, 2.9, 2.2, '⚙️', 'Processos', [
      'Gestão de processos aduaneiros',
      'Estados e prazos',
      'Documentação',
      'Atribuição a funcionários',
      'Relatórios de produtividade'
    ]);

    addCard(slide, 3.8, 4.3, 2.9, 2.2, '📋', 'Cotações', [
      'Criar/editar cotações',
      'Enviar por email',
      'Aprovação online',
      'Conversão em embarque',
      'Histórico'
    ]);

    addCard(slide, 7.1, 4.3, 2.9, 2.2, '🖼️', 'Conteúdo', [
      'Slider de imagens',
      'Notícias e blog',
      'Galeria de fotos',
      'Páginas estáticas',
      'Configurações do site'
    ]);

    addCard(slide, 10.4, 4.3, 2.5, 2.2, '📊', 'Contactos', [
      'Mensagens recebidas',
      'Marcar como lido',
      'Responder directamente',
      'Eliminar mensagens',
      'Estatísticas'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 7: BACKEND & API ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Backend & API', 'Arquitectura técnica do sistema');

    addCard(slide, 0.5, 1.8, 5.9, 2.5, '⚡', 'REST API (PHP)', [
      'Autenticação JWT via Supabase',
      'CRUD para todas as entidades',
      'Validação server-side',
      'Tratamento de erros robusto',
      'Headers CORS configurados',
      'Rate limiting e protecção'
    ]);

    addCard(slide, 6.8, 1.8, 6.0, 2.5, '🗄️', 'Base de Dados (Supabase)', [
      'PostgreSQL gerido pela Supabase',
      'Row Level Security (RLS) em todas as tabelas',
      'Realtime subscriptions',
      'Edge Functions para lógica complexa',
      'Storage para uploads (imagens, docs)',
      'Backups automáticos diários'
    ]);

    addCard(slide, 0.5, 4.6, 5.9, 2.0, '🔧', 'Endpoints Principais', [
      'auth/* - Autenticação e sessões',
      'companies - Gestão de empresas',
      'containers - Contentores e embarques',
      'quotes - Cotações e orçamentos',
      'processes - Processos aduaneiros',
      'shipments - Gestão de embarques',
      'site_images - Imagens do site'
    ]);

    addCard(slide, 6.8, 4.6, 6.0, 2.0, '🚀', 'Deploy & Infraestrutura', [
      'Frontend: Vercel (CDN global)',
      'Backend: Serverless functions na Vercel',
      'Base de dados: Supabase Cloud',
      'DNS: fmlider.co.ao (HostGator)',
      'SSL/TLS automático',
      'CI/CD via GitHub → Vercel'
    ]);

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.5, y: 6.55, w: 12.33, h: 0.35,
      fill: { color: GOLD },
      rectRadius: 0.05
    });

    slide.addText('Demo ao vivo:  projecto-fmlider.vercel.app  →  Fase de testes antes de ir para fmlider.co.ao', {
      x: 0.5, y: 6.55, w: 12.33, h: 0.35,
      fontSize: 10, color: BLUE_DARK, fontFace: 'Calibri', bold: true, align: 'center', valign: 'middle'
    });

    addFooter(slide);
  }

  // ========== SLIDE 8: SEGURANÇA ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Segurança', 'Medidas de protecção implementadas');

    addCard(slide, 0.5, 1.8, 3.8, 2.5, '🔐', 'Autenticação', [
      'Supabase Auth com JWT',
      'Sessões seguras e expiração',
      'Auto-logout ao fechar browser',
      'Proteção contra XSS e CSRF',
      'Rate limiting em endpoints',
      'Validação de inputs'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.5, '🛡️', 'Row Level Security', [
      'RLS em todas as tabelas Supabase',
      'Políticas por role (admin/func/cliente)',
      'Leitura: apenas dados autorizados',
      'Escrita: apenas owner ou admin',
      'Sem acesso anónimo a dados sensíveis',
      'Auditoria de acessos'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.5, '🔒', 'Protecções Avançadas', [
      'Variáveis de ambiente encriptadas',
      'Sem chaves no código fonte',
      'Service role key protegida',
      'CORS restrito a domínios',
      'Content Security Policy',
      'Proteção contra SQL injection'
    ]);

    addCard(slide, 0.5, 4.6, 12.33, 1.8, '📋', 'Políticas de Segurança por Tabela', [
      'empresas: admin pode tudo, funcionario lê, cliente vê apenas a sua empresa',
      'containers: admin/func CRUD completo, cliente apenas visualiza os seus',
      'shipments: admin/func gerem, cliente acompanha os seus embarques',
      'quotes: admin/func criam e editam, cliente submete e visualiza',
      'processes: admin/func gerem estados, cliente acompanha',
      'site_images: admin gerencia imagens do site, público lê'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 9: FUNCIONALIDADES ESPECIAIS ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Funcionalidades Especiais', 'Diferenciais do sistema');

    addCard(slide, 0.5, 1.8, 3.8, 2.5, '🤖', 'Chatbot com IA', [
      'Integração com Groq API',
      'Modelo Llama 3.3 70B',
      'Respostas sobre serviços FMLider',
      'Disponível 24/7 no site',
      'Interface amigável',
      'Histórico de conversas'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.5, '📍', 'Rastreamento', [
      'Tracking de contentores em tempo real',
      'Código BL ou número do contentor',
      'Estados: Aguardando → Em Trânsito → Entregue',
      'Notificações automáticas por email',
      'Timeline visual do percurso',
      'Disponível no site público'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.5, '🔍', 'Lookup BI/NIF', [
      'Verificação automática de documentos',
      'Integração com base de dados',
      'Validação em tempo real',
      'Resultado no formulário',
      'Prevenção de fraudes',
      'Experiência fluida para o utilizador'
    ]);

    addCard(slide, 0.5, 4.6, 5.9, 2.0, '🌐', 'Internacionalização', [
      '3 idiomas: Português, Inglês, Francês',
      'Tradução profissional de todo o conteúdo',
      'Alternância instantânea de idioma',
      'SEO multilingual',
      'Armazenamento de preferência do utilizador'
    ]);

    addCard(slide, 6.8, 4.6, 6.1, 2.0, '📱', 'Design Responsivo', [
      'Mobile-first com Bootstrap 5',
      'Testado em todos os tamanhos de ecrã',
      'Modais com scroll corrigido',
      'Dashboard optimizado para mobile',
      'Gestos táteis suportados',
      'Performance optimizada'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 10: DESIGN & UX ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Design & Experiência de Utilizador', 'Interface profissional e intuitiva');

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.5, y: 1.8, w: 5.9, h: 4.8,
      fill: { color: GRAY_LIGHT }, rectRadius: 0.1
    });

    slide.addText('Paleta de Cores', {
      x: 0.8, y: 1.95, w: 5.3, h: 0.4,
      fontSize: 16, color: BLUE_DARK, fontFace: 'Calibri', bold: true
    });

    const colors = [
      { color: BLUE_DARK, name: 'Azul Escuro', hex: '#1a365d' },
      { color: BLUE_MED, name: 'Azul Médio', hex: '#2a4a7f' },
      { color: BLUE_LIGHT, name: 'Azul Claro', hex: '#3b6cb4' },
      { color: GOLD, name: 'Dourado', hex: '#d4af37' },
      { color: GOLD_LIGHT, name: 'Dourado Claro', hex: '#f0d060' },
      { color: WHITE, name: 'Branco', hex: '#ffffff' },
    ];

    colors.forEach((c, i) => {
      const yPos = 2.5 + i * 0.6;
      slide.addShape(pptx.ShapeType.roundRect, {
        x: 0.8, y: yPos, w: 0.5, h: 0.45,
        fill: { color: c.color },
        rectRadius: 0.05,
        line: { color: GRAY_MED, width: 1 }
      });
      slide.addText(`${c.name}  (${c.hex})`, {
        x: 1.5, y: yPos, w: 3, h: 0.45,
        fontSize: 11, color: GRAY_DARK, fontFace: 'Calibri', valign: 'middle'
      });
    });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 6.8, y: 1.8, w: 6.0, h: 4.8,
      fill: { color: GRAY_LIGHT }, rectRadius: 0.1
    });

    slide.addText('Elementos de Design', {
      x: 7.1, y: 1.95, w: 5.4, h: 0.4,
      fontSize: 16, color: BLUE_DARK, fontFace: 'Calibri', bold: true
    });

    const designElements = [
      'Header sticky com navegação fluida',
      'Cards com sombras suaves e bordas douradas',
      'Animações de entrada profissionais',
      'Ícones personalizados por secção',
      'Imagens de fundo configuráveis (admin)',
      'Modais com scroll e responsividade',
      'Dashboard com cards e gráficos',
      'Tabelas com paginação e filtros',
      'Formulários com validação',
      'Loading states e skeleton screens',
      'Notificações toast',
      'Badges de estado coloridos'
    ];

    const bulletText = designElements.map(e => ({
      text: e,
      options: { bullet: { code: '2713' }, color: GRAY_DARK, fontSize: 11, fontFace: 'Calibri', lineSpacingMultiple: 1.3 }
    }));

    slide.addText(bulletText, {
      x: 7.1, y: 2.4, w: 5.4, h: 4.0, valign: 'top', paraSpaceAfter: 4
    });

    addFooter(slide);
  }

  // ========== SLIDE 11: MOBILE ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Otimização Mobile', 'Experiência optimizada para telemóveis');

    addCard(slide, 0.5, 1.8, 3.8, 2.5, '📱', 'Dashboard Mobile', [
      'Layout adaptado a ecrãs pequenos',
      'Cards empilhados verticalmente',
      'Botões de acção acessíveis',
      'Navegação por tabs inferior',
      'KPIs em formato compacto'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.5, '🎯', 'Modais & Formulários', [
      'Modais full-screen no mobile',
      'Scroll corrigido para iOS',
      'Prevenção de zoom automático',
      'Inputs optimizados para teclado',
      'Validação em tempo real'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.5, '⚡', 'Performance', [
      'Carregamento lazy de imagens',
      'Minimização de requests',
      'Cache de dados local',
      'Animações suaves (60fps)',
      'Tamanho optimizado do bundle'
    ]);

    addCard(slide, 0.5, 4.6, 12.33, 1.8, '📊', 'Páginas com Estilos Mobile-Independentes', [
      'Dashboard, Embarques, Contentores, Processos, Cotações, Contactos, Frota, Chat — todas com breakpoints 768px e 480px',
      'Tabelas responsivas com scroll horizontal',
      'Filtros e pesquisa optimizados para touch',
      'Botões de acção com tamanho adequado para dedos'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 12: INVESTIMENTO ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Investimento', 'Valor e condições de pagamento');

    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 1.4, w: 13.33, h: 0.06, fill: { color: GOLD } });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 1.5, y: 2.0, w: 10.33, h: 3.5,
      fill: { color: WHITE },
      rectRadius: 0.15,
      shadow: { type: 'outer', blur: 8, offset: 3, color: '888888', opacity: 0.25 }
    });

    slide.addText('SISTEMA WEB COMPLETO', {
      x: 1.5, y: 2.0, w: 10.33, h: 0.5,
      fontSize: 14, color: GOLD, fontFace: 'Calibri', align: 'center', bold: true, letterSpacing: 6
    });

    slide.addText('500.000 KZ', {
      x: 1.5, y: 2.5, w: 10.33, h: 1.0,
      fontSize: 48, color: BLUE_DARK, fontFace: 'Calibri', bold: true, align: 'center'
    });

    slide.addText('Preço sujeito a negociação após aprovação do projecto', {
      x: 1.5, y: 3.4, w: 10.33, h: 0.4,
      fontSize: 12, color: GOLD, fontFace: 'Calibri', align: 'center', italic: true
    });

    slide.addShape(pptx.ShapeType.rect, { x: 4.0, y: 3.9, w: 5.33, h: 0.02, fill: { color: GRAY_MED } });

    slide.addText('O que está incluído:', {
      x: 1.5, y: 4.05, w: 10.33, h: 0.4,
      fontSize: 13, color: BLUE_DARK, fontFace: 'Calibri', bold: true, align: 'center'
    });

    slide.addText('Site público completo  •  Painel de cliente  •  Painel de admin  •  Chatbot IA  •  Rastreamento  •  API backend  •  Deploy em produção', {
      x: 1.0, y: 4.4, w: 11.33, h: 0.5,
      fontSize: 11, color: GRAY_DARK, fontFace: 'Calibri', align: 'center'
    });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 1.5, y: 5.0, w: 10.33, h: 1.2,
      fill: { color: BLUE_DARK },
      rectRadius: 0.1
    });

    slide.addText([
      { text: 'Demo Disponível:  ', options: { color: GOLD, fontSize: 13, bold: true, fontFace: 'Calibri' } },
      { text: 'projecto-fmlider.vercel.app', options: { color: WHITE, fontSize: 13, fontFace: 'Calibri', bold: true } }
    ], { x: 1.5, y: 5.0, w: 10.33, h: 0.5, valign: 'middle', align: 'center' });

    slide.addText([
      { text: 'Após aprovação, o sistema será configurado em  ', options: { color: WHITE, fontSize: 11, fontFace: 'Calibri' } },
      { text: 'fmlider.co.ao', options: { color: GOLD_LIGHT, fontSize: 11, fontFace: 'Calibri', bold: true } }
    ], { x: 1.5, y: 5.5, w: 10.33, h: 0.4, valign: 'middle', align: 'center' });

    slide.addText('Condições de Pagamento: 50% adiantado + 50% na entrega', {
      x: 1.5, y: 5.9, w: 10.33, h: 0.3,
      fontSize: 10, color: GRAY_MED, fontFace: 'Calibri', align: 'center'
    });

    addFooter(slide);
  }

  // ========== SLIDE 13: CRONOGRAMA ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Cronograma de Implementação', 'Fases do projecto');

    const phases = [
      { week: 'Semana 1-2', title: 'Planeamento & Design', items: ['Análise de requisitos', 'Wireframes e mockups', 'Definição da BD', 'Setup do ambiente'] },
      { week: 'Semana 3-5', title: 'Desenvolvimento Frontend', items: ['Site público', 'Páginas interiores', 'Internacionalização', 'Design responsivo'] },
      { week: 'Semana 6-7', title: 'Backend & Integrações', items: ['API REST', 'Autenticação', 'Supabase setup', 'Chatbot IA'] },
      { week: 'Semana 8', title: 'Testes & Deploy', items: ['Testes completos', 'Correcção de bugs', 'Deploy em produção', 'Formação do cliente'] }
    ];

    phases.forEach((phase, i) => {
      const xPos = 0.5 + i * 3.15;
      slide.addShape(pptx.ShapeType.roundRect, {
        x: xPos, y: 1.8, w: 2.9, h: 4.5,
        fill: { color: WHITE },
        rectRadius: 0.1,
        shadow: { type: 'outer', blur: 4, offset: 2, color: '888888', opacity: 0.15 }
      });

      slide.addShape(pptx.ShapeType.roundRect, {
        x: xPos + 0.15, y: 1.95, w: 2.6, h: 0.5,
        fill: { color: i === 3 ? GOLD : BLUE_DARK },
        rectRadius: 0.05
      });

      slide.addText(phase.week, {
        x: xPos + 0.15, y: 1.95, w: 2.6, h: 0.5,
        fontSize: 11, color: WHITE, fontFace: 'Calibri', bold: true, align: 'center', valign: 'middle'
      });

      slide.addText(phase.title, {
        x: xPos + 0.15, y: 2.6, w: 2.6, h: 0.4,
        fontSize: 13, color: BLUE_DARK, fontFace: 'Calibri', bold: true, align: 'center'
      });

      const bulletText = phase.items.map(item => ({
        text: item,
        options: { bullet: { code: '2022' }, color: GRAY_DARK, fontSize: 10.5, fontFace: 'Calibri', lineSpacingMultiple: 1.4 }
      }));

      slide.addText(bulletText, {
        x: xPos + 0.3, y: 3.15, w: 2.3, h: 2.8, valign: 'top', paraSpaceAfter: 6
      });

      if (i < 3) {
        slide.addText('→', {
          x: xPos + 2.85, y: 3.5, w: 0.4, h: 0.5,
          fontSize: 20, color: GOLD, fontFace: 'Calibri', align: 'center', valign: 'middle'
        });
      }
    });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.5, y: 6.5, w: 12.33, h: 0.4,
      fill: { color: GRAY_LIGHT }, rectRadius: 0.05
    });

    slide.addText('Prazo total estimado: 8 semanas a partir do início do projecto', {
      x: 0.5, y: 6.5, w: 12.33, h: 0.4,
      fontSize: 12, color: BLUE_DARK, fontFace: 'Calibri', align: 'center', valign: 'middle', bold: true
    });

    addFooter(slide);
  }

  // ========== SLIDE 14: PRÓXIMOS PASSOS ==========
  {
    const slide = pptx.addSlide();
    addSectionHeader(slide, 'Próximos Passos', 'Como avançar com o projecto');

    addCard(slide, 0.5, 1.8, 3.8, 2.5, '1️⃣', 'Aprovação da Proposta', [
      'Revisão desta proposta',
      'Aprovação pelo Sr. Filipe',
      'Definição de requisitos finais',
      'Assinatura do contrato'
    ]);

    addCard(slide, 4.8, 1.8, 3.8, 2.5, '2️⃣', 'Pagamento Inicial', [
      '50% adiantado: 250.000 KZ',
      'Transferência bancária',
      'Confirmação do pagamento',
      'Início do desenvolvimento'
    ]);

    addCard(slide, 9.1, 1.8, 3.8, 2.5, '3️⃣', 'Kickoff do Projecto', [
      'Reunião de arranque',
      'Definição de cronograma',
      'Setup do ambiente',
      'Acesso ao repositório'
    ]);

    addCard(slide, 0.5, 4.6, 12.33, 1.8, '📋', 'Requisitos para Início', [
      'Acesso ao domínio fmlider.co.ao para configuração DNS',
      'Conta Supabase activa (já existe)',
      'Conteúdo final do site (textos, imagens)',
      'Definição dos utilizadores admin iniciais',
      'Contactos para integração (email, etc.)'
    ]);

    addFooter(slide);
  }

  // ========== SLIDE 15: CONTACTO ==========
  {
    const slide = pptx.addSlide();
    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 0, w: 13.33, h: 7.5, fill: { color: DARK_BG } });

    slide.addShape(pptx.ShapeType.rect, { x: 0, y: 2.5, w: 13.33, h: 3.0, fill: { color: BLUE_DARK }, transparency: 25 });

    if (fs.existsSync(logoPath)) {
      slide.addImage({ path: logoPath, x: 5.42, y: 0.4, w: 2.5, h: 2.0 });
    }

    slide.addText('Obrigado pela atenção!', {
      x: 0.5, y: 2.6, w: 12.33, h: 0.7,
      fontSize: 32, color: WHITE, fontFace: 'Calibri', bold: true, align: 'center'
    });

    slide.addText('Estamos prontos para transformar a FMLider digitalmente.', {
      x: 0.5, y: 3.3, w: 12.33, h: 0.5,
      fontSize: 16, color: GOLD_LIGHT, fontFace: 'Calibri', align: 'center'
    });

    slide.addShape(pptx.ShapeType.rect, { x: 4.0, y: 3.9, w: 5.33, h: 0.03, fill: { color: GOLD } });

    slide.addText('Masukulu Miguel', {
      x: 0.5, y: 4.2, w: 12.33, h: 0.5,
      fontSize: 18, color: WHITE, fontFace: 'Calibri', bold: true, align: 'center'
    });

    slide.addShape(pptx.ShapeType.roundRect, {
      x: 5.42, y: 4.65, w: 2.5, h: 0.45,
      fill: { color: BLUE_MED },
      rectRadius: 0.06,
      line: { color: GOLD, width: 1 }
    });

    slide.addText('CODINGLIFEDEV', {
      x: 5.42, y: 4.65, w: 2.5, h: 0.45,
      fontSize: 11, color: GOLD, fontFace: 'Calibri', bold: true, align: 'center', valign: 'middle', letterSpacing: 2
    });

    slide.addText([
      { text: '📧 masukulum@gmail.com', options: { color: WHITE, fontSize: 12, fontFace: 'Calibri', breakLine: true } },
      { text: '🌐 fmlider.co.ao', options: { color: WHITE, fontSize: 12, fontFace: 'Calibri', breakLine: true } },
      { text: '💻 github.com/Masukulmiguel/projecto-fmlider', options: { color: WHITE, fontSize: 12, fontFace: 'Calibri' } }
    ], { x: 0.5, y: 5.1, w: 12.33, h: 1.2, align: 'center', lineSpacingMultiple: 1.6 });

    slide.addText('FMLider Transitário & Logística © 2025', {
      x: 0.5, y: 6.5, w: 12.33, h: 0.4,
      fontSize: 10, color: GRAY_DARK, fontFace: 'Calibri', align: 'center'
    });
  }

  const outputPath = path.join(__dirname, 'Proposta_FMLider_Sistema.pptx');
  await pptx.writeFile({ fileName: outputPath });
  console.log(`Apresentação criada: ${outputPath}`);
}

generate().catch(err => { console.error('Erro:', err); process.exit(1); });
