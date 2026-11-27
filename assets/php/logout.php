<?php
session_start();
// Очищаем все переменные сессии
$_SESSION = array();
// Уничтожаем саму сессию
session_destroy();
// Перенаправляем на страницу входа
header("Location: ../pages/auth.php");
exit;
?>