<?php
// php/turnstile.php
require_once __DIR__ . '/config.php';

/**
 * Проверяет токен Cloudflare Turnstile.
 *
 * @param string $token Значение поля cf-turnstile-response из формы
 * @return bool true — проверка пройдена, false — нет
 */
function verifyTurnstile($token) {
    if (empty($token)) {
        return false;
    }

    $secretKey = defined('TURNSTILE_SECRET') ? TURNSTILE_SECRET : '';
    if (empty($secretKey)) {
        error_log('Turnstile: не задан TURNSTILE_SECRET в config.php');
        return false;
    }

    $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    $postData = http_build_query(array(
        'secret'   => $secretKey,
        'response' => $token,
        'remoteip' => $remoteIp
    ));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('Turnstile curl error: ' . $curlError);
        return false;
    }

    $result = json_decode($response, true);
    if (!is_array($result)) {
        error_log('Turnstile: некорректный JSON от Cloudflare: ' . $response);
        return false;
    }

    return isset($result['success']) && $result['success'] === true;
}
?>