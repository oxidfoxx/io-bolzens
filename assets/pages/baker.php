<?php
//date_default_timezone_set('Asia/Irkutsk');
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_baker'])) {
    header('Location: auth.php'); exit;
}
require_once '../php/db.php';

// === СКЛАД: Обновление ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        $updateStmt = $pdo->prepare("UPDATE stock SET quantity = ? WHERE product_id = ?");
        foreach ($_POST['quantity'] as $productId => $qty) {
            $qty = max(0, (int)$qty);
            $updateStmt->execute(array($qty, (int)$productId));
        }
    }
    header('Location: baker.php?tab=stock'); exit;
}

// === ВЗЯТЬ В ГОТОВКУ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'take_order') {
    $orderId = (int)$_POST['order_id'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cooking' WHERE order_id = ? AND status = 'В обработке'");
    $stmt->execute(array($orderId));
    header('Location: baker.php?tab=orders'); exit;
}

// === ПОДТВЕРДИТЬ ГОТОВНОСТЬ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_ready') {
    $orderId = (int)$_POST['order_id'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Готов к выдаче' WHERE order_id = ? AND status = 'cooking'");
    $stmt->execute(array($orderId));
    header('Location: baker.php?tab=orders'); exit;
}

// === ОТМЕНА ЗАКАЗА ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $orderId = (int)$_POST['order_id'];
    
    // 1. Получаем ID пользователя, который сделал заказ
    $stmtUser = $pdo->prepare("SELECT user_id FROM orders WHERE order_id = ?");
    $stmtUser->execute(array($orderId));
    $orderData = $stmtUser->fetch();
    
    if ($orderData) {
        $userId = $orderData['user_id'];
        
        // 2. Узнаем, сколько акционного кофе было в этом заказе (считаем только платные)
        $promo = $pdo->query("SELECT * FROM promo_settings WHERE id = 1")->fetch();
        if ($promo && $promo['is_active'] && $promo['product_id']) {
            $stmtItems = $pdo->prepare("SELECT SUM(quantity) as qty FROM order_items WHERE order_id = ? AND product_id = ? AND price > 0");
            $stmtItems->execute(array($orderId, $promo['product_id']));
            $bought = $stmtItems->fetch();
            
            if ($bought && $bought['qty'] > 0) {
                $minusQty = (int)$bought['qty'];
                // Вычитаем прогресс, но не даем уйти в минус (GREATEST(0, ...))
                $stmtProgress = $pdo->prepare("UPDATE users SET promo_progress = GREATEST(0, promo_progress - ?) WHERE user_id = ?");
                $stmtProgress->execute(array($minusQty, $userId));
            }
        }
    }

    // 3. Меняем статус и обнуляем код
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Отменён', pickup_code = NULL WHERE order_id = ?");
    $stmt->execute(array($orderId));
    
    header('Location: baker.php?tab=orders'); 
    exit;
}

// === ВЫДАЧА ПО КОДУ ===
$redeemMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'redeem') {
    $code = trim($_POST['pickup_code']);
    if ($code !== '') {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE pickup_code = ? AND status = 'Готов к выдаче'");
        $stmt->execute(array($code));
        $orderToRedeem = $stmt->fetch();
        
        if ($orderToRedeem) {
            $oid = $orderToRedeem['order_id'];
            
            $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItems->execute(array($oid));
            $itemsToRedeem = $stmtItems->fetchAll();
            
            $updStock = $pdo->prepare("UPDATE stock SET quantity = GREATEST(0, quantity - ?) WHERE product_id = ?");
            foreach ($itemsToRedeem as $it) {
                $updStock->execute(array($it['quantity'], $it['product_id']));
            }

            $update = $pdo->prepare("UPDATE orders SET status = 'Выдан', pickup_code = NULL WHERE order_id = ?");
            $update->execute(array($oid));
            
            $redeemMessage = '✅ Заказ №' . $oid . ' выдан!';
        } else {
            $redeemMessage = '❌ Код не найден или заказ не готов.';
        }
    }
}

