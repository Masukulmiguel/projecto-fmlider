import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  process.env.VITE_SUPABASE_URL || process.env.SUPABASE_URL || '',
  process.env.SUPABASE_SERVICE_KEY || process.env.SUPABASE_KEY || ''
)

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*')
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type')

  if (req.method === 'OPTIONS') return res.status(200).end()
  if (req.method !== 'POST') return res.status(405).json({ success: false, message: 'Method not allowed' })

  const { email, username } = req.body || {}
  if (!email || !username) {
    return res.status(422).json({ success: false, message: 'Email e username são obrigatórios.' })
  }

  try {
    const { data: user, error } = await supabase
      .from('users')
      .select('id, username, name, email, role, approval_status, photo')
      .eq('email', email)
      .eq('username', username)
      .eq('role', 'cliente')
      .single()

    if (error || !user) {
      return res.status(404).json({ success: false, message: 'Cliente não encontrado. Verifique o email e username.' })
    }

    if (user.approval_status !== 'approved') {
      return res.status(403).json({ success: false, message: 'Conta ainda não aprovada. Contacte o administrador.' })
    }

    const { data: company } = await supabase
      .from('companies')
      .select('company_name, nif, phone, email, address')
      .eq('user_id', user.id)
      .single()

    return res.status(200).json({
      success: true,
      data: { user, company },
    })
  } catch (err) {
    console.error('Verify client error:', err.message)
    return res.status(500).json({ success: false, message: 'Erro ao verificar cliente.' })
  }
}
