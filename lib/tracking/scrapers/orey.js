export async function trackOREY(value) {
  const strategies = [
    () => tryOREYWebsite(value),
  ];

  for (const strategy of strategies) {
    try {
      const result = await strategy();
      if (result && result.events && result.events.length > 0) {
        return result;
      }
    } catch (_) {}
  }

  const trackingUrl = `https://orey-angola.co.ao/`;
  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento BL ${value} disponível no site da OREY`,
  };
}

async function tryOREYWebsite(value) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 10000);
  try {
    const url = `https://orey-angola.co.ao/`;
    const r = await fetch(url, {
      signal: controller.signal,
      headers: {
        'Accept': 'text/html',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      },
    });
    clearTimeout(timer);
    if (!r.ok) return null;
    return null;
  } catch (_) {
    clearTimeout(timer);
    return null;
  }
}
