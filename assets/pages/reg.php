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
    <title>Регистрация</title>
    <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/reg.css">
    <script src="../js/reg.js"></script>
</head>
<body>
    <div class="user-form">
        <h1 class="form-header">Регистрация</h1>
        <form action="../php/register.php" method="post" onsubmit="return checkPass()">

            <div class="form-group">
                <input type="text" maxlength="30" id="username" name="username" placeholder="Имя пользователя" required>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Электронная почта" required>
            </div>

            <div class="form-group">
                <input type="password" maxlength="30" id="password" name="password" placeholder="Пароль" required>
                <button type="button" class="password-control" onclick="return togglePasswordVisibility(this, 'password');"></button>
            </div>

            <div class="form-group">
                <input type="password" maxlength="30" id="confirm-password" name="confirm-password" placeholder="Подтвердите пароль" required>
                <button type="button" class="password-control" onclick="return togglePasswordVisibility(this, 'confirm-password');"></button>
            </div>

            <div class="auth-link">
                Уже есть аккаунт? <a href="auth.php">Войти</a>
            </div>
            
            <button type="submit" class="submit-btn">Подтвердить</button>
        </form>

    </div>
</body>
</html>