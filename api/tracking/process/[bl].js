import { setCorsHeaders, handleOptions } from '../../_lib/cors.js';
import { createClient } from '@supabase/supabase-js';
import { detectCarrier, normalizeEvents } from '../../../lib/tracking/scrapers/browser.js';
import { trackHapag } from '../../../lib/tracking/scrapers/hapag.js';
import { trackMSC } from '../../../lib/tracking/scrapers/msc.js';
import { trackMaersk } from '../../../lib/tracking/scrapers/maersk.js';
import { trackCMACGM } from '../../../lib/tracking/scrapers/cmacgm.js';
import { trackNaiber } from '../../../lib/tracking/scrapers/naiber.js';
import { closeBrowser } from '../../../lib/tracking/scrapers/browser.js';

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

const CARRIER_STATUS_MAP = {
  arrival: 'chegou_ao_porto',
  departed: 'em_transito',
  departure: 'em_transito',
  loaded: 'em_transito',
  discharged: 'chegou_ao_porto',
  transhipment: 'em_transito',
  transshipment: 'em_transito',
  gate_in: 'em_terminal',
  gate_out: 'em_transporte',
  delivered: 'entregue',
  delivery: 'entregue',
  customs_hold: 'aguardando_chegada',
  customs_release: 'em_transito',
  empty_return: 'devolvido',
  vessel_arrival: 'chegou_ao_porto',
  port_arrival: 'chegou_ao_porto',
};

const FMLIDER_STATUS_LABELS = {
  aguardando_chegada: 'Aguardando Chegada',
  chegou_ao_porto: 'Chegou ao Porto',
  em_terminal: 'Em Terminal',
  na_base: 'Na Base FMLider',
  agendado_para_entrega: 'Agendado p/ Entrega',
  em_transporte: 'Em Transporte',
  entregue: 'Entregue',
  em_transito: 'Em Transito',
  devolvido: 'Devolvido',
  cancelado: 'Cancelado',
};

export default async function handler(req, res) {
  setCorsHeaders(req, res);
  if (req.method === 'OPTIONS') return handleOptions(req, res);
  if (req.method !== 'GET') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  const { bl } = req.query;
  if (!bl || bl.trim().length < 3) {
    return res.status(422).json({
      success: false,
      message: 'Forneça um número de BL válido.',
    });
  }

  const cleanBL = bl.trim().toUpperCase().replace(/[\s-]/g, '');

  try {
    const cached = await getCached(cleanBL);
    if (cached) {
      const fmliderStatus = mapToFmliderStatus(cached.events);
      return res.status(200).json({
        success: true,
        data: {
          bl: bl.trim(),
          carrier: cached.carrier,
          events: cached.events,
          fmliderStatus,
          fmliderStatusLabel: FMLIDER_STATUS_LABELS[fmliderStatus] || fmliderStatus,
          cached: true,
          cachedAt: cached.cached_at,
        },
      });
    }

    const { carrier, name: carrierName } = detectCarrier(cleanBL);
    if (carrier === 'unknown') {
      return res.status(200).json({
        success: true,
        data: {
          bl: bl.trim(),
          carrier: 'Desconhecida',
          events: [],
          fmliderStatus: 'aguardando_chegada',
          fmliderStatusLabel: 'Aguardando Chegada',
          cached: false,
          message: 'Transportadora não identificada pelo número de BL.',
        },
      });
    }

    const scraper = scrapers[carrier];
    if (!scraper) {
      return res.status(200).json({
        success: true,
        data: {
          bl: bl.trim(),
          carrier: carrierName,
          events: [],
          fmliderStatus: 'aguardando_chegada',
          fmliderStatusLabel: 'Aguardando Chegada',
          cached: false,
          message: `Rastreamento para ${carrierName} ainda não disponível.`,
        },
      });
    }

    const result = await scraper(cleanBL);
    const events = normalizeEvents(result.events || []);

    if (events.length > 0) {
      await cacheResult(cleanBL, carrierName, events);
    }

    try { await closeBrowser(); } catch (e) {}

    const fmliderStatus = mapToFmliderStatus(events);

    return res.status(200).json({
      success: true,
      data: {
        bl: bl.trim(),
        carrier: carrierName,
        events,
        fmliderStatus,
        fmliderStatusLabel: FMLIDER_STATUS_LABELS[fmliderStatus] || fmliderStatus,
        cached: false,
        error: result.error || null,
      },
    });
  } catch (error) {
    try { await closeBrowser(); } catch (e) {}
    console.error('BL tracking error:', error.message);
    return res.status(500).json({
      success: false,
      message: 'Erro ao rastrear BL.',
    });
  }
}

function mapToFmliderStatus(events) {
  if (!events || events.length === 0) return 'aguardando_chegada';

  const latestEvent = events[0];
  const statusText = (latestEvent.status || '').toLowerCase();

  for (const [keyword, status] of Object.entries(CARRIER_STATUS_MAP)) {
    if (statusText.includes(keyword)) {
      return status;
    }
  }

  if (statusText.includes('port') || statusText.includes('porto')) return 'chegou_ao_porto';
  if (statusText.includes('terminal')) return 'em_terminal';
  if (statusText.includes('base')) return 'na_base';
  if (statusText.includes('deliver') || statusText.includes('entrega')) return 'entregue';
  if (statusText.includes('depart') || statusText.includes('partida')) return 'em_transito';
  if (statusText.includes('arriv') || statusText.includes('chegada')) return 'chegou_ao_porto';

  return 'em_transito';
}

async function getCached(bl) {
  try {
    const { data } = await supabase
      .from('container_events')
      .select('carrier, events, cached_at')
      .eq('container_number', bl)
      .gte('cached_at', new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString())
      .single();
    return data;
  } catch {
    return null;
  }
}

async function cacheResult(bl, carrier, events) {
  try {
    await supabase.from('container_events').upsert(
      { container_number: bl, carrier, events, cached_at: new Date().toISOString() },
      { onConflict: 'container_number' }
    );
  } catch (e) {
    console.error('Cache error:', e.message);
  }
}
