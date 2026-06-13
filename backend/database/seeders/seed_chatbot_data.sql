-- Seed data for chatbot context (services, FAQs, partners, testimonials)
-- Run after schema.sql

-- ============= SERVICES =============
INSERT INTO services (title, slug, description, content, status, order_by) VALUES
('Transporte Rodoviário', 'transporte-rodoviario',
 'Transporte de mercadorias por camião em todo o território nacional e países vizinhos.',
 'Frota moderna de camiões e contentores para transporte de carga geral, refrigerada, contentores 20 e 40 pés. Cobertura Angola, RDC, Zâmbia, Namíbia, África do Sul. Rastreamento GPS em tempo real.', 1, 1),

('Transporte Marítimo', 'transporte-maritimo',
 'Envio de carga por via marítima com agentes nos principais portos do mundo.',
 'Serviços de freight forwarding marítimo FCL e LCL, com agentes nos portos de Luanda, Sines, Roterdão, Xangai, Durban. Desalfandegamento integrado.', 1, 2),

('Transporte Aéreo', 'transporte-aereo',
 'Carga aérea urgente e de alto valor, com as principais companhias do mundo.',
 'Envio de carga aérea consolidada e dedicada, com serviço expresso 24-48h para destinos em todo o mundo. Ideal para amostras, peças urgentes, perecíveis.', 1, 3),

('Despachante / Transitário', 'despachante',
 'Desalfandegamento de mercadorias na importação e exportação, em Angola e no exterior.',
 'Equipa de despachantes oficiais credenciados pela AGT. Tratamento de DU (Documento Único), licenças de importação, certificados de origem, inspeções.', 1, 4),

('Armazenagem e Logística', 'armazenagem-logistica',
 'Armazéns em Luanda e nas províncias para guarda e distribuição de mercadorias.',
 'Armazéns com 5.000m² em Luanda, Viana, Benguela. Serviços de picking, packing, etiquetagem, cross-docking, gestão de stock. CCTV 24h.', 1, 5),

('Mudanças e Remoções', 'mudancas',
 'Mudanças residenciais e empresariais com embalamento profissional.',
 'Serviço porta-a-porta com embalamento, desmontagem e montagem. Seguro de carga incluído. Cobertura nacional e internacional.', 1, 6),

('Carga Consolada (Groupage)', 'carga-consolidada',
 'Envie pequenas quantidades pagando apenas pelo espaço ocupado.',
 'Saídas semanais para Portugal, Brasil, China, Dubai, África do Sul. Pague apenas o kg/m3 ocupado. Ideal para pequenas encomendas.', 1, 7),

('Seguro de Carga', 'seguro-carga',
 'Cobertura completa da sua mercadoria durante o transporte.',
 'Apólices All Risks para transporte marítimo, aéreo e rodoviário. Parceria com seguradoras internacionais (Lloyd''s, Allianz).', 1, 8);


-- ============= FAQs =============
INSERT INTO faqs (question, answer, category, order_by, status) VALUES
('Como posso solicitar um orçamento?',
 'Pode solicitar um orçamento de 3 formas: 1) Preenchendo o formulário de contacto no site, 2) Enviando email para geral@fmlider.co.ao com os detalhes da carga, 3) Ligando para +244 935 141 747. Responderemos em até 24 horas úteis.',
 'Cotações', 1, 1),

('Quais destinos servem?',
 'Trabalhamos com praticamente todos os países do mundo. Temos rotas regulares para Portugal, Brasil, China, Dubai, EUA, países da Europa e toda a SADC (Angola, RDC, Zâmbia, Namíbia, Botswana, África do Sul, Moçambique).',
 'Serviços', 2, 1),

('Quanto tempo demora um envio para Portugal?',
 'Envios marítimos: 18-25 dias. Envios aéreos: 3-5 dias. Carga consolidada (groupage): 25-35 dias. Os prazos podem variar com a época do ano e desalfandegamento.',
 'Prazos', 3, 1),

('Quanto tempo demora um envio para a China?',
 'Envios marítimos da China para Angola: 35-45 dias. Envios aéreos: 2-4 dias. Para cargas urgentes recomendamos sempre o transporte aéreo.',
 'Prazos', 4, 1),

('Como faço o rastreamento da minha carga?',
 'Todos os embarques têm um número de tracking (ex: EMB-20260101-0001). Pode usar esse número no nosso site na secção "Rastrear Envio" ou solicitar atualização por email/telefone.',
 'Rastreamento', 5, 1),

('Quais documentos preciso para importar para Angola?',
 'Documentos habituais: Fatura comercial, packing list, conhecimento de embarque (B/L ou AWB), certificado de origem, DU (Documento Único) emitido pela AGT, licença de importação (quando aplicável). A nossa equipa trata de tudo.',
 'Documentação', 6, 1),

