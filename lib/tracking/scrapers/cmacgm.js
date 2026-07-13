export async function trackCMACGM(value) {
  const trackingUrl = `https://www.cma-cgm.com/ebusiness/tracking?reference=${encodeURIComponent(value)}`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento ${value} disponível no site da CMA CGM`,
  };
}
