import nodemailer from 'nodemailer';
import { setCorsHeaders, handleOptions } from '../_lib/cors.js';

const transporter = nodemailer.createTransport({
  host: 'smtp.gmail.com',
  port: 465,
  secure: true,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS?.replace(/\s/g, ''),
  },
});

function welcomeTemplate(name) {
  const appName = 'FMLider';
  const siteUrl = 'https://fmlider-66.vercel.app';
  return `<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#1a365d 0%,#2563eb 100%);padding:40px 32px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700;">${appName}</h1>
    <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Transitário & Logística</p>
  </td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#0f172a;margin:0 0 16px;font-size:20px;">Bem-vindo à ${appName}!</h2>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Olá <strong>${name}</strong>,</p>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">A sua conta foi criada com sucesso. Estamos a processar o seu registo e em breve receberá uma notificação quando a sua conta for aprovada.</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;width:100%;"><tr><td style="background:#fffbeb;border:1px solid #f59e0b;border-radius:10px;padding:16px;">
      <p style="margin:0;color:#92400e;font-size:14px;line-height:1.5;"><strong>Processo de Aprovação</strong><br>A sua conta está a ser analisada pelo administrador. Receberá um email assim que a conta for aprovada, com as instruções para aceder à plataforma.</p>
    </td></tr></table>
    <p style="color:#475569;line-height:1.6;margin:0 0 8px;">Enquanto aguarda, pode visitar o nosso site para conhecer os nossos serviços:</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;"><tr>
      <td style="background:#2563eb;border-radius:10px;">
        <a href="${siteUrl}" style="display:inline-block;padding:14px 36px;color:#fff;text-decoration:none;font-weight:600;font-size:16px;">Visitar Site</a>
      </td>
    </tr></table>
    <p style="color:#94a3b8;font-size:13px;line-height:1.5;margin:0;">Se tiver dúvidas, entre em contacto connosco através do email <a href="mailto:geral@fmlider.co.ao" style="color:#2563eb;">geral@fmlider.co.ao</a>.</p>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">© 2026 ${appName} - Transitário & Logística, Luanda, Angola</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>`;
}

export default async function handler(req, res) {
  setCorsHeaders(req, res);
  if (req.method === 'OPTIONS') return handleOptions(req, res);
  if (req.method !== 'POST') return res.status(405).json({ success: false, message: 'Method not allowed' });

  const { email, name } = req.body || {};
  if (!email || !name) {
    return res.status(422).json({ success: false, message: 'Email e nome são obrigatórios' });
  }

  if (!process.env.SMTP_USER || !process.env.SMTP_PASS) {
    console.error('SMTP credentials not configured');
    return res.status(500).json({ success: false, message: 'Email service not configured' });
  }

  try {
    await transporter.sendMail({
      from: `"FMLider" <${process.env.SMTP_USER}>`,
      to: email,
      subject: 'Bem-vindo à FMLider - Conta Criada',
      html: welcomeTemplate(name),
    });

    return res.status(200).json({ success: true, message: 'Email de boas-vindas enviado' });
  } catch (err) {
    console.error('Welcome email error:', err.message, err.code);
    return res.status(500).json({ success: false, message: 'Erro ao enviar email', detail: err.message, code: err.code });
  }
}
