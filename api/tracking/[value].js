import { detectCarrier, normalizeEvents } from '../../lib/tracking/scrapers/browser.js';
import { trackHapag } from '../../lib/tracking/scrapers/hapag.js';
import { trackMSC } from '../../lib/tracking/scrapers/msc.js';
import { trackMaersk } from '../../lib/tracking/scrapers/maersk.js';
import { trackCMACGM } from '../../lib/tracking/scrapers/cmacgm.js';
import { trackNaiber } from '../../lib/tracking/scrapers/naiber.js';

let supabaseClient = null;

function getSupabase() {
  if (supabaseClient) return supabaseClient;
  if (!process.env.SUPABASE_URL || !process.env.SUPABASE_SERVICE_KEY) return null;
  try {
    const { createClient } = require('@supabase/supabase-js');
    supabaseClient = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_KEY);
    return supabaseClient;
  } catch (e) {
    return null;
  }
}

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
    const db = getSupabase();

    if (db) {
      try {
        const { data } = await db
          .from('container_events')
          .select('events, cached_at')
          .eq('container_number', cleanValue)
          .gte('cached_at', new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString())
          .single();

        if (data) {
          return res.status(200).json({
            success: true,
            data: {
              input: value.trim(),
              carrier: carrierName,
              carrierId: carrier,
              events: data.events,
              cached: true,
              cachedAt: data.cached_at,
            },
          });
        }
      } catch (e) {}
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

    if (events.length > 0 && db) {
      try {
        await db.from('container_events').upsert(
          {
            container_number: cleanValue,
            carrier,
            events,
            cached_at: new Date().toISOString(),
          },
          { onConflict: 'container_number' }
        );
      } catch (e) {}
    }

    return res.status(200).json({
      success: true,
      data: {
        input: value.trim(),
        carrier: carrierName,
        carrierId: carrier,
        events,
        redirect: result.redirect || null,
        cached: false,
        error: result.error || null,
        message: result.message || null,
      },
    });
  } catch (error) {
    console.error('Tracking error:', error.message);
    return res.status(200).json({
      success: true,
      data: {
        input: value.trim(),
        carrier: carrierName,
        carrierId: carrier,
        events: [],
        message: 'Erro ao rastrear. Tente novamente mais tarde.',
      },
    });
  }
}
