import { getBrowser } from './browser.js';

export async function trackHapag(value) {
  const browser = await getBrowser();
  const page = await browser.newPage();

  try {
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
    );

    const isBL = /^\d{10,12}$/.test(value.replace(/[\s-]/g, ''));
    const url = 'https://www.hapag-lloyd.com/en/online-business/tracking.html';

    await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });

    await page.waitForSelector('input[name="tracking-number"], input[id*="tracking"], input[placeholder*="container"], input[placeholder*="BL"]', { timeout: 10000 });

    const inputSelector = 'input[name="tracking-number"], input[id*="tracking"], input[placeholder*="container"], input[placeholder*="BL"]';
    await page.type(inputSelector, value, { delay: 50 });

    await page.keyboard.press('Enter');

    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});

    await new Promise(r => setTimeout(r, 3000));

    const events = await page.evaluate(() => {
      const rows = document.querySelectorAll('table tbody tr, .tracking-event, .event-row, [class*="event"], [class*="shipment"]');
      const results = [];

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 2) {
          results.push({
            date: cells[0]?.textContent?.trim() || '',
            status: cells[1]?.textContent?.trim() || '',
            location: cells[2]?.textContent?.trim() || '',
            vessel: cells[3]?.textContent?.trim() || '',
          });
        } else {
          const text = row.textContent?.trim();
          if (text && text.length > 5) {
            results.push({ status: text, date: '', location: '' });
          }
        }
      });

      if (results.length === 0) {
        const allText = document.body.innerText;
        const lines = allText.split('\n').filter(l => l.trim().length > 10);
        lines.forEach(line => {
          if (/container|BL|vessel|port|arrival|departure|gate/i.test(line)) {
            results.push({ status: line.trim(), date: '', location: '' });
          }
        });
      }

      return results;
    });

    return { success: true, events, raw: events.length === 0 ? 'No events found' : null };
  } catch (error) {
    return { success: false, error: error.message, events: [] };
  } finally {
    await page.close();
  }
}
