export async function trackMaersk(value) {
  try {
    const apiUrl = `https://api.maersk.com/shipmenttracking/v2/tracking?trackingNumber=${encodeURIComponent(value)}`;
    const res = await fetch(apiUrl, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Accept': 'application/json',
      },
      redirect: 'follow',
      signal: AbortSignal.timeout(8000),
    });

    if (!res.ok) {
      return { events: [], error: `Maersk API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const events = [];

    if (data && data.events) {
      for (const e of data.events) {
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
    return { events: [], error: `Maersk: ${error.message}` };
  }
}
