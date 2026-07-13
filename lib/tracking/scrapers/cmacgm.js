export async function trackCMACGM(value) {
  const isBL = !/^[A-Z]{4}\d{7}$/i.test(value);

  const strategies = [
    () => tryCmaCgmScrape(value, isBL),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result && result.events && result.events.length > 0) {
        return result;
      }
    } catch (_) {}
  }

  const trackingUrl = isBL
    ? `https://www.cma-cgm.com/ebusiness/tracking?reference=${encodeURIComponent(value)}`
    : `https://www.cma-cgm.com/ebusiness/tracking?reference=${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento ${isBL ? 'BL' : 'contentor'} disponível no site da CMA CGM`,
  };
}

async function tryCmaCgmScrape(value, isBL) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 10000);
  try {
    const url = `https://www.cma-cgm.com/ebusiness/tracking/result?trackingId=${encodeURIComponent(value)}`;
    const r = await fetch(url, {
      signal: controller.signal,
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      },
    });
    clearTimeout(timer);
    if (!r.ok) return null;

    const html = await r.text();
    const events = parseCmaCgmHtml(html);
    if (events.length > 0) return { events, redirect: null, error: null };
    return null;
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}

function parseCmaCgmHtml(html) {
  const events = [];
  const eventRegex = /<tr[^>]*>[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<td[^>]*>([\s\S]*?)<\/td>[\s\S]*?<\/tr>/gi;
  let match;
  while ((match = eventRegex.exec(html)) !== null) {
    const status = match[1].replace(/<[^>]+>/g, '').trim();
    const location = match[2].replace(/<[^>]+>/g, '').trim();
    const date = match[3].replace(/<[^>]+>/g, '').trim();
    const vessel = match[4].replace(/<[^>]+>/g, '').trim();
    if (status && date) {
      events.push({ date, status, location, vessel, voyage: '', container: '' });
    }
  }
  return events;
}
