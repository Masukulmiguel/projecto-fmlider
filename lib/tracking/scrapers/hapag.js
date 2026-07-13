export async function trackHapag(value) {
  const isContainer = /^[A-Z]{4}\d{7}$/i.test(value);
  const trackingUrl = isContainer
    ? `https://www.hapag-lloyd.com/en/tracking?containerNumber=${encodeURIComponent(value)}`
    : `https://www.hapag-lloyd.com/en/tracking?blNumber=${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento ${value} disponível no site da Hapag-Lloyd`,
  };
}
