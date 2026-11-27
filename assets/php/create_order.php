<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/auth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['selected_items'])) {
    header('Location: ../pages/cart.php');
    exit;
}

require_once 'db.php';

$userId = (int)$_SESSION['user_id'];
$selectedIds = array_filter(array_map('intval', $_POST['selected_items']));

if (empty($selectedIds)) {
    header('Location: ../pages/cart.php');
    exit;
}

// 1. Получаем количества, которые пользователь указал в модалке
$quantities = array();
if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($qty < 1) $qty = 1;
        if ($qty > 99) $qty = 99;
        $quantities[$pid] = $qty;
    }
}

// 2. Получаем актуальные цены И остатки на складе
$placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
$stmt = $pdo->prepare("
    SELECT p.product_id, p.price, s.quantity AS stock_qty 
    FROM products p 
    LEFT JOIN stock s ON s.product_id = p.product_id 
    WHERE p.product_id IN ($placeholders)
");
$stmt->execute($selectedIds);
$products = $stmt->fetchAll();

$productPrices = array();
$stockAmounts = array();

foreach ($products as $p) {
    $productPrices[$p['product_id']] = $p['price'];
    // Если записи на складе нет, считаем что 0
    $stockAmounts[$p['product_id']] = isset($p['stock_qty']) ? (int)$p['stock_qty'] : 0;
}

// 3. ПРОВЕРКА СКЛАДА (Вот тут мы отсекаем заказы, если товара нет)
foreach ($selectedIds as $pid) {
    // Если товара нет в базе (удалили) - пропускаем
    if (!isset($productPrices[$pid])) continue;

    // Получаем нужное количество из формы или из сессии
    if (isset($quantities[$pid])) {
        $qty = $quantities[$pid];
    } elseif (isset($_SESSION['cart'][$pid])) {
        $qty = (int)$_SESSION['cart'][$pid];
    } else {
        continue; // товара нет ни в форме, ни в корзине
    }

    // Сверяем с остатком
    if ($stockAmounts[$pid] < $qty) {
        // Если не хватает — выкидываем обратно в корзину с ошибкой
        header('Location: ../pages/cart.php?error=out_of_stock');
        exit;
    }
}

// 4. Считаем итог и собираем позиции
$total = 0;
$orderItems = array();

foreach ($selectedIds as $pid) {
    if (!isset($productPrices[$pid])) continue;

    if (isset($quantities[$pid])) {
        $qty = $quantities[$pid];
    } elseif (isset($_SESSION['cart'][$pid])) {
        $qty = (int)$_SESSION['cart'][$pid];
    } else {
        continue;
    }

    // Проверяем, не бесплатный ли это товар
    $isFree = isset($_POST['is_free'][$pid]) && $_POST['is_free'][$pid] == '1';
    
    if ($isFree) {
        // ЖЕСТКАЯ ЗАЩИТА: Принудительно ставим 1 шт. и цену 0, 
        // игнорируя то, что пришло из формы
        $qty = 1;
        $price = 0;
    } else {
        // Для платных товаров берем цену из базы
        $price = $productPrices[$pid];
    }

    if ($qty < 1) continue;

    $total += $price * $qty;

    $orderItems[] = array(
        'product_id' => $pid,
        'quantity'   => $qty,
        'price'      => $price
    );
}

if (empty($orderItems)) {
    header('Location: ../pages/cart.php');
    exit;
}

// 5. Обработка времени (часы и минуты)
$pickupHour = isset($_POST['pickup_hour']) ? (int)$_POST['pickup_hour'] : 12;
$pickupMinute = isset($_POST['pickup_minute']) ? (int)$_POST['pickup_minute'] : 0;

//date_default_timezone_set('Asia/Irkutsk');
$pickupDate = date('Y-m-d');
$pickupTimeString = sprintf('%s %02d:%02d:00', $pickupDate, $pickupHour, $pickupMinute);

// Если выбранное время уже прошло, отменяем заказ и кидаем обратно в корзину
if (strtotime($pickupTimeString) < time()) {
    header('Location: ../pages/cart.php?error=past_time');
    exit;
}

// 6. Генерация уникального кода
function generatePickupCode($pdo) {
    for ($i = 0; $i < 20; $i++) {
        $code = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $check = $pdo->prepare("SELECT order_id FROM orders WHERE pickup_code = ?");
        $check->execute(array($code));
        if (!$check->fetch()) return $code;
    }
    return substr((string)time(), -6);
}
$pickupCode = generatePickupCode($pdo);

// 7. Создаем заказ со статусом "Ожидает оплаты"
$stmt = $pdo->prepare("INSERT INTO orders (user_id, status, total, pickup_code, pickup_time, is_paid) VALUES (?, 'pending_payment', ?, ?, ?, 0)");
$stmt->execute(array($userId, $total, $pickupCode, $pickupTimeString));
$orderId = (int)$pdo->lastInsertId();

// 8. Добавляем позиции
$stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($orderItems as $item) {
    $stmtItem->execute(array($orderId, $item['product_id'], $item['quantity'], $item['price']));
}

// 9. Очищаем корзину от выбранных
foreach ($selectedIds as $pid) {
    unset($_SESSION['cart'][$pid]);
    
    // Если оформленный товар был тем самым бесплатным кофе — стираем метку
    if (isset($_SESSION['free_promo_id']) && $_SESSION['free_promo_id'] == $pid) {
        unset($_SESSION['free_promo_id']);
    }
}

// 10. Идем на оплату
$_SESSION['pending_order_id'] = $orderId;
header('Location: ../pages/payment.php');
exit;