('Fazem entregas em todas as províncias de Angola?',
 'Sim, temos rede de distribuição em todas as 18 províncias. Para algumas regiões remotas os prazos podem ser superiores e há custos adicionais de transporte da última milha.',
 'Serviços', 7, 1),

('Como posso pagar?',
 'Aceitamos transferência bancária (AOA, USD, EUR), Multicaixa Express, TPA e numerário (até limites legais). Para clientes empresariais com contrato oferecemos condições de pagamento a 15/30 dias.',
 'Pagamento', 8, 1),

('Têm seguro de carga?',
 'Sim, oferecemos seguro All Risks em parceria com seguradoras internacionais. Recomendamos vivamente segurar a carga, especialmente em envios de alto valor. Custo típico: 0.3% a 0.8% do valor da mercadoria.',
 'Seguro', 9, 1),

('Qual é o horário de funcionamento?',
 'Segunda a sexta das 08:00 às 18:00. Sábado das 08:00 às 13:00. Para emergências fora de horas, ligue +244 935 141 747.',
 'Geral', 10, 1),

('Onde fica a vossa sede?',
 'A nossa sede fica em Luanda, na Rua Comandante Valódia, nº 123, bairro Maianga. Temos também filiais em Benguela, Lubango e Namibe.',
 'Geral', 11, 1),

('Como criar uma conta de cliente?',
 'Clique em "Registar" no topo do site, preencha o formulário de registo. Após aprovação pela nossa equipa (até 24h), poderá aceder à área de cliente para gerir embarques, documentos e cotações.',
 'Conta', 12, 1);


-- ============= PARTNERS =============
INSERT INTO partners (name, description, website, order_by, status) VALUES
('AGT - Administração Geral Tributária', 'Autoridade tributária angolana responsável pelo desalfandegamento.', 'https://www.agt.minfin.gov.ao', 1, 1),
('TAAG Linhas Aéreas de Angola', 'Companhia aérea de bandeira, parceira para carga aérea.', 'https://www.taag.co.ao', 2, 1),
('Porto de Luanda', 'Principal porto de Angola, parceiro para operações marítimas.', 'https://www.portoluanda.co.ao', 3, 1),
('Sines Portugal', 'Porto de Sines, parceiro para cargas com destino à Europa.', 'https://www.apsines.pt', 4, 1),
('DHL Global', 'Parceiro internacional para envios expressos.', 'https://www.dhl.com', 5, 1);


-- ============= TESTIMONIALS =============
INSERT INTO testimonials (name, position, company, message, rating, status, order_by) VALUES
('João Pedro', 'Director de Operações', 'Construções Atlântico',
 'A FMLider é o nosso parceiro logístico há mais de 5 anos. Profissionalismo, pontualidade e preços justos. Recomendamos vivamente.', 5, 1, 1),

('Maria Santos', 'CEO', 'Santos Importações Lda',
 'Excelente serviço de desalfandegamento. Resolveram um problema de licenciamento em tempo recorde. Equipa muito competente.', 5, 1, 2),

('Carlos Eduardo', 'Gestor de Compras', 'TechAngola',
 'Usamos o serviço de carga consolidada da China há 2 anos. Sempre a cumprir prazos. Preço competitivo.', 4, 1, 3),


-- ============= NEWS =============
INSERT INTO news (title, slug, description, content, category, status, user_id, published_at) VALUES
('FMLider expande operações para a SADC',
 'fmlider-expande-operacoes-sadc',
 'Nova rota semanal de camião para Lusaca, Zâbia, com tempos de trânsito de 5 dias.',
 'A FMLider anunciou a abertura de nova rota rodoviária Luanda-Lusaca com saída semanal. O serviço inclui desalfandegamento integrado e seguro all risks. Tempos de trânsito garantidos em 5 dias úteis.',
 'Expansão', 'published', 1, NOW()),

('Parceria com Porto de Sines',
 'parceria-porto-sines',
 'Acordo garante prioridade no descarregamento de contentores com destino a Angola.',
 'FMLider assinou protocolo com a Administração do Porto de Sines que garante prioridade no descarregamento e taxas preferenciais para contentores com destino a Angola. Estimativa de poupança de 3-5 dias no trânsito.',
 'Parcerias', 'published', 1, NOW()),

('Novo armazém em Viana',
 'novo-armazem-viana',
 'Mais 3.000m² de área coberta para armazenagem e cross-docking.',
 'Inaugurámos em Janeiro um novo armazém em Viana com 3.000m², elevando a nossa capacidade total para 8.000m² em Luanda. O espaço inclui área refrigerada, zona de cross-docking e sistema CCTV 24h.',
 'Empresa', 'published', 1, NOW());
