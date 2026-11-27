<?php
function verifyTurnstile($token) {
    $secretKey = '0x4AAAAAAFQ6RQ_uHoaunkwsW8ea6ZloSt4'; // <-- Замените на ваш Secret Key из Cloudflare
    
    if (empty($token)) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    )));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) return false;

    $result = json_decode($response, true);
    return isset($result['success']) && $result['success'] === true;
}
?>