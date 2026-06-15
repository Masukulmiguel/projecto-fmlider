import { supabase } from '@/lib/supabase'

let tracked = false

const fetchGeo = () => {
  return new Promise((resolve) => {
    if (!navigator.geolocation) return resolve(null)
    navigator.geolocation.getCurrentPosition(
      (pos) => resolve({
        lat: pos.coords.latitude,
        lon: pos.coords.longitude
      }),
      () => resolve(null),
      { timeout: 4000, maximumAge: 600000 }
    )
  })
}

const ipLookup = async () => {
  try {
    const r = await fetch('https://ipapi.co/json/')
    const data = await r.json()
    if (data && !data.error) {
      return { country: data.country_name, city: data.city, region: data.region }
    }
  } catch (e) { /* silent */ }
  try {
    const r = await fetch('https://ipwho.is/')
    const data = await r.json()
    if (data && data.success !== false) {
      return { country: data.country, city: data.city, region: data.region }
    }
  } catch (e) { /* silent */ }
  return null
}

const parseUserAgent = () => {
  const ua = navigator.userAgent || ''
  let browser = 'Unknown'
  let browser_version = ''
  let os = 'Unknown'
  let device_type = 'desktop'

  if (ua.includes('Firefox/')) {
    browser = 'Firefox'
    browser_version = ua.split('Firefox/')[1]?.split(' ')[0] || ''
  } else if (ua.includes('Edg/')) {
    browser = 'Edge'
    browser_version = ua.split('Edg/')[1]?.split(' ')[0] || ''
  } else if (ua.includes('Chrome/')) {
    browser = 'Chrome'
    browser_version = ua.split('Chrome/')[1]?.split(' ')[0] || ''
  } else if (ua.includes('Safari/') && ua.includes('Version/')) {
    browser = 'Safari'
    browser_version = ua.split('Version/')[1]?.split(' ')[0] || ''
  }

  if (ua.includes('Windows')) os = 'Windows'
  else if (ua.includes('Mac OS')) os = 'macOS'
  else if (ua.includes('Linux')) os = 'Linux'
  else if (ua.includes('Android')) os = 'Android'
  else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS'

  if (ua.includes('Mobile') || ua.includes('Android')) device_type = 'mobile'
  else if (ua.includes('Tablet') || ua.includes('iPad')) device_type = 'tablet'

  return { browser, browser_version, os, device_type }
}

const getSessionId = () => {
  let sid = localStorage.getItem('fml_sid')
  if (!sid) {
    sid = crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2) + Date.now().toString(36)
    localStorage.setItem('fml_sid', sid)
  }
  return sid
}

export const trackVisitor = async () => {
  if (tracked) return
  tracked = true

  try {
    let geoInfo = null
    try {
      const geo = await fetchGeo()
      if (geo) {
        const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${geo.lat}&lon=${geo.lon}&format=json&accept-language=pt`)
        const data = await r.json()
        if (data && data.address) {
          geoInfo = {
            country: data.address.country,
            city: data.address.city || data.address.town || data.address.village,
            region: data.address.state || data.address.region
          }
        }
      }
    } catch (e) { /* silent */ }
    if (!geoInfo) geoInfo = await ipLookup()

    const ua = parseUserAgent()
    const sessionId = getSessionId()

    await supabase.from('visitors').insert({
      session_id: sessionId,
      ip_address: null,
      country: geoInfo?.country || null,
      city: geoInfo?.city || null,
      region: geoInfo?.region || null,
      browser: ua.browser,
      browser_version: ua.browser_version,
      os: ua.os,
      device_type: ua.device_type,
      referrer: document.referrer || null,
      page_url: window.location.href,
      user_agent: navigator.userAgent || null,
    })
  } catch (e) {
    tracked = false
  }
}

export const resetTracking = () => { tracked = false }
