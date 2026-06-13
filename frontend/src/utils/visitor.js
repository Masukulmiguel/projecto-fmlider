import axios from 'axios'

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
    const r = await axios.get('https://ipapi.co/json/', { timeout: 4000 })
    if (r.data && !r.data.error) {
      return {
        country: r.data.country_name,
        city: r.data.city,
        region: r.data.region,
      }
    }
  } catch (e) { /* silent */ }
  try {
    const r = await axios.get('https://ipwho.is/', { timeout: 4000 })
    if (r.data && r.data.success !== false) {
      return {
        country: r.data.country,
        city: r.data.city,
        region: r.data.region,
      }
    }
  } catch (e) { /* silent */ }
  return null
}

export const trackVisitor = async () => {
  if (tracked) return
  tracked = true

  try {
    let geo = null
    try { geo = await fetchGeo() } catch (e) {}

    let geoInfo = null
    if (geo) {
      try {
        const r = await axios.get(`https://nominatim.openstreetmap.org/reverse`, {
          params: { lat: geo.lat, lon: geo.lon, format: 'json', 'accept-language': 'pt' },
          timeout: 4000
        })
        if (r.data && r.data.address) {
          geoInfo = {
            country: r.data.address.country,
            city: r.data.address.city || r.data.address.town || r.data.address.village,
            region: r.data.address.state || r.data.address.region
          }
        }
      } catch (e) { /* silent */ }
    }
    if (!geoInfo) geoInfo = await ipLookup()

    await axios.post('/api/visitors/track', {
      page_url: window.location.href,
      referrer: document.referrer || '',
      country: geoInfo?.country || null,
      city: geoInfo?.city || null,
      region: geoInfo?.region || null,
    })
  } catch (e) {
    tracked = false
  }
}

export const resetTracking = () => { tracked = false }
