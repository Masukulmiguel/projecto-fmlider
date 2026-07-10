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
  if (/^MSDU|^MSKU/.test(v)) return { carrier: 'maersk', name: 'Maersk' };
  if (/^MSCU|^MEDU/.test(v)) return { carrier: 'msc', name: 'MSC' };
  if (/^CMAU/.test(v)) return { carrier: 'cmacgm', name: 'CMA CGM' };
  if (/^EISU/.test(v)) return { carrier: 'evergreen', name: 'Evergreen' };
  if (/^PCIU/.test(v)) return { carrier: 'pil', name: 'PIL' };
  if (/^ONEY|^ONEY/.test(v)) return { carrier: 'one', name: 'ONE' };
  if (/^COSU/.test(v)) return { carrier: 'cosco', name: 'COSCO' };
  if (/^OOLU/.test(v)) return { carrier: 'oocl', name: 'OOCL' };
  if (/^SEGU/.test(v)) return { carrier: 'sealand', name: 'Sealand' };
  if (/^HDMU/.test(v)) return { carrier: 'hmm', name: 'HMM' };
  if (/^KKFU|^KLCU/.test(v)) return { carrier: 'kline', name: 'K Line' };
  if (/^MOLU|^MOEU/.test(v)) return { carrier: 'mol', name: 'MOL' };
  if (/^NYKU/.test(v)) return { carrier: 'nyk', name: 'NYK' };
  if (/^ZIMU/.test(v)) return { carrier: 'zim', name: 'ZIM' };
  if (/^WFHU/.test(v)) return { carrier: 'wanhai', name: 'Wan Hai' };
  if (/^SINU/.test(v)) return { carrier: 'sinotrans', name: 'Sinotrans' };
  if (/^TGHU/.test(v)) return { carrier: 'tropical', name: 'Tropical Shipping' };
  if (/^GCLU|^GESU/.test(v)) return { carrier: 'tex', name: 'Textainer' };
  if (/^FCIU|^FSCU/.test(v)) return { carrier: 'florens', name: 'Florens' };

  if (/^\d{10,12}$/.test(v)) return { carrier: 'hapag', name: 'Hapag-Lloyd (BL)' };

  return { carrier: 'unknown', name: 'Desconhecida' };
}

export async function closeBrowser() {
  // No-op - Puppeteer removed for Vercel Hobby compatibility
}
