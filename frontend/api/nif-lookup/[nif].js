const PORTAL_URL = 'https://portaldocontribuinte.minfin.gov.ao';
const LOOKUP_PATH = '/consultar-nif-do-contribuinte';
const AJAX_PATH = '/consultar-headNifId-do-contribuinte';

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'GET') return res.status(405).json({ success: false, message: 'Method not allowed' });

  const { nif } = req.query;
  if (!nif || !/^\d{10}$/.test(nif)) {
    return res.status(422).json({ success: false, message: 'NIF inválido. Deve conter exactamente 10 dígitos.' });
  }

  try {
    const cookieStore = {};

    const viewState = await getViewState(cookieStore);
    if (!viewState) {
      return res.status(502).json({ success: false, message: 'Não foi possível aceder ao portal da AGT.' });
    }

    const result = await postNifLookup(nif, viewState, cookieStore);
    if (!result) {
      return res.status(404).json({ success: false, message: 'NIF não encontrado no portal da AGT.' });
    }

    return res.status(200).json({ success: true, message: 'OK', data: result });
  } catch (err) {
    console.error('NIF lookup error:', err.message);
    return res.status(500).json({ success: false, message: 'Erro ao consultar NIF. Tente novamente.' });
  }
}

async function getViewState(cookieStore) {
  const res = await fetch(PORTAL_URL + LOOKUP_PATH, {
    headers: {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    },
    redirect: 'follow',
  });

  if (!res.ok) return null;

  const html = await res.text();

  const setCookies = res.headers.getSetCookie?.() || [];
  for (const c of setCookies) {
    const [kv] = c.split(';');
    const [k, v] = kv.split('=');
    if (k && v) cookieStore[k.trim()] = v.trim();
  }

  const m = html.match(/name="javax\.faces\.ViewState"[^>]*value="([^"]+)"/);
  if (m) return m[1];

  const m2 = html.match(/name='javax\.faces\.ViewState'[^>]*value='([^']+)'/);
  if (m2) return m2[1];

  return null;
}

async function postNifLookup(nif, viewState, cookieStore) {
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

  const cookieHeader = Object.entries(cookieStore).map(([k, v]) => `${k}=${v}`).join('; ');

  const res = await fetch(PORTAL_URL + AJAX_PATH, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'Faces-Request': 'partial/ajax',
      'X-Requested-With': 'XMLHttpRequest',
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
      'Cookie': cookieHeader,
    },
    body: params.toString(),
    redirect: 'follow',
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
