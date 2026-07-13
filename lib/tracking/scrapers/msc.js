export async function trackMSC(value) {
  const trackingUrl = `https://www.msc.com/en/track-a-shipment?reference=${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento ${value} disponível no site da MSC`,
  };
}
