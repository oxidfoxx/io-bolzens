<?php
session_start();
require_once '../php/db.php'; 

if (!isset($_SESSION['verify_email'])) {
    header("Location: reg.php");
    exit;
}

$email = $_SESSION['verify_email'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = isset($_POST['code']) ? trim($_POST['code']) : '';

    if (!empty($user_code)) {
        // Условие 'code_expires_at > NOW()' гарантирует, что код не действует вечно!
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_email = ? AND verification_code = ? AND code_expires_at > NOW()");
        $stmt->execute([$email, $user_code]);
        $user = $stmt->fetch();

        if ($user) {
            // Активируем аккаунт, полностью стираем временный код и время из БД
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, code_expires_at = NULL WHERE user_id = ?");
            $update->execute([$user['user_id']]);

            // Авторизуем пользователя
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name']; // Для правильного отображения в профиле
            
            unset($_SESSION['verify_email']);

            header("Location: profile.php");
            exit;
        } else {
            // Сюда пользователь попадет, если код неверный ИЛИ если 15 минут уже истекли
            $error = "Неверный код или срок его действия (15 мин) истек.";
        }
    } else {
        $error = "Пожалуйста, введите код.";
    }
}
?>

<!DOCTYPE HTML>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение регистрации</title>
    <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/reg.css"> 
</head>
<body>
<div class="user-form">
    <h1 class="form-header">Подтверждение<br>почты</h1>
    <p class="form-group">
        Мы отправили 6-значный код на почту:<br>
        <strong><?php echo htmlspecialchars($email); ?></strong>
    </p>
    
    <?php if(!empty($error)): ?>
        <p><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="verify.php" method="post">
        <div class="form-group">
            <input type="text" name="code" maxlength="6" placeholder="000000" required autocomplete="off">
        </div>
        <button type="submit" class="submit-btn">
            Подтвердить
        </button>
    </form>
</div>
</body>
</html>
