import { setCorsHeaders, handleOptions } from '../_lib/cors.js'
import { createClient } from '@supabase/supabase-js'

export default async function handler(req, res) {
  setCorsHeaders(req, res)
  if (req.method === 'OPTIONS') return handleOptions(req, res)
  if (req.method !== 'POST') return res.status(405).json({ success: false, message: 'Method not allowed' })

  const supabaseUrl = process.env.VITE_SUPABASE_URL || process.env.SUPABASE_URL || ''
  const supabaseKey = process.env.SUPABASE_SERVICE_KEY || process.env.SUPABASE_KEY || ''

  if (!supabaseUrl || !supabaseKey) {
    return res.status(500).json({ success: false, message: 'Supabase env vars not configured' })
  }

  const supabase = createClient(supabaseUrl, supabaseKey)

  const { email, username } = req.body || {}
  if (!email || !username) {
    return res.status(422).json({ success: false, message: 'Email e username são obrigatórios.' })
  }

  try {
    const { data: user, error } = await supabase
      .from('users')
      .select('id, name, approval_status')
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
      .select('company_name')
      .eq('user_id', user.id)
      .single()

    return res.status(200).json({
      success: true,
      data: {
        user: { name: user.name },
        company: company ? { company_name: company.company_name } : null,
      },
    })
  } catch (err) {
    console.error('Verify client error:', err.message)
    return res.status(500).json({ success: false, message: 'Erro ao verificar cliente.' })
  }
}
