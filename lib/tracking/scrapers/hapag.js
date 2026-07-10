const HLAG_API_BASE = 'https://api.hlag.com/hlag/v2/events';

export async function trackHapag(value) {
  const clientId = process.env.HAPAG_CLIENT_ID;
  const clientSecret = process.env.HAPAG_CLIENT_SECRET;

  if (!clientId || !clientSecret) {
    return {
      events: [],
      error: 'Hapag-Lloyd API não configurada. Contacte o administrador.',
    };
  }

  try {
    const isBL = /^\d{10,12}$/.test(value);
    const paramName = isBL ? 'transportDocumentReference' : 'equipmentReference';
    const url = `${HLAG_API_BASE}?${paramName}=${encodeURIComponent(value)}`;

    const auth = Buffer.from(`${clientId}:${clientSecret}`).toString('base64');
    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Basic ${auth}`,
      },
      signal: AbortSignal.timeout(10000),
    });

    if (!res.ok) {
      return { events: [], error: `Hapag-Lloyd API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const allEvents = [];

    if (data.transportEvents) {
      for (const e of data.transportEvents) {
        allEvents.push({
          date: e.eventDateTime || '',
          status: e.description || e.transportEventTypeCode || '',
          location: e.eventLocation?.locationName || e.transportCall?.unLocationCode || '',
          vessel: e.transportCall?.vessel?.vesselName || '',
        });
      }
    }

    if (data.equipmentEvents) {
      for (const e of data.equipmentEvents) {
        allEvents.push({
          date: e.eventDateTime || '',
          status: e.description || e.equipmentEventTypeCode || '',
          location: e.eventLocation?.locationName || e.transportCall?.unLocationCode || '',
          vessel: e.transportCall?.vessel?.vesselName || '',
        });
      }
    }

    return { events: allEvents, error: allEvents.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `Hapag-Lloyd: ${error.message}` };
  }
}
