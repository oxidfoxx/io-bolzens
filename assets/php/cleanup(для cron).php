<?php
/**
 * Скрипт очистки БД. Запускается по Cron 1 раз в сутки.
 */
require_once 'db.php';

// 1. Удаляем просроченные коды подтверждения почты
$stmt1 = $pdo->exec("UPDATE users SET verification_code = NULL, code_expires_at = NULL 
                     WHERE code_expires_at IS NOT NULL AND code_expires_at < NOW()");

// 2. (Опционально) Удаляем неоплаченные заказы старше 24 часов
// Если пользователь нажал "Оформить", но ушел со страницы оплаты
$stmt2 = $pdo->exec("DELETE FROM orders WHERE is_paid = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

// Логируем результат (опционально)
echo "Cleaned up: " . date('Y-m-d H:i:s') . "\n";