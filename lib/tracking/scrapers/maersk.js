export async function trackMaersk(value) {
  const strategies = [
    () => tryMaerskInternalApi(value),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result && result.events && result.events.length > 0) {
        return result;
      }
    } catch (_) {}
  }

  const trackingUrl = `https://www.maersk.com/tracking/${encodeURIComponent(value)}`;
  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: 'Rastreamento disponível no site da Maersk',
  };
}

async function tryMaerskInternalApi(value) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 10000);
  try {
    const url = `https://www.maersk.com/tracking/api/tracking?reference=${encodeURIComponent(value)}`;
    const r = await fetch(url, {
      signal: controller.signal,
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'application/json',
        'Referer': 'https://www.maersk.com/tracking/',
      },
    });
    clearTimeout(timer);
    if (!r.ok) return null;

    const data = await r.json();
    const events = [];

    if (data.containers) {
      for (const c of data.containers) {
        if (c.events) {
          for (const e of c.events) {
            events.push({
              date: e.eventDateTime || e.eventDate || '',
              status: e.eventType || e.status || e.description || '',
              location: e.location || e.port || '',
              vessel: e.vesselName || e.vessel || '',
              voyage: e.voyageNumber || '',
              container: c.containerNumber || '',
            });
          }
        }
      }
    }

    if (data.events) {
      for (const e of data.events) {
        events.push({
          date: e.eventDateTime || e.eventDate || '',
          status: e.eventType || e.status || e.description || '',
          location: e.location || e.port || '',
          vessel: e.vesselName || e.vessel || '',
          voyage: e.voyageNumber || '',
          container: '',
        });
      }
    }

    return events.length > 0 ? { events, redirect: null, error: null } : null;
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}
