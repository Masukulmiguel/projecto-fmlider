import { getBrowser } from '../../lib/tracking/scrapers/browser.js';

export async function trackMaersk(value) {
  const browser = await getBrowser();
  if (!browser) {
    return { events: [], error: 'Serviço de rastreamento temporariamente indisponível. Tente novamente mais tarde.' };
  }
  const page = await browser.newPage();
  try {
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
    );
    await page.goto('https://www.maersk.com/tracking', { waitUntil: 'networkidle2', timeout: 30000 });
    await page.waitForSelector('input[type="text"], input[name*="tracking"], input[placeholder*="B/L"]', { timeout: 10000 });
    const inputSelector = 'input[type="text"], input[name*="tracking"], input[placeholder*="B/L"]';
    await page.type(inputSelector, value, { delay: 50 });
    await page.keyboard.press('Enter');
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
    await new Promise(r => setTimeout(r, 3000));
    const events = await page.evaluate(() => {
      const rows = document.querySelectorAll('table tbody tr, .tracking-event, [class*="event"]');
      const results = [];
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 2) {
          results.push({
            date: cells[0]?.textContent?.trim() || '',
            status: cells[1]?.textContent?.trim() || '',
            location: cells[2]?.textContent?.trim() || '',
          });
        }
      });
      return results;
    });
    return { events, error: events.length === 0 ? 'Nenhum evento encontrado' : null };
  } catch (error) {
    return { events: [], error: error.message };
  } finally {
    await page.close().catch(() => {});
  }
}
