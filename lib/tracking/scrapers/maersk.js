export async function trackMaersk(value) {
  try {
    const isContainer = /^[A-Z]{4}\d{7}$/.test(value);
    const url = `https://api.maersk.com/track/${encodeURIComponent(value)}?operator=MAEU`;

    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'User-Agent': 'Mozilla/5.0',
      },
      signal: AbortSignal.timeout(10000),
    });

    if (!res.ok) {
      return { events: [], error: `Maersk API: HTTP ${res.status}` };
    }

    const data = await res.json();
    const allEvents = [];

    if (data.containers) {
      for (const container of data.containers) {
        if (container.events) {
          for (const e of container.events) {
            allEvents.push({
              date: e.event_date || e.date || '',
              status: e.status || e.event_type || '',
              location: e.location || e.port || '',
              vessel: e.vessel_name || e.vessel || '',
              voyage: e.voyage || '',
              container: container.container_number || '',
            });
          }
        }
      }
    }

    if (data.events) {
      for (const e of data.events) {
        allEvents.push({
          date: e.event_date || e.date || '',
          status: e.status || e.event_type || '',
          location: e.location || e.port || '',
          vessel: e.vessel_name || e.vessel || '',
          voyage: e.voyage || '',
          container: '',
        });
      }
    }

    return { events: allEvents, error: allEvents.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: `Maersk: ${error.message}` };
  }
}
