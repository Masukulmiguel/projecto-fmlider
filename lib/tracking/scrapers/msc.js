const MSC_API_BASE = 'https://ovhweportalapim.azure-api.net/dpo/trackandtrace/v2.2/events';

export async function trackMSC(value) {
  try {
    const url = `${MSC_API_BASE}?equipmentReference=${encodeURIComponent(value)}`;
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' },
      signal: AbortSignal.timeout(10000),
    });

    if (!res.ok) {
      return { events: [], error: `MSC API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const events = (Array.isArray(data) ? data : []).map(e => ({
      date: e.eventDateTime || e.eventCreatedDateTime || '',
      status: e.description || e.equipmentEventTypeCode || e.transportEventTypeCode || '',
      location: e.eventLocation?.locationName || e.transportCall?.unLocationCode || '',
      vessel: e.transportCall?.vessel?.vesselName || '',
    }));

    return { events, error: events.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `MSC: ${error.message}` };
  }
}
