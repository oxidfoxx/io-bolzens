<?php
require_once 'db.php';
require_once 'turnstile.php';
require_once 'email_templates.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // === ПРОВЕРКА TURNSTILE ===
    $token = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';
    if (!verifyTurnstile($token)) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<script>alert('Проверка Cloudflare не пройдена. Попробуйте снова.'); window.history.back();</script>";
        exit;
    }

    $name  = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($name) || empty($email) || empty($pass)) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<script>alert('Заполните все поля'); window.history.back();</script>";
        exit;
    }

    // Проверка, не занят ли Email
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<script>
            alert('Пользователь с таким Email уже зарегистрирован!');
            window.location.href = '../pages/reg.php';
        </script>";
        exit;
    }
    
    try {
        // Хешируем пароль
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // 1. Генерация 6-значного кода и времени его жизни (15 минут)
        $code = (string)rand(100000, 900000);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 2. Подготовка и запись пользователя в БД со статусом is_verified = 0
        $stmt = $pdo->prepare("INSERT INTO users (user_name, user_email, user_password_hash, is_verified, verification_code, code_expires_at) VALUES (?, ?, ?, 0, ?, ?)");
        $stmt->execute([$name, $email, $hash, $code, $expires_at]);

        // 3. Отправка письма встроенной функцией mail()
        $to = $email;
        // Кодируем тему в UTF-8 — иначе кириллица в заголовке ломается
        $subject = "=?UTF-8?B?" . base64_encode("Код подтверждения регистрации") . "?=";
        $message = emailVerificationTemplate($code, $name);   // шаблон письма

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Golden Croissant <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
        $headers .= "Reply-To: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (!mail($to, $subject, $message, $headers)) {
            throw new Exception("Внутренняя ошибка сервера: не удалось отправить Email. Проверьте настройки почты.");
        }

        // 4. Запоминаем email в сессии для страницы верификации
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['verify_email'] = $email;

        // Перенаправляем на страницу ввода кода
        header("Location: ../pages/verify.php");
        exit;

    } catch (PDOException $e) {
        header('Content-Type: text/html; charset=utf-8');
        die("Системная ошибка при записи в базу данных.");
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=utf-8');
        die($e->getMessage());
    }
}
