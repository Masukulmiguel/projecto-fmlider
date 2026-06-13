-- Fix UTF-8 corruption in existing data
SET NAMES utf8mb4;

-- Services
UPDATE services SET
  title = 'Transporte Rodoviário',
  description = 'Transporte de mercadorias por camião em todo o território nacional e países vizinhos.',
  content = 'Frota moderna de camiões e contentores para transporte de carga geral, refrigerada, contentores 20 e 40 pés. Cobertura Angola, RDC, Zâmbia, Namíbia, África do Sul. Rastreamento GPS em tempo real.'
WHERE slug = 'transporte-rodoviario';

UPDATE services SET
  title = 'Transporte Marítimo',
  description = 'Envio de carga por via marítima com agentes nos principais portos do mundo.',
  content = 'Serviços de freight forwarding marítimo FCL e LCL, com agentes nos portos de Luanda, Sines, Roterdão, Xangai, Durban. Desalfandegamento integrado.'
WHERE slug = 'transporte-maritimo';

UPDATE services SET
  title = 'Transporte Aéreo',
  description = 'Carga aérea urgente e de alto valor, com as principais companhias do mundo.',
  content = 'Envio de carga aérea consolidada e dedicada, com serviço expresso 24-48h para destinos em todo o mundo. Ideal para amostras, peças urgentes, perecíveis.'
WHERE slug = 'transporte-aereo';

UPDATE services SET
  title = 'Despachante / Transitário',
  description = 'Desalfandegamento de mercadorias na importação e exportação, em Angola e no exterior.',
  content = 'Equipa de despachantes oficiais credenciados pela AGT. Tratamento de DU (Documento Único), licenças de importação, certificados de origem, inspeções.'
WHERE slug = 'despachante';

UPDATE services SET
  title = 'Armazenagem e Logística',
  description = 'Armazéns em Luanda e nas províncias para guarda e distribuição de mercadorias.',
  content = 'Armazéns com 5.000m² em Luanda, Viana, Benguela. Serviços de picking, packing, etiquetagem, cross-docking, gestão de stock. CCTV 24h.'
WHERE slug = 'armazenagem-logistica';

UPDATE services SET
  title = 'Mudanças e Remoções',
  description = 'Mudanças residenciais e empresariais com embalamento profissional.',
  content = 'Serviço porta-a-porta com embalamento, desmontagem e montagem. Seguro de carga incluído. Cobertura nacional e internacional.'
WHERE slug = 'mudancas';

UPDATE services SET
  title = 'Carga Consolada (Groupage)',
  description = 'Envie pequenas quantidades pagando apenas pelo espaço ocupado.',
  content = 'Saídas semanais para Portugal, Brasil, China, Dubai, África do Sul. Pague apenas o kg/m3 ocupado. Ideal para pequenas encomendas.'
WHERE slug = 'carga-consolidada';

UPDATE services SET
  title = 'Seguro de Carga',
  description = 'Cobertura completa da sua mercadoria durante o transporte.',
  content = 'Apólices All Risks para transporte marítimo, aéreo e rodoviário. Parceria com seguradoras internacionais.'
WHERE slug = 'seguro-carga';

-- FAQs
UPDATE faqs SET
  question = 'Como posso solicitar um orçamento?',
  answer = 'Pode solicitar um orçamento de 3 formas: 1) Preenchendo o formulário de contacto no site, 2) Enviando email para geral@fmlider.co.ao com os detalhes da carga, 3) Ligando para +244 935 141 747. Responderemos em até 24 horas úteis.'
WHERE order_by = 1;

UPDATE faqs SET
  question = 'Quais destinos servem?',
  answer = 'Trabalhamos com praticamente todos os países do mundo. Temos rotas regulares para Portugal, Brasil, China, Dubai, EUA, países da Europa e toda a SADC (Angola, RDC, Zâmbia, Namíbia, Botswana, África do Sul, Moçambique).'
WHERE order_by = 2;

UPDATE faqs SET
  question = 'Quanto tempo demora um envio para Portugal?',
  answer = 'Envios marítimos: 18-25 dias. Envios aéreos: 3-5 dias. Carga consolidada (groupage): 25-35 dias. Os prazos podem variar com a época do ano e desalfandegamento.'
WHERE order_by = 3;

UPDATE faqs SET
  question = 'Quanto tempo demora um envio para a China?',
  answer = 'Envios marítimos da China para Angola: 35-45 dias. Envios aéreos: 2-4 dias. Para cargas urgentes recomendamos sempre o transporte aéreo.'
WHERE order_by = 4;

UPDATE faqs SET
  question = 'Como faço o rastreamento da minha carga?',
  answer = 'Todos os embarques têm um número de tracking (ex: EMB-20260101-0001). Pode usar esse número no nosso site na secção "Rastrear Envio" ou solicitar atualização por email/telefone.'
