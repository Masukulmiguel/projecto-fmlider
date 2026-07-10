export function normalizeEvents(events) {
  return events
    .filter(e => e.date || e.status)
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .map(e => ({
      date: e.date || null,
      status: e.status || '',
      location: e.location || '',
      vessel: e.vessel || '',
      voyage: e.voyage || '',
      container: e.container || '',
    }));
}

export function detectCarrier(value) {
  const v = value.toUpperCase().replace(/[\s-]/g, '');

  if (/^HLCU/.test(v)) return { carrier: 'hapag', name: 'Hapag-Lloyd' };
  if (/^MSDU/.test(v)) return { carrier: 'maersk', name: 'Maersk' };
  if (/^MSCU/.test(v)) return { carrier: 'msc', name: 'MSC' };
  if (/^CMAU/.test(v)) return { carrier: 'cmacgm', name: 'CMA CGM' };
  if (/^MSKU/.test(v)) return { carrier: 'maersk', name: 'Maersk' };
  if (/^EISU/.test(v)) return { carrier: 'evergreen', name: 'Evergreen' };
  if (/^PCIU/.test(v)) return { carrier: 'pil', name: 'PIL' };
  if (/^ONEY/.test(v)) return { carrier: 'one', name: 'ONE' };

  if (/^\d{10,12}$/.test(v)) return { carrier: 'hapag', name: 'Hapag-Lloyd (BL)' };

  return { carrier: 'unknown', name: 'Desconhecida' };
}

export async function closeBrowser() {
  // No-op - Puppeteer removed for Vercel Hobby compatibility
}
