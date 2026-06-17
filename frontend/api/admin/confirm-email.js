export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*')
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')

  if (req.method === 'OPTIONS') {
    return res.status(200).end()
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' })
  }

  const { user_id } = req.body

  if (!user_id) {
    return res.status(400).json({ error: 'user_id is required' })
  }

  const supabaseUrl = process.env.SUPABASE_URL
  const serviceKey = process.env.SUPABASE_SERVICE_ROLE_KEY

  if (!supabaseUrl || !serviceKey) {
    console.error('Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY env vars')
    return res.status(500).json({ error: 'Server configuration error' })
  }

  try {
    const response = await fetch(`${supabaseUrl}/auth/v1/admin/users/${user_id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${serviceKey}`,
        'apikey': serviceKey,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email_confirm: true }),
    })

    if (!response.ok) {
      const err = await response.text()
      console.error('Supabase admin error:', response.status, err)
      return res.status(response.status).json({ error: 'Failed to confirm email', details: err })
    }

    return res.status(200).json({ success: true, message: 'Email confirmed' })
  } catch (e) {
    console.error('Error confirming email:', e)
    return res.status(500).json({ error: 'Internal server error' })
  }
}
