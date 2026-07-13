export async function trackHapag(value) {
  const strategies = [
    () => tryHapagScrape(value),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result && result.events && result.events.length > 0) {
        return result;
      }
    } catch (_) {}
  }

  const trackingUrl = `https://www.hapag-lloyd.com/en/tracking?containerNumber=${encodeURIComponent(value)}`;
  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: 'Rastreamento disponível no site da Hapag-Lloyd',
  };
}

async function tryHapagScrape(value) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 10000);
  try {
    const url = `https://www.hapag-lloyd.com/en/tracking/container-tracking?containerNumber=${encodeURIComponent(value)}`;
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
    const events = [];
    const eventRegex = /data-event[^"]*"([^"]*)"[^>]*data-date[^"]*"([^"]*)"[^>]*data-location[^"]*"([^"]*)"/gi;
    let match;
    while ((match = eventRegex.exec(html)) !== null) {
      events.push({
        date: match[2] || '',
        status: match[1] || '',
        location: match[3] || '',
        vessel: '',
        voyage: '',
        container: '',
      });
    }
    return events.length > 0 ? { events, redirect: null, error: null } : null;
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}
