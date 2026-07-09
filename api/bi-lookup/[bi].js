const AGT_PORTAL = 'https://portaldocontribuinte.minfin.gov.ao';
const AGT_LOOKUP = '/consultar-nif-do-contribuinte';
const AGT_AJAX = '/consultar-headNifId-do-contribuinte';

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'GET') return res.status(405).json({ success: false, message: 'Method not allowed' });

  const { bi } = req.query;
  if (!bi || !/^\d{9}[A-Za-z]{2}\d{3}$/.test(bi)) {
    return res.status(422).json({ success: false, message: 'BI inválido. Deve conter 14 caracteres (ex: 006151112LA041).' });
  }

  const biUpper = bi.toUpperCase();

  const strategies = [
    () => tryEdgarApi(biUpper),
    () => tryBuscadorApi(biUpper),
    () => tryAngolaApi(biUpper),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result) {
        return res.status(200).json({ success: true, message: 'OK', data: result });
      }
    } catch (_) {}
  }

  return res.status(404).json({ success: false, message: 'BI não encontrado. Verifique o número e tente novamente.' });
}

async function tryEdgarApi(bi) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 8000);
  try {
    const r = await fetch(`http://consulta.edgarsingui.ao/consultar/${encodeURIComponent(bi)}`, {
      signal: controller.signal,
      headers: { 'User-Agent': 'FMLider-App/1.0' },
    });
    clearTimeout(timer);
    if (!r.ok) return null;
    const d = await r.json();
    if (d.error || !d.name) return null;
    return { nome: d.name, bi, fonte: 'Edgar Singui API' };
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}

async function tryBuscadorApi(bi) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 8000);
  try {
    const r = await fetch(`https://buscador.ao/search/document?type=BI&number=${encodeURIComponent(bi)}`, {
      signal: controller.signal,
      headers: { 'User-Agent': 'FMLider-App/1.0' },
    });
    clearTimeout(timer);
    if (!r.ok) return null;
    const d = await r.json();
    if (!d.data || !d.data.name) return null;
    return { nome: d.data.name, bi: d.data.bi || bi, fonte: 'Buscador.ao' };
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}

async function tryAngolaApi(bi) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 8000);
  try {
    const r = await fetch(`https://angolaapi.onrender.com/api/v1/validate/bi/${encodeURIComponent(bi)}`, {
      signal: controller.signal,
      headers: { 'User-Agent': 'FMLider-App/1.0' },
    });
    clearTimeout(timer);
    if (!r.ok) return null;
    const d = await r.json();
    if (!d.sucess && !d.success) return null;
    return { nome: null, bi, fonte: 'Angola API', validFormat: true };
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}
