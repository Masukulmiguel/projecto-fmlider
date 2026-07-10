import { getBrowser } from './browser.js';

export async function trackMSC(value) {
  const browser = await getBrowser();
  const page = await browser.newPage();

  try {
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
    );

    await page.goto('https://www.msc.com/track-a-shipment', {
      waitUntil: 'networkidle2',
      timeout: 30000,
    });

    await page.waitForSelector('input[type="text"], input[name*="tracking"], input[placeholder*="container"], input[placeholder*="BL"]', { timeout: 10000 });

    const inputSelector = 'input[type="text"], input[name*="tracking"], input[placeholder*="container"], input[placeholder*="BL"]';
    await page.type(inputSelector, value, { delay: 50 });

    const btnSelector = 'button[type="submit"], button[class*="track"], button[class*="search"]';
    await page.click(btnSelector).catch(() => page.keyboard.press('Enter'));

    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});

    await new Promise(r => setTimeout(r, 3000));

    const events = await page.evaluate(() => {
      const rows = document.querySelectorAll('table tbody tr, .tracking-event, [class*="event"], [class*="shipment-detail"]');
      const results = [];

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 2) {
          results.push({
            date: cells[0]?.textContent?.trim() || '',
            status: cells[1]?.textContent?.trim() || '',
            location: cells[2]?.textContent?.trim() || '',
          });
        } else {
          const text = row.textContent?.trim();
          if (text && text.length > 5) {
            results.push({ status: text, date: '', location: '' });
          }
        }
      });

      return results;
    });

    return { success: true, events };
  } catch (error) {
    return { success: false, error: error.message, events: [] };
  } finally {
    await page.close();
  }
}
