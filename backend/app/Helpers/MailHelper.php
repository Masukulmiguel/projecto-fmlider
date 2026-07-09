<?php

namespace App\Helpers;

class MailHelper
{
    private static string $fromEmail;
    private static string $fromName;
    private static string $frontendUrl;

    public static function init(): void
    {
        self::$fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@fmlider.co.ao';
        self::$fromName = getenv('MAIL_FROM_NAME') ?: 'FMLider';
        self::$frontendUrl = getenv('FRONTEND_URL') ?: 'https://fmlider.co.ao';
    }

    public static function sendApprovalEmail(string $toEmail, string $toName, string $loginUrl): bool
    {
        self::init();

        $subject = 'Conta Aprovada - FMLider';
        $html = self::approvalTemplate($toName, $loginUrl);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To: ' . self::$fromEmail,
            'X-Mailer: FMLider-Mailer/1.0',
        ];

        return @mail($toEmail, $subject, $html, implode("\r\n", $headers));
    }

    public static function sendRejectionEmail(string $toEmail, string $toName, string $reason = ''): bool
    {
        self::init();

        $subject = 'Conta Rejeitada - FMLider';
        $html = self::rejectionTemplate($toName, $reason);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To: ' . self::$fromEmail,
            'X-Mailer: FMLider-Mailer/1.0',
        ];

        return @mail($toEmail, $subject, $html, implode("\r\n", $headers));
    }

    public static function sendPasswordResetEmail(string $toEmail, string $toName, string $newPassword): bool
    {
        self::init();

        $subject = 'Senha Reposta - FMLider';
        $html = self::passwordResetTemplate($toName, $newPassword);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To: ' . self::$fromEmail,
            'X-Mailer: FMLider-Mailer/1.0',
        ];

        return @mail($toEmail, $subject, $html, implode("\r\n", $headers));
    }

    public static function sendWelcomeEmail(string $toEmail, string $toName): bool
    {
        self::init();

        $subject = 'Bem-vindo à FMLider - Conta Criada';
        $html = self::welcomeTemplate($toName);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To: ' . self::$fromEmail,
            'X-Mailer: FMLider-Mailer/1.0',
        ];

        return @mail($toEmail, $subject, $html, implode("\r\n", $headers));
    }

    private static function approvalTemplate(string $name, string $loginUrl): string
    {
        $appName = self::$fromName;
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#1a365d 0%,#2563eb 100%);padding:40px 32px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700;">{$appName}</h1>
    <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Transitário & Logística</p>
  </td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#0f172a;margin:0 0 16px;font-size:20px;">Conta Aprovada!</h2>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Olá <strong>{$name}</strong>,</p>
    <p style="color:#475569;line-height:1.6;margin:0 0 24px;">A sua conta foi aprovada pelo administrador. Já pode aceder à plataforma FMLider para gerir os seus embarques, cotações e muito mais.</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;"><tr>
      <td style="background:#2563eb;border-radius:10px;">
        <a href="{$loginUrl}" style="display:inline-block;padding:14px 36px;color:#fff;text-decoration:none;font-weight:600;font-size:16px;">Aceder à Plataforma</a>
      </td>
    </tr></table>
    <p style="color:#94a3b8;font-size:13px;line-height:1.5;margin:0;">Se não criou esta conta, pode ignorar este email.</p>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">© 2026 {$appName} - Transitário & Logística, Luanda, Angola</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }

    private static function rejectionTemplate(string $name, string $reason): string
    {
        $appName = self::$fromName;
        $reasonBlock = $reason
            ? '<p style="color:#475569;line-height:1.6;margin:0 0 16px;"><strong>Motivo:</strong> ' . htmlspecialchars($reason) . '</p>'
            : '';
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#1a365d 0%,#2563eb 100%);padding:40px 32px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700;">{$appName}</h1>
    <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Transitário & Logística</p>
  </td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#dc3545;margin:0 0 16px;font-size:20px;">Conta Rejeitada</h2>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Olá <strong>{$name}</strong>,</p>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Infelizmente a sua conta não foi aprovada pelo administrador.</p>
    {$reasonBlock}
    <p style="color:#475569;line-height:1.6;margin:0 0 24px;">Se tiver dúvidas, entre em contacto connosco através do email <a href="mailto:geral@fmlider.co.ao" style="color:#2563eb;">geral@fmlider.co.ao</a>.</p>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">© 2026 {$appName} - Transitário & Logística, Luanda, Angola</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }

    private static function passwordResetTemplate(string $name, string $newPassword): string
    {
        $appName = self::$fromName;
        $loginUrl = self::$frontendUrl . '/login';
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#1a365d 0%,#2563eb 100%);padding:40px 32px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700;">{$appName}</h1>
    <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Transitário & Logística</p>
  </td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#0f172a;margin:0 0 16px;font-size:20px;">Senha Reposição</h2>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Olá <strong>{$name}</strong>,</p>
    <p style="color:#475569;line-height:1.6;margin:0 0 24px;">O administrador gerou uma nova senha para a sua conta. Utilize a senha abaixo para iniciar sessão:</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;width:100%;"><tr><td style="background:#f1f5f9;border:2px dashed #2563eb;border-radius:10px;padding:16px;text-align:center;">
      <p style="margin:0;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;">A sua nova senha</p>
      <p style="margin:8px 0 0;color:#1a365d;font-size:24px;font-weight:700;font-family:monospace;letter-spacing:2px;">{$newPassword}</p>
    </td></tr></table>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;"><tr>
      <td style="background:#2563eb;border-radius:10px;">
        <a href="{$loginUrl}" style="display:inline-block;padding:14px 36px;color:#fff;text-decoration:none;font-weight:600;font-size:16px;">Iniciar Sessão</a>
      </td>
    </tr></table>
    <p style="color:#dc3545;font-size:13px;line-height:1.5;margin:0 0 8px;"><strong>Importante:</strong> Por segurança, recomendamos que altere esta senha após iniciar sessão.</p>
    <p style="color:#94a3b8;font-size:13px;line-height:1.5;margin:0;">Se não solicitou esta alteração, entre em contacto com o administrador.</p>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">© 2026 {$appName} - Transitário & Logística, Luanda, Angola</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }

    private static function welcomeTemplate(string $name): string
    {
        $appName = self::$fromName;
        $siteUrl = self::$frontendUrl;
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#1a365d 0%,#2563eb 100%);padding:40px 32px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:24px;font-weight:700;">{$appName}</h1>
    <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Transitário & Logística</p>
  </td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#0f172a;margin:0 0 16px;font-size:20px;">Bem-vindo à {$appName}!</h2>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">Olá <strong>{$name}</strong>,</p>
    <p style="color:#475569;line-height:1.6;margin:0 0 16px;">A sua conta foi criada com sucesso. Estamos a processar o seu registo e em breve receberá uma notificação quando a sua conta for aprovada.</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;width:100%;"><tr><td style="background:#fffbeb;border:1px solid #f59e0b;border-radius:10px;padding:16px;">
      <p style="margin:0;color:#92400e;font-size:14px;line-height:1.5;"><strong>Processo de Aprovação</strong><br>A sua conta está a ser analisada pelo administrador. Receberá um email assim que a conta for aprovada, com as instruções para aceder à plataforma.</p>
    </td></tr></table>
    <p style="color:#475569;line-height:1.6;margin:0 0 8px;">Enquanto aguarda, pode visitar o nosso site para conhecer os nossos serviços:</p>
    <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;"><tr>
      <td style="background:#2563eb;border-radius:10px;">
        <a href="{$siteUrl}" style="display:inline-block;padding:14px 36px;color:#fff;text-decoration:none;font-weight:600;font-size:16px;">Visitar Site</a>
      </td>
    </tr></table>
    <p style="color:#94a3b8;font-size:13px;line-height:1.5;margin:0;">Se tiver dúvidas, entre em contacto connosco através do email <a href="mailto:geral@fmlider.co.ao" style="color:#2563eb;">geral@fmlider.co.ao</a>.</p>
  </td></tr>
  <tr><td style="background:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">© 2026 {$appName} - Transitário & Logística, Luanda, Angola</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }
}
