<?php
function emailVerificationTemplate($code, $userName = '') {
    $safeName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
    $greeting = $userName
        ? "Здравствуйте, <strong>{$safeName}</strong>!"
        : "Здравствуйте!";

    return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Подтверждение регистрации</title>
</head>
<body style="margin:0;padding:0;background:#F5EFE7;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#F5EFE7;padding:40px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellspacing="0" cellpadding="0" border="0" style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
          <tr>
            <td style="background:#773A25;padding:28px 40px;text-align:center;">
              <h1 style="margin:0;color:#FFFFFF;font-size:22px;letter-spacing:1px;">🥐 Golden Croissant</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:36px 40px 12px;">
              <h2 style="margin:0 0 12px;color:#333333;font-size:20px;">Подтверждение регистрации</h2>
              <p style="margin:0 0 20px;color:#555555;font-size:15px;line-height:1.6;">
                {$greeting}<br>
                Спасибо за регистрацию в нашей булочной. Чтобы активировать аккаунт, введите код ниже на странице подтверждения.
              </p>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:8px 40px 24px;">
              <div style="display:inline-block;background:#F5EFE7;border:2px dashed #773A25;border-radius:12px;padding:18px 36px;">
                <span style="font-family:'Courier New',monospace;font-size:34px;font-weight:bold;letter-spacing:10px;color:#773A25;">
                  {$code}
                </span>
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:0 40px 32px;">
              <p style="margin:0 0 8px;color:#777777;font-size:13px;line-height:1.6;">
                ⏱ Код действителен в течение <strong>15 минут</strong>.
              </p>
              <p style="margin:0;color:#777777;font-size:13px;line-height:1.6;">
                Если вы не регистрировались на нашем сайте — просто проигнорируйте это письмо.
              </p>
            </td>
          </tr>
          <tr>
            <td style="background:#FAFAFA;padding:20px 40px;text-align:center;border-top:1px solid #EEEEEE;">
              <p style="margin:0;color:#999999;font-size:12px;">
                © Golden Croissant — булочная с историей<br>
                г. Иркутск, ул. Баумана, 233Б
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
?>