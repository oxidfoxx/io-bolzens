<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($pass)) {
        die("Заполните все поля");
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['user_password_hash'])) {
        
        // 1. ПРОВЕРКА: Если почта НЕ подтверждена (равна 0)
        if ((int)$user['is_verified'] === 0) {
            
            // Запоминаем email в сессии, чтобы verify.php знал, для кого введен код
            $_SESSION['verify_email'] = $user['user_email'];

            // Проверяем, не истекло ли время действия старого кода
            $current_time = date('Y-m-d H:i:s');
            
            if ($user['code_expires_at'] === null || $current_time > $user['code_expires_at']) {
                // Код просрочен: генерируем новый и обновляем время действия на +15 минут
                $new_code = (string)rand(100000, 900000);
                $new_expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                $update_code = $pdo->prepare("UPDATE users SET verification_code = ?, code_expires_at = ? WHERE user_id = ?");
                $update_code->execute([$new_code, $new_expires_at, $user['user_id']]);

                // Отправляем новый код на чистом PHP
                $to = $user['user_email'];
                $subject = "Новый код подтверждения регистрации";
                $message = "
                <html>
                <head><title>Новый код подтверждения</title></head>
                <body>
                    <h2>Здравствуйте!</h2>
                    <p>Ваш предыдущий код верификации истек.</p>
                    <p>Новый код для подтверждения регистрации: <b style='font-size: 18px; color: #333;'>{$new_code}</b></p>
                    <p>Код действителен в течение 15 минут.</p>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";

                mail($to, $subject, $message, $headers);
                
                $msg = "Ваш аккаунт не подтвержден, а прошлый код истек. Новый код отправлен на почту!";
            } else {
                // Старый код еще действует (15 минут не прошло)
                $msg = "Ваш аккаунт не подтвержден! Введите код, который мы отправили вам ранее.";
            }

            // Выводим alert и перенаправляем на страницу ввода кода
            header('Content-Type: text/html; charset=utf-8');
            echo "<script>
                alert('{$msg}');
                window.location.href = '../pages/verify.php';
            </script>";
            exit;
        }

        // 2. ЕСЛИ ПОЧТА ПОДТВЕРЖДЕНА (is_verified == 1): Стандартный успешный вход
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['is_baker']  = isset($user['is_baker']) ? (int)$user['is_baker'] : 0;

        // Редирект: пекаря — на его панель, обычного — в профиль
        if ($_SESSION['is_baker'] === 1) {
            header("Location: ../pages/baker.php");
        } else {
            header("Location: ../pages/profile.php");
        }
        exit;
        
    } else {
        header('Content-Type: text/html; charset=utf-8');
        die("Неверный email или пароль");
    }
}