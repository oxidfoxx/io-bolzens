<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/reg.css">
    <script src="../js/reg.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
    <div class="user-form">
        <h1 class="form-header">Авторизация</h1>
        <form action="../php/login.php" method="post">

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Электронная почта" required>
            </div>

            <div class="form-group">
                <input type="password" maxlength="30" id="password" name="password" placeholder="Пароль" required>
                <button type="button" class="password-control" onclick="return togglePasswordVisibility(this, 'password');"></button>
            </div>

            <div class="form-group">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAFQ6RaiRSq4sseDl" data-theme="light" data-size="flexible"></div>
            </div>

            <div class="reg-link">
                Нет аккаунта? <a href="reg.php">Зарегистрироваться</a>
            </div>
            
            <button type="submit" class="submit-btn">Войти</button>
        </form>
    </div>
</body>
</html>