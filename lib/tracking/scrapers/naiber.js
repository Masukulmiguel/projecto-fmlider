export async function trackNaiber(value) {
  const trackingUrl = `https://pslnavegacao.com/?page_id=2830`;

  return {
    events: [],
    redirect: trackingUrl,
    error: null,
    message: 'Rastreamento disponível no site da PSL Navegação',
  };
}
