import { createClient } from '@supabase/supabase-js';
import { detectCarrier, normalizeEvents } from '../../lib/tracking/scrapers/browser.js';
import { trackHapag } from '../../lib/tracking/scrapers/hapag.js';
import { trackMSC } from '../../lib/tracking/scrapers/msc.js';
import { trackMaersk } from '../../lib/tracking/scrapers/maersk.js';
import { trackCMACGM } from '../../lib/tracking/scrapers/cmacgm.js';
import { trackNaiber } from '../../lib/tracking/scrapers/naiber.js';
import { closeBrowser } from '../../lib/tracking/scrapers/browser.js';

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_KEY
);

const scrapers = {
  hapag: trackHapag,
  msc: trackMSC,
  maersk: trackMaersk,
  cmacgm: trackCMACGM,
  naiber: trackNaiber,
};

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'GET') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  const { value } = req.query;
  if (!value || value.trim().length < 3) {
    return res.status(422).json({
      success: false,
      message: 'Forneça um número de BL ou contentor válido.',
    });
  }

  const cleanValue = value.trim().toUpperCase().replace(/[\s-]/g, '');
  const { carrier, name: carrierName } = detectCarrier(cleanValue);

  try {
    const cached = await getCached(cleanValue);
    if (cached) {
      return res.status(200).json({
        success: true,
        data: {
          input: value.trim(),
          carrier: carrierName,
          carrierId: carrier,
          events: cached.events,
          cached: true,
          cachedAt: cached.cached_at,
        },
      });
    }

    if (carrier === 'unknown') {
      return res.status(200).json({
        success: true,
        data: {
          input: value.trim(),
          carrier: 'Desconhecida',
          carrierId: 'unknown',
          events: [],
          message: 'Não foi possível identificar a transportadora pelo número fornecido.',
        },
      });
    }

    const scraper = scrapers[carrier];
    if (!scraper) {
      return res.status(200).json({
        success: true,
        data: {
          input: value.trim(),
          carrier: carrierName,
          carrierId: carrier,
          events: [],
          message: `Rastreamento para ${carrierName} ainda não disponível.`,
        },
      });
    }

    const result = await scraper(cleanValue);
    const events = normalizeEvents(result.events || []);

    if (events.length > 0) {
      await cacheResult(cleanValue, carrier, events);
    }

    try {
      await closeBrowser();
    } catch (e) {}

    return res.status(200).json({
      success: true,
      data: {
        input: value.trim(),
        carrier: carrierName,
        carrierId: carrier,
        events,
        cached: false,
        error: result.error || null,
      },
    });
  } catch (error) {
    try {
      await closeBrowser();
    } catch (e) {}

    console.error('Tracking error:', error.message);
    return res.status(500).json({
      success: false,
      message: 'Erro ao rastrear. Tente novamente.',
    });
  }
}

async function getCached(value) {
  try {
    const { data } = await supabase
      .from('container_events')
      .select('events, cached_at')
      .eq('container_number', value)
      .gte('cached_at', new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString())
      .single();

    return data;
  } catch {
    return null;
  }
}

async function cacheResult(value, carrier, events) {
  try {
    await supabase.from('container_events').upsert(
      {
        container_number: value,
        carrier,
        events,
        cached_at: new Date().toISOString(),
      },
      { onConflict: 'container_number' }
    );
  } catch (e) {
    console.error('Cache error:', e.message);
  }
}
