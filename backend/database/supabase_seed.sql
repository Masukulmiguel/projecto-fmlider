-- ============================================================
-- FMLider - Supabase Seed Data
-- Run AFTER supabase_migration.sql
-- ============================================================

-- ============================================================
-- 1. SETTINGS
-- ============================================================
INSERT INTO settings (key, value) VALUES
('company_name', 'FMLider Transitário & Logística'),
('company_phone', '+244 935141747'),
('company_email', 'geral@fmlider.co.ao'),
('company_address', 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco, Luanda'),
('company_description', 'Especialistas em logística, transportes e desembaraço aduaneiro em Angola. Fundada em 2017, com mais de 60 colaboradores e operando em 30+ países.'),
('founded_year', '2017'),
('employees_count', '60'),
('countries_partners', '32'),
('facebook', 'https://facebook.com/fmlider'),
('instagram', 'https://instagram.com/fmlider'),
('linkedin', 'https://linkedin.com/company/fmlider'),
('whatsapp', '244935141747'),
('working_hours', 'Segunda a sexta: 08:00-18:00, Sábado: 08:00-13:00'),
('meta_title', 'FMLider - Logística, Transportes e Transitário em Angola'),
('meta_description', 'FMLider é especialista em logística, transportes, desembaraço aduaneiro e serviços de transitário em Angola. Soluções completas para a sua cadeia de abastecimento.')
ON CONFLICT (key) DO NOTHING;

-- ============================================================
-- 2. SERVICES
-- ============================================================
INSERT INTO services (title, slug, description, content, status, order_by) VALUES
('Desembaraço Aduaneiro', 'desembaraco-aduaneiro',
 'Especialistas em importação, exportação e processos aduaneiros em Angola.',
 'A FMLider dispõe de uma equipa de despachantes aduaneiros certificados, com vasta experiência em processos alfandegários em Angola. Garantimos o cumprimento de todas as formalidades legais e optimizamos os prazos de desembaraço.\n\n• Despachantes certificados\n• Acompanhamento integral do processo\n• Consultoria em regulamentação aduaneira\n• Optimização de custos alfandegários',
 1, 1),
('Transportes', 'transportes',
 'Transporte seguro de cargas em todo território nacional e internacional.',
 'A FMLider oferece soluções de transporte rodoviário, marítimo e aéreo com frota própria e parceiros globais de confiança.\n\n• Transporte rodoviário com frota própria\n• Transporte marítimo (contentores 20" e 40")\n• Transporte aéreo para cargas urgentes\n• Rastreio em tempo real',
 1, 2),
('Armazenagem', 'armazenagem',
 'Espaços preparados para armazenagem de mercadorias e contentores.',
 'Disponibilizamos mais de 3.000m² de armazéns em Viana, equipados com sistemas modernos de gestão e segurança.\n\n• Mais de 3.000m² de armazéns\n• Cross-docking eficiente\n• Sistemas de gestão de inventário\n• Segurança 24/7',
 1, 3),
('Door To Door', 'door-to-door',
 'Soluções completas desde a origem até ao destino, sem preocupações.',
 'Serviço completo de porta a porta, desde a recolha na origem internacional até a entrega final em Angola.\n\n• Recolha na origem internacional\n• Gestão documental completa\n• Desembaraço aduaneiro\n• Entrega no destino final',
 1, 4),
('Mudanças e Remoções', 'mudancas-remocoes',
 'Serviço profissional de mudança residencial e corporativa com seguro.',
 'Realizamos mudanças residenciais e corporativas com toda a segurança e profissionalismo.\n\n• Mudanças residenciais\n• Mudanças corporativas\n• Embalagem profissional\n• Seguro de transporte',
 1, 5),
('Carga Consolada', 'carga-consolada',
 'Consolidação de cargas para optimizar custos de frete.',
 'O serviço de groupage permite consolidar mercadorias de diferentes clientes num contentor, reduzindo significativamente os custos de transporte.\n\n• Consolidação de cargas\n• Optimização de custos\n• Frequências regulares\n• Rastreio completo',
 1, 6),
('Seguro de Carga', 'seguro-carga',
 'Cobertura All Risks para protecção total das suas mercadorias.',
 'Oferecemos seguros de carga com cobertura All Risks para proteger as suas mercadorias durante todo o transporte.\n\n• Cobertura All Risks\n• Valores declarados\n• Processamento rápido de sinistros\n• Parceiros seguradores internacionais',
 1, 7),
('Consultoria Aduaneira', 'consultoria-aduaneira',
 'Assessoria especializada em compliance e regulamentação aduaneira.',
 'A nossa equipa de consultores ajuda-o a navegar a complexa regulamentação aduaneira angolana.\n\n• Análise de compliance\n• Regulamentação aduaneira\n• Optimização fiscal\n• Formação equipas',
 1, 8)
ON CONFLICT (slug) DO NOTHING;

-- ============================================================
-- 3. PARTNERS
-- ============================================================
INSERT INTO partners (name, logo, website, status, order_by) VALUES
('DHL', '/assets/partners/dhl.png', 'https://www.dhl.com', 1, 1),
('Maersk', '/assets/partners/maersk.png', 'https://www.maersk.com', 1, 2),
('MSC', '/assets/partners/msc.png', 'https://www.msc.com', 1, 3),
('CMA CGM', '/assets/partners/cmacgm.png', 'https://www.cma-cgm.com', 1, 4),
('AGT', '/assets/partners/agt.png', 'https://www.agt.co.ao', 1, 5),
('TAAG', '/assets/partners/taag.png', 'https://www.taag.com', 1, 6),
('Porto de Luanda', '/assets/partners/portoluanda.png', 'https://www.portoluanda.ao', 1, 7),
('Porto de Sines', '/assets/partners/portosines.png', 'https://www.portodesines.pt', 1, 8)
ON CONFLICT DO NOTHING;

-- ============================================================
-- 4. BANNERS
-- ============================================================
INSERT INTO banners (title, description, image, link, status, order_by) VALUES
('Soluções Logísticas Integradas', 'Desde o desembaraço aduaneiro até à entrega final. A FMLider é a sua parceira de confiança em Angola.', '/assets/banners/banner1.jpg', '/servicos', 1, 1),
('Transporte Marítimo e Aéreo', 'Contentores 20" e 40" com parceiros globais como DHL, Maersk e MSC. Envios urgentes via companhias aéreas.', '/assets/banners/banner2.jpg', '/servicos', 1, 2),
('Porta a Porta Internacional', 'Serviço completo de door-to-door desde qualquer ponto do mundo até Angola. Sem preocupações.', '/assets/banners/banner3.jpg', '/servicos', 1, 3)
ON CONFLICT DO NOTHING;

-- ============================================================
-- 5. TESTIMONIALS
-- ============================================================
INSERT INTO testimonials (name, position, company, message, rating, status, order_by) VALUES
('Carlos Silva', 'Director de Logística', 'Angola Mining Corp',
 'A FMLider transformou a nossa cadeia de abastecimento. O desembaraço aduaneiro que antes demorava semanas agora é feito em dias. Profissionalismo exemplar.',
 5, 1, 1),
('Maria Santos', 'Gerente de Importações', 'TechAngola Lda',
 'Excelente serviço de transportes marítimos. O rastreio em tempo real e a comunicação constante dão-nos total confiança nas nossas remessas.',
 5, 1, 2),
('Pedro Neto', 'CEO', 'Distribuidora Geral',
 'A equipa da FMLider é extremamente competente. O serviço Door to Door Simplificou imenso as nossas importações da China.',
 5, 1, 3),
('Ana Ferreira', 'Directora de Operações', 'Construções Modernas',
 'Trabalhamos com a FMLider há 3 anos e nunca nos desapontaram. A armazenagem em Viana é segura e bem gerida.',
 4, 1, 4)
ON CONFLICT DO NOTHING;

-- ============================================================
-- 6. FAQs
-- ============================================================
INSERT INTO faqs (question, answer, category, status, order_by) VALUES
('Que serviços de desembaraço aduaneiro oferecem?',
 'Oferecemos desembaraço aduaneiro completo para importação e exportação, incluindo preparação documental, classificação aduaneira, cálculo de impostos e acompanhamento até à libertação da mercadoria.',
 'Serviços', 1, 1),
('Quanto tempo demora o desembaraço aduaneiro?',
 'O tempo médio de desembaraço é de 2 a 5 dias úteis, dependendo do tipo de mercadoria e da kompleicidade documental. Trabalhamos para optimizar sempre os prazos.',
 'Serviços', 1, 2),
('Quais são as vossas zonas de cobertura?',
 'Operamos em todo o território angolano, com presença forte em Luanda, Benguela, Lubango e Huambo. Internacionalmente, trabalhamos com parceiros em mais de 30 países.',
 'Cobertura', 1, 3),
('Como posso rastrear a minha carga?',
 'Disponibilizamos um sistema de rastreio em tempo real. Pode aceder ao tracking através do nosso site ou solicitar actualizações periódicas por email ou WhatsApp.',
 'Rastreio', 1, 4),
('Que tipos de contentores disponíveis?',
 'Trabalhamos com contentores de 20" e 40" (secos e refrigerados), conforme as necessidades da sua carga. Também oferecemos contentores flat rack e open top para cargas especiais.',
 'Transporte', 1, 5),
('Qual é o processo para solicitar uma cotação?',
 'Pode solicitar uma cotação através do nosso formulário online, por email ou telefone. Precisamos de informações sobre origem, destino, tipo de carga e peso para fornecer uma estimativa precisa.',
 'Cotações', 1, 6),
('Oferecem seguro de carga?',
 'Sim, oferecemos seguro de carga com cobertura All Risks para proteger as suas mercadorias durante todo o transporte. Os valores dependem do tipo e valor da mercadoria.',
 'Seguros', 1, 7),
('Como funciona o serviço Door to Door?',
 'O serviço Door to Door inclui recolha na origem internacional, transporte até Angola, desembaraço aduaneiro e entrega no destino final. Cuidamos de todo o processo para si.',
 'Serviços', 1, 8)
ON CONFLICT DO NOTHING;

-- ============================================================
-- 7. NEWS (sample articles)
-- ============================================================
INSERT INTO news (title, slug, description, content, category, status, published_at) VALUES
('FMLider expande operações para o Lobito', 'fmlider-expande-operacoes-lobito',
 'A FMLider anuncia a abertura de novas instalações no Porto do Lobito, reforçando a presença no sul de Angola.',
 'A FMLider Transitário & Logística announce a expansão das suas operações para o Porto do Lobito, com novas instalações que incluem armazém de 1.500m² e escritório de desembaraço aduaneiro.\n\nEsta expansão permite à FMLider servir melhor os clientes na província da Benguela e regiões circundantes, reforçando o compromisso com a crescimento sustentável em Angola.\n\n"As nossas novas instalações no Lobito representam um passo importante na nossa estratégia de expansão", disse o director geral da FMLider. "Estamos comprometidos em estar mais perto dos nossos clientes e em oferecer serviços de qualidade em todo o território."',
 'Empresa', 'published', '2026-06-01 10:00:00+00'),
('Novo acordo com a Maersk para rotas marítimas', 'novo-acordo-maersk-rotas-maritimas',
 'A FMLider firma parceria estratégica com a Maersk para novas rotas marítimas entre Angola e Europa.',
 'A FMLider announce um novo acordo de parceria com a Maersk, uma das maiores companhias marítimas do mundo, para operar novas rotas directas entre Angola e portos europeus.\n\nEste acordo permite oferecer prazos de trânsito mais curtos e tarifas competitivas para os clientes da FMLider.\n\n"As novas rotas com a Maersk permitem-nos oferecer um serviço mais rápido e eficiente", explicou o responsável de operações marítimas da FMLider.',
 'Parcerias', 'published', '2026-05-15 10:00:00+00'),
('FMLider recebe certificação ISO 9001:2015', 'fmlider-recebe-certificacao-iso',
 'A FMLider obteve a certificação ISO 9001:2015, confirmando o compromisso com a qualidade nos serviços de logística.',
 'A FMLider Transitário & Logística announce a obtenção da certificação ISO 9001:2015, um marco importante que reconhece a qualidade dos seus processos e serviços.\n\nEsta certificação é o resultado de um trabalho contínuo de melhoria e demonstra o compromisso da empresa com a excelência operacional.\n\n"A certificação ISO 9001:2015 é um reconhecimento do trabalho da nossa equipa e do nosso compromisso com a satisfação dos clientes", afirmou a dirección da FMLider.',
 'Empresa', 'published', '2026-04-20 10:00:00+00')
ON CONFLICT (slug) DO NOTHING;

-- ============================================================
-- ADMIN USER NOTE
-- ============================================================
-- To create the admin user, go to Supabase Dashboard > Authentication > Users
-- and create a new user with:
--   Email: admin@fmlider.co.ao
--   Password: (your chosen password)
--
-- Then insert the profile in the users table:
-- INSERT INTO users (id, username, name, email, phone, role, status, approval_status)
-- VALUES ('<auth-user-uuid>', 'admin', 'Administrador', 'admin@fmlider.co.ao', '+244 935141747', 'admin', 1, 'approved');
--
-- Or run this after creating the auth user (replace YOUR_UUID with the actual UUID from auth.users):
-- INSERT INTO users (username, name, email, phone, role, status, approval_status)
-- VALUES ('admin', 'Administrador', 'admin@fmlider.co.ao', '+244 935141747', 'admin', 1, 'approved');

-- ============================================================
-- END OF SEED
-- ============================================================
