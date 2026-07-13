export async function trackGrimaldi(value) {
  const trackingUrl = `https://www.gnet.grimaldi-eservice.com/GNET/Pages_GAtlas/WFContainerTracking`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento BL ${value} disponível no site da Grimaldi`,
  };
}