// === СОХРАНЕНИЕ НАСТРОЕК АКЦИИ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_promo') {
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : NULL;
    
    $stmt = $pdo->prepare("UPDATE promo_settings SET is_active = ?, product_id = ? WHERE id = 1");
    $stmt->execute(array($isActive, $productId));
    header('Location: baker.php?tab=promo'); exit;
}

// === ВЫБОРКА ===
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

$orders = $pdo->query("
    SELECT o.*, u.user_name, u.user_email 
    FROM orders o JOIN users u ON o.user_id = u.user_id
    WHERE o.is_paid = 1 AND o.status IN ('В обработке', 'cooking', 'Готов к выдаче')
    ORDER BY o.pickup_time ASC
")->fetchAll();

$orderItemsMap = array();
if (!empty($orders)) {
    $ids = array();
    foreach ($orders as $o) { $ids[] = $o['order_id']; }
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.title, s.quantity AS stock_qty
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN stock s ON s.product_id = oi.product_id
        WHERE oi.order_id IN ($in)
    ");
    $stmtItems->execute($ids);
    $itemsResult = $stmtItems->fetchAll();
    foreach ($itemsResult as $it) $orderItemsMap[$it['order_id']][] = $it;
}

$stockItems = $pdo->query("
    SELECT s.*, p.title, c.title AS category_title 
    FROM stock s 
    JOIN products p ON s.product_id = p.product_id 
    JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.category_id, p.title
")->fetchAll();

$groupedStock = array();
foreach ($stockItems as $item) {
    $groupedStock[$item['category_title']][] = $item;
}

// Получаем список напитков для акции (предполагаем, что slug категории = 'drinks')
$drinksList = $pdo->query("
    SELECT p.* FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    WHERE c.slug = 'drinks' 
    ORDER BY p.title
")->fetchAll();

// Получаем текущие настройки акции
$promoSettings = $pdo->query("SELECT * FROM promo_settings WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель пекаря</title>
    <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
    <style>
        body { background: #F5EFE7; font-family: 'Montserrat Alternates', sans-serif; margin: 0; padding: 20px; color: #333; }
        .baker-layout { display: flex; gap: 20px; max-width: 1400px; margin: 0 auto; align-items: flex-start; }
        
        /* ЛЕВАЯ ЧАСТЬ */
        .main-col { flex: 1; min-width: 0; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #FFF; padding: 20px 30px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header h1 { margin: 0; font-size: 24px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tabs a { padding: 12px 25px; background: #EBEBEB; text-decoration: none; border-radius: 10px; color: #000; font-weight: 500; transition: 0.2s; }
        .tabs a:hover { background: #DDD; }
        .tabs a.active { background: #773A25; color: #FFF; }
        
        .section { background: #FFF; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .section h2 { margin-top: 0; font-size: 20px; margin-bottom: 20px; }

        /* КАРТОЧКИ ЗАКАЗОВ */
        .order-card { border: 1px solid #EEE; padding: 15px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; gap: 20px; align-items: flex-start; }
        .order-card.status-ready { border-left: 5px solid #5CB85C; background: #f9fff9; }
        .order-card.status-cooking { border-left: 5px solid #5BC0DE; background: #f4fbfd; }
        .order-info { width: 180px; flex-shrink: 0; }
        .order-num { font-size: 18px; font-weight: bold; }
        .order-time { color: #773A25; font-weight: 500; margin-top: 5px; font-size: 14px; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; color: #FFF; font-size: 12px; margin-top: 8px; font-weight: 500; }
        
        .status-badge.В-обработке { background: #F0AD4E; }
        .status-badge.cooking { background: #5BC0DE; }
        .status-badge.Готов-к-выдаче { background: #5CB85C; }
        
        .order-items { flex: 1; }
        .items-list { list-style: none; padding: 0; margin: 0 0 10px 0; }
        .items-list li { padding: 4px 0; font-size: 15px; display: flex; justify-content: space-between; border-bottom: 1px dashed #EEE; }
        .items-list li:last-child { border-bottom: none; }
        .items-list li.enough { color: #28a745; }
        .items-list li.not-enough { color: #dc3545; font-weight: 500; }
        .order-actions { display: flex; flex-direction: column; gap: 8px; width: 160px; flex-shrink: 0; }

        /* КНОПКИ */
        .btn { padding: 10px 15px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; color: #FFF; background: #773A25; text-align: center; text-decoration: none; transition: 0.2s; }
        .btn:hover { background: #5c2c1c; }
        .btn.green { background: #5CB85C; } .btn.green:hover { background: #4cae4c; }
        .btn.red { background: #D9534F; } .btn.red:hover { background: #c9302c; }
        .btn.full { width: 100%; box-sizing: border-box; }

        /* ПРАВАЯ ЧАСТЬ */
        .side-col { width: 350px; flex-shrink: 0; position: sticky; top: 20px; }
        .redeem-panel { background: #773A25; color: #FFF; padding: 30px; border-radius: 15px; box-shadow: 0 8px 24px rgba(119, 58, 37, 0.2); text-align: center; }
        .redeem-panel h2 { margin-top: 0; font-size: 22px; color: #FFF; margin-bottom: 20px; }
        .redeem-input { width: 100%; box-sizing: border-box; font-size: 32px; padding: 15px; text-align: center; letter-spacing: 8px; border: none; border-radius: 12px; margin-bottom: 15px; font-family: monospace; outline: none; }
        .redeem-panel .btn { font-size: 18px; padding: 15px; background: #5CB85C; }
        .redeem-panel .btn:hover { background: #4cae4c; }
        .redeem-msg { margin-top: 20px; padding: 15px; border-radius: 10px; background: rgba(255,255,255,0.1); font-size: 15px; }

        /* СКЛАД */
        .stock-header-action { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .stock-category { margin-bottom: 25px; }
        .stock-category h3 { font-size: 16px; color: #773A25; border-bottom: 2px solid #F5EFE7; padding-bottom: 8px; margin-bottom: 15px; }
        .stock-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 15px; }
        .stock-item { display: flex; align-items: center; justify-content: space-between; background: #FAFAFA; padding: 10px 15px; border-radius: 10px; border: 1px solid #EEE; }
        .stock-title { font-size: 14px; flex: 1; padding-right: 10px; }
        .stock-controls { display: flex; align-items: center; gap: 5px; }
        .stock-input { width: 50px; padding: 6px; font-size: 16px; text-align: center; border: 1px solid #CCC; border-radius: 6px; font-weight: bold; -moz-appearance: textfield; }
        .stock-input::-webkit-outer-spin-button, .stock-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .qty-btn { width: 30px; height: 30px; border-radius: 6px; border: none; background: #EBEBEB; color: #333; font-size: 20px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .qty-btn:hover { background: #DEDEDE; }
        .qty-btn:active { background: #CCC; }

        /* КАСТОМНАЯ МОДАЛКА */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(3px); }
        .modal-overlay.active { display: flex; }
        .modal-box { background: #FFF; padding: 30px 40px; border-radius: 20px; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: pop 0.3s ease; }
        @keyframes pop { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .modal-box h3 { margin-top: 0; font-size: 22px; color: #333; }
        .modal-box p { color: #666; margin-bottom: 25px; font-size: 16px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-actions .btn { min-width: 120px; }
    </style>
</head>
<body>

<div class="baker-layout">
    <div class="main-col">
        <div class="header">
            <h1>Пекарь: <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
            <a href="../php/logout.php" style="color: #773A25; text-decoration: none; font-weight: 500;">Выйти</a>
        </div>

        <div class="tabs">
            <a href="?tab=orders" class="<?php echo $tab == 'orders' ? 'active' : ''; ?>">📦 Заказы</a>
            <a href="?tab=stock" class="<?php echo $tab == 'stock' ? 'active' : ''; ?>">📊 Склад</a>
            <a href="?tab=promo" class="<?php echo $tab == 'promo' ? 'active' : ''; ?>">🎁 Акция</a>
        </div>

        <?php if ($tab == 'orders'): ?>
            <div class="section">
                <h2>Активные заказы</h2>
                <div id="ordersList">
                    <?php if (empty($orders)): ?>
                        <p style="color: #999;">Заказов нет.</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): 
                            $displayStatus = $order['status'];
                            if ($order['status'] === 'В обработке') $displayStatus = 'Ожидает';
                            if ($order['status'] === 'cooking') $displayStatus = 'Готовится';
                            if ($order['status'] === 'Готов к выдаче') $displayStatus = 'Готов';

                            $pickup = strtotime($order['pickup_time']);
                            $diff = round(($pickup - time()) / 60);
                            $timeText = $diff > 0 ? "Через $diff мин. (" . date('H:i', $pickup) . ")" : "Просрочен";
                            $statusClass = str_replace(' ', '-', $order['status']);
                        ?>
                            <div class="order-card status-<?php echo $order['status'] == 'cooking' ? 'cooking' : ($order['status'] == 'Готов к выдаче' ? 'ready' : ''); ?>">
                                <div class="order-info">
                                    <div class="order-num">Заказ №<?php echo $order['order_id']; ?></div>
                                    <div class="order-time">🕒 <?php echo $timeText; ?></div>
                                    <div class="status-badge <?php echo $statusClass; ?>"><?php echo $displayStatus; ?></div>
                                </div>
                                
                                <div class="order-items">
                                    <ul class="items-list">
                                        <?php foreach ($orderItemsMap[$order['order_id']] as $item): 
                                            $stockQty = isset($item['stock_qty']) ? $item['stock_qty'] : 0;
                                            $enough = $stockQty >= $item['quantity'];
                                        ?>
                                            <li class="<?php echo $enough ? 'enough' : 'not-enough'; ?>">
                                                <span><?php echo $item['title']; ?> × <?php echo $item['quantity']; ?></span>
                                                <span style="font-size: 13px;">(Склад: <?php echo $stockQty; ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="order-actions">
                                    <?php if ($order['status'] == 'В обработке'): ?>
                                        <form method="POST" class="action-form" data-confirm="Взять заказ в готовку?">
                                            <input type="hidden" name="action" value="take_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button class="btn full">Взять в готовку</button>
                                        </form>
                                    <?php elseif ($order['status'] == 'cooking'): ?>
                                        <form method="POST" class="action-form" data-confirm="Подтвердить, что заказ готов?">
                                            <input type="hidden" name="action" value="mark_ready">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button class="btn green full">Готов к выдаче</button>
                                        </form>
                                    <?php elseif ($order['status'] == 'Готов к выдаче'): ?>
                                        <div style="color: #5CB85C; font-weight: 500; text-align: center; font-size: 14px; margin-bottom: 5px;">Ждём клиента</div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="action-form" data-confirm="Вы уверены, что хотите отменить заказ?">
                                        <input type="hidden" name="action" value="cancel_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button class="btn full" style="background: transparent; color: #D9534F; border: 1px solid #D9534F;">Отменить</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($tab == 'stock'): ?>
            <div class="section">
                <h2>Наличие товаров</h2>
                <form method="POST" id="stockForm">
                    <input type="hidden" name="action" value="update_stock">
                    <div class="stock-header-action">
                        <button type="submit" class="btn green" style="font-size: 16px; padding: 12px 25px;">💾 Сохранить всё</button>
                    </div>

                    <?php foreach ($groupedStock as $categoryTitle => $items): ?>
                        <div class="stock-category">
                            <h3><?php echo htmlspecialchars($categoryTitle); ?></h3>
                            <div class="stock-grid">
                                <?php foreach ($items as $item): ?>
                                    <div class="stock-item">
                                        <div class="stock-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="stock-controls">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, -1)">−</button>
                                            <input type="number" 
                                                   name="quantity[<?php echo $item['product_id']; ?>]" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="0" 
                                                   class="stock-input">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>

        <?php elseif ($tab == 'promo'): ?>
            <div class="section">
                <h2>Настройки акции «5+1»</h2>
                <form method="POST" style="max-width: 500px;">
                    <input type="hidden" name="action" value="update_promo">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 16px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?php echo (isset($promoSettings['is_active']) && $promoSettings['is_active']) ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                            Включить акцию в профиле пользователей
                        </label>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: 500;">Выберите напиток для акции:</label>
                        <select name="product_id" style="width: 100%; padding: 12px; font-size: 16px; border-radius: 8px; border: 1px solid #CCC;">
                            <option value="">-- Не выбран --</option>
                            <?php foreach ($drinksList as $drink): ?>
                                <option value="<?php echo $drink['product_id']; ?>" <?php echo (isset($promoSettings['product_id']) && $promoSettings['product_id'] == $drink['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($drink['title']); ?> (<?php echo $drink['price']; ?> ₽)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn green" style="font-size: 16px; padding: 12px 25px;">💾 Сохранить настройки</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="side-col">
        <div class="redeem-panel">
            <h2>🔑 Выдача заказа</h2>
            <p style="font-size: 14px; opacity: 0.8; margin-bottom: 15px;">Введите 6 цифр, чтобы списать и выдать заказ</p>
            <form method="POST">
                <input type="hidden" name="action" value="redeem">
                <input type="text" name="pickup_code" maxlength="6" pattern="\d{6}" placeholder="000000" required autocomplete="off" class="redeem-input">
                <button type="submit" class="btn full">Подтвердить выдачу</button>
            </form>
            <?php if ($redeemMessage): ?>
                <div class="redeem-msg"><?php echo $redeemMessage; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- КАСТОМНАЯ МОДАЛКА ПОДТВЕРЖДЕНИЯ -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <h3>Подтверждение</h3>
        <p id="confirmText">Вы уверены?</p>
        <div class="modal-actions">
            <button class="btn" style="background: #EBEBEB; color: #333;" onclick="closeModal()">Отмена</button>
            <button class="btn red" id="confirmBtn">Да, подтвердить</button>
        </div>
    </div>
</div>

<script>
    if (window.location.search.includes('tab=orders') || !window.location.search) {
        setInterval(() => {
            if (document.activeElement && document.activeElement.classList.contains('redeem-input')) return;

            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newList = doc.getElementById('ordersList');
                    if (newList) document.getElementById('ordersList').innerHTML = newList.innerHTML;
                    bindForms();
                });
        }, 15000); 
    }

    function changeQty(btn, delta) {
        const input = btn.parentElement.querySelector('.stock-input');
        let val = parseInt(input.value) || 0;
        val += delta;
        if (val < 0) val = 0;
        input.value = val;
    }

    let currentFormToSubmit = null;

    function openModal(text, form) {
        document.getElementById('confirmText').textContent = text;
        document.getElementById('confirmModal').classList.add('active');
        currentFormToSubmit = form;
    }

    function closeModal() {
        document.getElementById('confirmModal').classList.remove('active');
        currentFormToSubmit = null;
    }

    document.getElementById('confirmBtn').addEventListener('click', () => {
        if (currentFormToSubmit) {
            currentFormToSubmit.submit();
        }
        closeModal();
    });

    function bindForms() {
        document.querySelectorAll('.action-form').forEach(form => {
            form.onsubmit = null;
            form.onsubmit = function(e) {
                e.preventDefault();
                const text = this.dataset.confirm || 'Вы уверены?';
                openModal(text, this);
            };
        });
    }
    
    bindForms();
</script>
</body>
</html>