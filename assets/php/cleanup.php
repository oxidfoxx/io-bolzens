<?php
/**
 * Скрипт очистки БД. Запускается по Cron 1 раз в сутки.
 */
// Защита от запуска через браузер (разрешаем только из CLI)
if (php_sapi_name() !== 'cli') {
    die('Доступ запрещен');
}

require_once __DIR__ . '/db.php';

// 1. Удаляем просроченные коды подтверждения почты
$stmt1 = $pdo->exec("UPDATE users SET verification_code = NULL, code_expires_at = NULL 
                     WHERE code_expires_at IS NOT NULL AND code_expires_at < NOW()");

// 2. Удаляем неоплаченные заказы старше 24 часов
$stmt2 = $pdo->exec("DELETE FROM orders WHERE is_paid = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

// Логируем результат
echo "[" . date('Y-m-d H:i:s') . "] Очищено кодов: {$stmt1}, удалено неоплаченных заказов: {$stmt2}\n";