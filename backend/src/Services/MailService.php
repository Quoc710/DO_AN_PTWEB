<?php

namespace App\Services;

use App\Config\Env;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use Throwable;

class MailService
{
    /**
     * Gửi email kích hoạt tài khoản
     *
     * @param string $toEmail Địa chỉ người nhận
     * @param string $fullName Tên người nhận
     * @param string $token Token kích hoạt
     * @return array [success => bool, message => string, link => string]
     */
    public function sendActivationEmail(string $toEmail, string $fullName, string $token): array
    {
        $appUrl = rtrim(Env::get('APP_URL', 'http://localhost:8000'), '/');
        $activationLink = "{$appUrl}/api/tai-khoan/verify-email?token={$token}";

        $subject = "Kích hoạt tài khoản AURA MINIMAL của bạn";
        $htmlBody = $this->renderActivationTemplate($fullName, $activationLink);

        $smtpHost = Env::get('MAIL_HOST', 'smtp.gmail.com');
        $smtpPort = (int)Env::get('MAIL_PORT', '587');
        $smtpUser = Env::get('MAIL_USER', '');
        $smtpPass = Env::get('MAIL_PASS', '');
        $fromEmail = trim((string)Env::get('MAIL_FROM', ''));
        if ($fromEmail === '') {
            $fromEmail = $smtpUser ?: 'noreply@auraminimal.vn';
        }
        $fromName = Env::get('MAIL_FROM_NAME', 'AURA MINIMAL');

        // Nếu đã cấu hình tài khoản SMTP -> Gửi email thật qua PHPMailer
        if (!empty($smtpUser) && !empty($smtpPass)) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
                $mail->SMTPSecure = $smtpPort === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtpPort;
                $mail->Timeout = 10;
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];

                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($toEmail, $fullName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = "Xin chào {$fullName},\n\nVui lòng truy cập liên kết sau để kích hoạt tài khoản của bạn:\n{$activationLink}\n\nLiên kết có hiệu lực trong 24 giờ.";

                $mail->send();

                return [
                    'success' => true,
                    'sent_real' => true,
                    'message' => 'Email kích hoạt đã được gửi tới ' . $toEmail,
                    'link' => $activationLink,
                ];
            } catch (Throwable $e) {
                // Ghi nhận lỗi và fallback lưu file để tiện kiểm tra
                $smtpError = $e->getMessage();
                error_log("Lỗi gửi mail SMTP: " . $e->getMessage());
            }
        }

        // Chế độ thử nghiệm (khi chưa điền Gmail App Password vào .env)
        // Lưu lại bản HTML và trả link kích hoạt để tiện test ngay lập tức
        $logDir = __DIR__ . '/../../storage/mail_logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        @file_put_contents("{$logDir}/activation_{$token}.html", $htmlBody);

        return [
            'success' => true,
            'sent_real' => false,
            'smtp_error' => $smtpError ?? null,
            'message' => 'Đã tạo liên kết kích hoạt (Chế độ môi trường phát triển).',
            'link' => $activationLink,
        ];
    }

    private function renderActivationTemplate(string $name, string $link): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Kích hoạt tài khoản AURA MINIMAL</title>
</head>
<body style="margin: 0; padding: 30px; background-color: #f6f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1a1a28;">
  <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e1f4;">
    <tr>
      <td style="padding: 32px 32px 20px; border-bottom: 1px solid #f0edf8;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #000000; margin-right: 8px; vertical-align: middle;"></span>
              <strong style="font-size: 13px; letter-spacing: 0.18em; text-transform: uppercase; color: #000000; vertical-align: middle;">AURA MINIMAL</strong>
            </td>
            <td align="right" style="font-size: 11px; color: #78767c; font-family: monospace;">
              XÁC THỰC TÀI KHOẢN
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="padding: 32px;">
        <h1 style="font-size: 20px; font-weight: 600; margin: 0 0 12px; color: #1a1a28;">Xác thực tài khoản nhân viên</h1>
        <p style="font-size: 14px; line-height: 1.6; color: #47464b; margin: 0 0 20px;">
          Xin chào <strong>{$safeName}</strong>,<br>
          Bạn vừa đăng ký tài khoản nhân viên tại hệ thống <strong>AURA MINIMAL</strong>. Để hoàn tất quá trình và kích hoạt tài khoản của bạn, vui lòng bấm vào nút bên dưới:
        </p>
        <div style="text-align: center; margin: 30px 0;">
          <a href="{$safeLink}" style="display: inline-block; padding: 14px 28px; background-color: #000000; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 0.02em;">Kích hoạt tài khoản ngay &rarr;</a>
        </div>
        <p style="font-size: 12px; color: #78767c; line-height: 1.5; margin: 20px 0 0;">
          * Liên kết kích hoạt này có hiệu lực trong <strong>24 giờ</strong>.<br>
          Nếu nút bấm trên không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:<br>
          <a href="{$safeLink}" style="color: #000000; word-break: break-all;">{$safeLink}</a>
        </p>
      </td>
    </tr>
    <tr>
      <td style="padding: 20px 32px; background-color: #fbf8ff; border-top: 1px solid #f0edf8; text-align: center; font-size: 11px; color: #9c9aa2;">
        Đây là thư tự động từ Hệ Thống Quản Lý Cửa Hàng Vải AURA MINIMAL. Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email.
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
