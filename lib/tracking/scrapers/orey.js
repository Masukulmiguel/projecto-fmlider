export async function trackOREY(value) {
  const trackingUrl = `https://orey-angola.co.ao/`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: `Rastreamento BL ${value} disponível no site da OREY Angola`,
  };
}