WHERE order_by = 5;

UPDATE faqs SET
  question = 'Quais documentos preciso para importar para Angola?',
  answer = 'Documentos habituais: Fatura comercial, packing list, conhecimento de embarque (B/L ou AWB), certificado de origem, DU (Documento Único) emitido pela AGT, licença de importação (quando aplicável). A nossa equipa trata de tudo.'
WHERE order_by = 6;

UPDATE faqs SET
  question = 'Fazem entregas em todas as províncias de Angola?',
  answer = 'Sim, temos rede de distribuição em todas as 18 províncias. Para algumas regiões remotas os prazos podem ser superiores e há custos adicionais de transporte da última milha.'
WHERE order_by = 7;

UPDATE faqs SET
  question = 'Como posso pagar?',
  answer = 'Aceitamos transferência bancária (AOA, USD, EUR), Multicaixa Express, TPA e numerário (até limites legais). Para clientes empresariais com contrato oferecemos condições de pagamento a 15/30 dias.'
WHERE order_by = 8;

UPDATE faqs SET
  question = 'Têm seguro de carga?',
  answer = 'Sim, oferecemos seguro All Risks em parceria com seguradoras internacionais. Recomendamos vivamente segurar a carga, especialmente em envios de alto valor. Custo típico: 0.3% a 0.8% do valor da mercadoria.'
WHERE order_by = 9;

UPDATE faqs SET
  question = 'Qual é o horário de funcionamento?',
  answer = 'Segunda a sexta das 08:00 às 18:00. Sábado das 08:00 às 13:00. Para emergências fora de horas, ligue +244 935 141 747.'
WHERE order_by = 10;

UPDATE faqs SET
  question = 'Onde fica a vossa sede?',
  answer = 'A nossa sede fica em Luanda, na Rua Comandante Valódia, nº 123, bairro Maianga. Temos também filiais em Benguela, Lubango e Namibe.'
WHERE order_by = 11;

UPDATE faqs SET
  question = 'Como criar uma conta de cliente?',
  answer = 'Clique em "Registar" no topo do site, preencha o formulário de registo. Após aprovação pela nossa equipa (até 24h), poderá aceder à área de cliente para gerir embarques, documentos e cotações.'
WHERE order_by = 12;

-- Partners
UPDATE partners SET
  description = 'Autoridade tributária angolana responsável pelo desalfandegamento.'
WHERE name LIKE 'AGT%';

UPDATE partners SET
  description = 'Companhia aérea de bandeira, parceira para carga aérea.'
WHERE name LIKE 'TAAG%';

UPDATE partners SET
  description = 'Principal porto de Angola, parceiro para operações marítimas.'
WHERE name LIKE 'Porto de Luanda%';

UPDATE partners SET
  description = 'Porto de Sines, parceiro para cargas com destino à Europa.'
WHERE name LIKE 'Sines%';

UPDATE partners SET
  description = 'Parceiro internacional para envios expressos.'
WHERE name LIKE 'DHL%';

-- Testimonials
UPDATE testimonials SET
  position = 'Director de Operações',
  company = 'Construções Atlântico',
  message = 'A FMLider é o nosso parceiro logístico há mais de 5 anos. Profissionalismo, pontualidade e preços justos. Recomendamos vivamente.'
WHERE name = 'João Pedro';

UPDATE testimonials SET
  message = 'Excelente serviço de desalfandegamento. Resolveram um problema de licenciamento em tempo recorde. Equipa muito competente.'
WHERE name = 'Maria Santos';

UPDATE testimonials SET
  message = 'Usamos o serviço de carga consolidada da China há 2 anos. Sempre a cumprir prazos. Preço competitivo.'
WHERE name = 'Carlos Eduardo';

-- News
UPDATE news SET
  description = 'Nova rota semanal de camião para Lusaca, Zâmbia, com tempos de trânsito de 5 dias.',
  content = 'A FMLider anunciou a abertura de nova rota rodoviária Luanda-Lusaca com saída semanal. O serviço inclui desalfandegamento integrado e seguro all risks.'
WHERE slug = 'fmlider-expande-operacoes-sadc';

UPDATE news SET
  description = 'Acordo garante prioridade no descarregamento de contentores com destino a Angola.',
  content = 'FMLider assinou protocolo com a Administração do Porto de Sines que garante prioridade no descarregamento e taxas preferenciais para contentores com destino a Angola.'
WHERE slug = 'parceria-porto-sines';

UPDATE news SET
  description = 'Mais 3.000m² de área coberta para armazenagem e cross-docking.',
  content = 'Inaugurámos em Janeiro um novo armazém em Viana com 3.000m², elevando a nossa capacidade total para 8.000m² em Luanda. O espaço inclui área refrigerada, zona de cross-docking e sistema CCTV 24h.'
WHERE slug = 'novo-armazem-viana';
