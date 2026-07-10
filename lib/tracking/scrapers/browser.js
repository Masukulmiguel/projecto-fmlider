let browserInstance = null;

export async function getBrowser() {
  try {
    const puppeteer = await import('puppeteer-core');
    const chromium = await import('@sparticuz/chromium');

    if (browserInstance && browserInstance.connected) {
      return browserInstance;
    }

    const executablePath = await chromium.default.executablePath(
      'https://github.com/nicholasgasior/aws-lambda-puppeteer/releases/download/v1.0.0/chromium-v1.0.0-pack.tar'
    );

    browserInstance = await puppeteer.default.launch({
      args: chromium.default.args,
      defaultViewport: chromium.default.defaultViewport,
      executablePath,
      headless: chromium.default.headless,
    });

    return browserInstance;
  } catch (error) {
    console.error('Browser launch failed:', error.message);
    return null;
  }
}

export async function closeBrowser() {
  if (browserInstance) {
    try {
      await browserInstance.close();
    } catch (e) {}
    browserInstance = null;
  }
}

export function normalizeEvents(events) {
  return events
    .filter(e => e.date || e.status)
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .map(e => ({
      date: e.date || null,
      status: e.status || '',
      location: e.location || '',
      vessel: e.vessel || '',
      voyage: e.voyage || '',
      container: e.container || '',
    }));
}

export function detectCarrier(value) {
  const v = value.toUpperCase().replace(/[\s-]/g, '');

  if (/^HLCU/.test(v)) return { carrier: 'hapag', name: 'Hapag-Lloyd' };
  if (/^MSDU/.test(v)) return { carrier: 'maersk', name: 'Maersk' };
  if (/^MSCU/.test(v)) return { carrier: 'msc', name: 'MSC' };
  if (/^CMAU/.test(v)) return { carrier: 'cmacgm', name: 'CMA CGM' };
  if (/^MSKU/.test(v)) return { carrier: 'maersk', name: 'Maersk' };
  if (/^EISU/.test(v)) return { carrier: 'evergreen', name: 'Evergreen' };
  if (/^PCIU/.test(v)) return { carrier: 'pil', name: 'PIL' };
  if (/^ONEY/.test(v)) return { carrier: 'one', name: 'ONE' };

  if (/^\d{10,12}$/.test(v)) return { carrier: 'hapag', name: 'Hapag-Lloyd (BL)' };

  return { carrier: 'unknown', name: 'Desconhecida' };
}
