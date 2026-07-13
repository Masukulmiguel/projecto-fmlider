export async function trackGrimaldi(value) {
  const strategies = [
    () => tryGrimaldiWebsite(value),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result && result.events && result.events.length > 0) {
        return result;
      }
    } catch (_) {}
  }

  const trackingUrl = `https://www.grimaldi.com/en/tracking`;
  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento BL ${value} disponível no site da Grimaldi`,
  };
}

async function tryGrimaldiWebsite(value) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 10000);
  try {
    const url = `https://www.gnet.grimaldi-eservice.com/services/api/Info?ShipmentNo=${encodeURIComponent(value)}`;
    const r = await fetch(url, {
      signal: controller.signal,
      headers: {
        'Accept': 'application/json',
        'User-Agent': 'FMLider-App/1.0',
      },
    });
    clearTimeout(timer);
    if (!r.ok) return null;

    const data = await r.json();
    const events = [];
    if (Array.isArray(data)) {
      for (const item of data) {
        if (item.TRACKING_EVENT || item.TRACKING_DATETIME) {
          events.push({
            date: item.TRACKING_DATETIME || '',
            status: item.TRACKING_EVENT || '',
            location: item.POD || item.POL || '',
            vessel: item.VOYAGE || '',
            voyage: item.VOYAGE || '',
            container: '',
          });
        }
      }
    }
    return events.length > 0 ? { events, redirect: null, error: null } : null;
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}
