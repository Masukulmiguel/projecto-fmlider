import { createClient } from '@supabase/supabase-js'

const supabaseUrl = process.env.VITE_SUPABASE_URL
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*')
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type')

  if (req.method === 'OPTIONS') return res.status(200).end()
  if (req.method !== 'POST') return res.status(405).json({ success: false, message: 'Method not allowed' })

  if (!supabaseUrl || !supabaseServiceKey) {
    return res.status(500).json({ success: false, message: 'Supabase env vars not configured' })
  }

  const supabase = createClient(supabaseUrl, supabaseServiceKey)

  try {
    const { email, password } = req.body || {}

    if (!email || !password) {
      return res.status(422).json({ success: false, message: 'Email and password required' })
    }

    const adminEmail = email || 'admin@fmlider.co.ao'
    const adminPassword = password || 'Admin@2024!'

    const { data: existingUsers } = await supabase.auth.admin.listUsers()
    const adminExists = existingUsers?.users?.some(u => u.email === adminEmail)

    if (adminExists) {
      return res.status(200).json({
        success: true,
        message: 'Admin user already exists',
        data: { email: adminEmail }
      })
    }

    const { data: authUser, error: authError } = await supabase.auth.admin.createUser({
      email: adminEmail,
      password: adminPassword,
      email_confirm: true,
      user_metadata: {
        name: 'Administrador',
        role: 'admin'
      }
    })

    if (authError) {
      return res.status(500).json({ success: false, message: `Auth error: ${authError.message}` })
    }

    const { error: profileError } = await supabase
      .from('users')
      .insert({
        id: authUser.user.id,
        username: 'admin',
        name: 'Administrador',
        email: adminEmail,
        phone: '+244 935141747',
        role: 'admin',
        status: 1,
        approval_status: 'approved'
      })

    if (profileError) {
      return res.status(200).json({
        success: true,
        message: `Auth user created but profile insert failed: ${profileError.message}. You may need to insert the profile manually.`,
        data: { userId: authUser.user.id, email: adminEmail }
      })
    }

    return res.status(200).json({
      success: true,
      message: 'Admin user created successfully',
      data: { userId: authUser.user.id, email: adminEmail }
    })

  } catch (err) {
    return res.status(500).json({ success: false, message: err.message })
  }
}
