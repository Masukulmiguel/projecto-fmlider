export async function trackHapag(value) {
  const trackingUrl = `https://www.hapag-lloyd.com/en/tracking?containerNumber=${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: 'Rastreamento disponível no site da Hapag-Lloyd',
  };
}
