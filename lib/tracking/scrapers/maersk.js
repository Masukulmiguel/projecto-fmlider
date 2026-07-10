const MAERSK_API_BASE = 'https://api.maersk.com/shipmenttracking/v2';

export async function trackMaersk(value) {
  const apiKey = process.env.MAERSK_API_KEY;

  if (!apiKey) {
    return {
      events: [],
      error: 'Maersk API não configurada. Contacte o administrador.',
    };
  }

  try {
    const isBL = /^\d{10,12}$/.test(value);
    const paramName = isBL ? 'transportDocumentReference' : 'equipmentReference';
    const url = `${MAERSK_API_BASE}/events?${paramName}=${encodeURIComponent(value)}`;

    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${apiKey}`,
      },
      signal: AbortSignal.timeout(10000),
    });

    if (!res.ok) {
      return { events: [], error: `Maersk API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const allEvents = [];

    if (data.transportEvents) {
      for (const e of data.transportEvents) {
        allEvents.push({
          date: e.eventDateTime || '',
          status: e.description || e.transportEventTypeCode || '',
          location: e.eventLocation?.locationName || '',
          vessel: e.transportCall?.vessel?.vesselName || '',
        });
      }
    }

    if (data.equipmentEvents) {
      for (const e of data.equipmentEvents) {
        allEvents.push({
          date: e.eventDateTime || '',
          status: e.description || e.equipmentEventTypeCode || '',
          location: e.eventLocation?.locationName || '',
          vessel: e.transportCall?.vessel?.vesselName || '',
        });
      }
    }

    return { events: allEvents, error: allEvents.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `Maersk: ${error.message}` };
  }
}
