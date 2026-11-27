<?php
session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['pending_order_id'])) {
    header('Location: profile.php');
    exit;
}

require_once '../php/db.php';

$orderId = (int)$_SESSION['pending_order_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute(array($orderId, $_SESSION['user_id']));
$order = $stmt->fetch();

if (!$order) {
    unset($_SESSION['pending_order_id']);
    header('Location: profile.php');
    exit;
}

// "Оплата"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    // 1. Меняем статус на "В обработке", чтобы заказ появился у пекаря
    $update = $pdo->prepare("UPDATE orders SET is_paid = 1, paid_at = NOW(), status = 'В обработке' WHERE order_id = ?");
    $update->execute(array($orderId));
    
    // 2. Начисляем прогресс по акции (если она включена)
    $promo = $pdo->query("SELECT * FROM promo_settings WHERE id = 1")->fetch();
    if ($promo && $promo['is_active'] && $promo['product_id']) {
        // Ищем в заказе купленный кофе (цена > 0, чтобы не считать бесплатный)
        $stmtItems = $pdo->prepare("SELECT SUM(quantity) as qty FROM order_items WHERE order_id = ? AND product_id = ? AND price > 0");
        $stmtItems->execute(array($orderId, $promo['product_id']));
        $bought = $stmtItems->fetch();
        
        if ($bought && $bought['qty'] > 0) {
            $addQty = (int)$bought['qty'];
            $stmtProgress = $pdo->prepare("UPDATE users SET promo_progress = promo_progress + ? WHERE user_id = ?");
            $stmtProgress->execute(array($addQty, $_SESSION['user_id']));
        }
    }

    unset($_SESSION['pending_order_id']);
    header('Location: profile.php?paid=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата заказа - Golden Croissant</title>
    <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/reg.css">
</head>
<body>
    <div class="user-form">
        <div class="form-group">
            <h1 class="form-header">Оплата</h1>
            <p class="auth-link">
                Заказ №<?php echo $order['order_id']; ?><br>
                <small style="font-size: 16px;">Готовность: <?php echo date('d.m в H:i', strtotime($order['pickup_time'])); ?></small>
            </p>
        </div>
        
        <div class="form-header" style="color: #773A25;">
            <?php echo number_format($order['total'], 0, '', ' '); ?> ₽
        </div>

        <form method="POST">
            <button class="submit-btn" type="submit" name="pay" value="1">Оплатить</button>
            
            <a class="auth-link" href="profile.php" style="margin-top: 15px; display: inline-block;">Отменить и вернуться в профиль</a>
        </form>
    </div>
</body>
</html>