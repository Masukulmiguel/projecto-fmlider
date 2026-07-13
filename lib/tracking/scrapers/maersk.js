export async function trackMaersk(value) {
  const trackingUrl = `https://www.maersk.com/tracking/${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento ${value} disponível no site da Maersk`,
  };
}
