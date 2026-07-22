<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use GuzzleHttp\Client;

/**
 * MailketingService — SIAP PAKAI.
 * POST ke https://api.mailketing.co.id/api/v1/send
 * Param: api_token, from_name, from_email, recipient, subject, content
 */
class MailketingService
{
    public function send(string $to, string $subject, string $htmlContent): array
    {
        $token = Setting::get('mailketing_api_token', config('threfnet.mailketing.api_token'));
        $endpoint = config('threfnet.mailketing.endpoint');

        try {
            $response = $this->http()->post($endpoint, [
                'form_params' => [
                    'api_token'  => $token,
                    'from_name'  => Setting::get('mailketing_from_name', config('threfnet.mailketing.from_name')),
                    'from_email' => Setting::get('mailketing_from_email', config('threfnet.mailketing.from_email')),
                    'recipient'  => $to,
                    'subject'    => $subject,
                    'content'    => $htmlContent,
                ],
            ]);

            $status = $response->getStatusCode();
            return [
                'ok'   => $status >= 200 && $status < 300,
                'body' => (string) $response->getBody(),
            ];
        } catch (\Throwable $e) {
            report($e);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Template email HTML berbrand THRE.F.NET. */
    public static function template(string $title, string $bodyHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
        <tr>
          <td style="background:#0d6efd;padding:20px 28px;">
            <span style="color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:0.5px;">THRE.F.NET</span>
          </td>
        </tr>
        <tr>
          <td style="padding:28px;">
            <h2 style="margin:0 0 12px;font-size:18px;">{$title}</h2>
            <div style="font-size:14px;line-height:1.6;">{$bodyHtml}</div>
          </td>
        </tr>
        <tr>
          <td style="padding:18px 28px;background:#f8fafc;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
            Email ini dikirim otomatis oleh THRE.F.NET Billing System. Mohon tidak membalas email ini.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    protected function http(): Client
    {
        return new Client(['timeout' => 20]);
    }
}
