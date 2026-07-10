export async function trackHapag(value) {
  try {
    const isBL = /^\d{10,12}$/.test(value);
    const apiUrl = `https://www.hapag-lloyd.com/api/tracking?trackingNumber=${encodeURIComponent(value)}`;
    const res = await fetch(apiUrl, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Accept': 'application/json',
      },
      redirect: 'follow',
      signal: AbortSignal.timeout(8000),
    });

    if (!res.ok) {
      return { events: [], error: `Hapag-Lloyd API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const events = [];

    if (data && data.transportEvents) {
      for (const e of data.transportEvents) {
        events.push({
          date: e.eventDate || e.date || '',
          status: e.eventType || e.status || '',
          location: e.eventLocation || e.location || '',
          vessel: e.vesselName || '',
        });
      }
    }

    return { events, error: events.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `Hapag-Lloyd: ${error.message}` };
  }
}
