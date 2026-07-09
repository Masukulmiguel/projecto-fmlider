import https from 'https';

const PORTAL_URL = 'https://portaldocontribuinte.minfin.gov.ao';
const LOOKUP_PATH = '/consultar-nif-do-contribuinte';
const AJAX_PATH = '/consultar-headNifId-do-contribuinte';

function httpsFetch(urlStr, options = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL(urlStr);
    const reqOptions = {
      hostname: url.hostname,
      port: 443,
      path: url.pathname + url.search,
      method: options.method || 'GET',
      headers: options.headers || {},
      rejectUnauthorized: false,
    };
    const req = https.request(reqOptions, (res) => {
      let body = '';
      res.on('data', (chunk) => { body += chunk; });
      res.on('end', () => {
        resolve({
          ok: res.statusCode >= 200 && res.statusCode < 300,
          status: res.statusCode,
          url: urlStr,
          text: () => Promise.resolve(body),
          headers: res.headers,
        });
      });
    });
    req.on('error', reject);
    if (options.body) req.write(options.body);
    req.end();
  });
}

function parseCookies(headers) {
  const raw = headers['set-cookie'];
  if (!raw) return {};
  const cookies = {};
  const arr = Array.isArray(raw) ? raw : [raw];
  for (const c of arr) {
    const [kv] = c.split(';');
    const [k, v] = kv.split('=');
    if (k && v) cookies[k.trim()] = v.trim();
  }
  return cookies;
}

function cookieHeader(cookies) {
  return Object.entries(cookies).map(([k, v]) => `${k}=${v}`).join('; ');
}

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'GET') return res.status(405).json({ success: false, message: 'Method not allowed' });

  const nif = req.query.nif;
  if (!nif || !/^\d{10}$/.test(nif)) {
    return res.status(422).json({ success: false, message: 'NIF inválido. Deve conter exactamente 10 dígitos.' });
  }

  try {
    const cookies = {};
    const viewState = await getViewState(cookies);
    if (!viewState) {
      return res.status(502).json({ success: false, message: 'Não foi possível aceder ao portal da AGT.' });
    }

    const result = await postNifLookup(nif, viewState, cookies);
    if (!result) {
      return res.status(404).json({ success: false, message: 'NIF não encontrado no portal da AGT.' });
    }

    return res.status(200).json({ success: true, message: 'OK', data: result });
  } catch (err) {
    console.error('NIF lookup error:', err.message);
    return res.status(500).json({ success: false, message: 'Erro ao consultar NIF. Tente novamente.' });
  }
}

async function getViewState(cookies) {
  const res = await httpsFetch(PORTAL_URL + LOOKUP_PATH, {
    headers: {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language': 'pt-BR,pt;q=0.9',
    },
  });

  if (!res.ok) return null;
  Object.assign(cookies, parseCookies(res.headers));
  const html = await res.text();

  const m = html.match(/name="javax\.faces\.ViewState"[^>]*value="([^"]+)"/);
  if (m) return m[1];

  const m2 = html.match(/name='javax\.faces\.ViewState'[^>]*value='([^']+)'/);
  if (m2) return m2[1];

  return null;
}

async function postNifLookup(nif, viewState, cookies) {
  const params = new URLSearchParams({
    'javax.faces.partial.ajax': 'true',
    'javax.faces.source': 'j_id_2x:j_id_34',
    'javax.faces.partial.execute': '@all',
    'javax.faces.partial.render': 'showpanelNIF',
    'j_id_2x_SUBMIT': '1',
    'j_id_2x:txtNIFNumber': nif,
    'j_id_2x:j_id_34': 'j_id_2x:j_id_34',
    'javax.faces.ViewState': viewState,
  });

  const headers = {
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    'Faces-Request': 'partial/ajax',
    'X-Requested-With': 'XMLHttpRequest',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept': '*/*',
    'Origin': PORTAL_URL,
    'Referer': PORTAL_URL + LOOKUP_PATH,
  };
  const cookieStr = cookieHeader(cookies);
  if (cookieStr) headers['Cookie'] = cookieStr;

  const res = await httpsFetch(PORTAL_URL + AJAX_PATH, {
    method: 'POST',
    headers,
    body: params.toString(),
  });

  if (!res.ok) return null;
  const xml = await res.text();
  return parseResponse(xml);
}

function parseResponse(xmlResponse) {
  const startMarker = '<update id="showpanelNIF"><![CDATA[';
  const startIdx = xmlResponse.indexOf(startMarker);
  if (startIdx === -1) return null;

  const htmlStart = startIdx + startMarker.length;
  const endMarker = ']]></update>';
  const endIdx = xmlResponse.indexOf(endMarker, htmlStart);
  if (endIdx === -1) return null;

  const html = xmlResponse.substring(htmlStart, endIdx);

  if (!html.includes('panel-default-header')) return null;

  const extractField = (label) => {
    const escaped = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const re = new RegExp(escaped + '\\s*<\\/label>\\s*<div[^>]*>\\s*<label[^>]*>([^<]+)<\\/label>', 's');
    const m = html.match(re);
    return m ? m[1].trim() : null;
  };

  const nome = extractField('Nome:');
  if (!nome) return null;

  let residencia = null;
  const resM = html.match(/Residente Fiscal<\/label>.*?<label[^>]*>([^<]+)<\/label>/s);
  if (resM) residencia = resM[1].trim();

  return {
    nif: extractField('NIF:'),
    nome,
    tipo: extractField('Tipo:'),
    estado: extractField('Estado:'),
    inadimplente: extractField('Inadimplente:'),
    regime_iva: extractField('Regime de IVA:'),
    residencia_fiscal: residencia,
  };
}
