export async function trackNaiber(value) {
  try {
    const url = `http://pslnavegacao.com/tracking.html`;
    const res = await fetch(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
      },
      redirect: 'follow',
      signal: AbortSignal.timeout(8000),
    });

    const html = await res.text();
    const events = [];

    const regex = /<tr[^>]*>[\s\S]*?<td[^>]*>(.*?)<\/td>[\s\S]*?<td[^>]*>(.*?)<\/td>[\s\S]*?<td[^>]*>(.*?)<\/td>[\s\S]*?<\/tr>/gi;
    let match;
    while ((match = regex.exec(html)) !== null) {
      const date = match[1].replace(/<[^>]+>/g, '').trim();
      const status = match[2].replace(/<[^>]+>/g, '').trim();
      const location = match[3].replace(/<[^>]+>/g, '').trim();
      if (status && /container|BL|contentor|carga/i.test(status + ' ' + value)) {
        events.push({ date, status, location });
      }
    }

    return { events, error: events.length === 0 ? 'Nenhum evento encontrado para este contentor' : null };
  } catch (error) {
    return { events: [], error: `Naiber: ${error.message}` };
  }
}
