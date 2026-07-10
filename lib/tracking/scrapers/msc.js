export async function trackMSC(value) {
  try {
    const apiUrl = `https://www.msc.com/services/tracking/api/tracking?reference=${encodeURIComponent(value)}`;
    const res = await fetch(apiUrl, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Accept': 'application/json',
      },
      redirect: 'follow',
      signal: AbortSignal.timeout(8000),
    });

    if (!res.ok) {
      return { events: [], error: `MSC API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const events = [];

    if (data && data.movements) {
      for (const m of data.movements) {
        events.push({
          date: m.date || m.eventDate || '',
          status: m.eventType || m.status || '',
          location: m.location || m.port || '',
          vessel: m.vesselName || '',
        });
      }
    }

    return { events, error: events.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `MSC: ${error.message}` };
  }
}
