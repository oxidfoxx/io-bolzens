<?php
//date_default_timezone_set('Asia/Irkutsk');
if (session_id() === '') {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Пекаря — на его панель
if (!empty($_SESSION['is_baker'])) {
    header('Location: baker.php');
    exit;
}

require_once '../php/db.php';

$base_path   = '../';
$home_path   = '../../';
$header_type = 'profile';

$userId = (int)$_SESSION['user_id'];

// Заказы пользователя
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND is_paid = 1 ORDER BY created_at DESC");
$stmt->execute(array($userId));
$orders = $stmt->fetchAll();

// Товары по каждому заказу
$orderItemsMap = array();
if (!empty($orders)) {
    $orderIds = array();
    foreach ($orders as $o) $orderIds[] = (int)$o['order_id'];
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.title, p.image_path 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id IN ($placeholders)
    ");
    $stmtItems->execute($orderIds);
    $allItems = $stmtItems->fetchAll();

    foreach ($allItems as $it) {
        $orderItemsMap[$it['order_id']][] = $it;
    }
}

// === РАСЧЕТ АКЦИИ ===
$promo = $pdo->query("SELECT * FROM promo_settings WHERE id = 1")->fetch();
$promoProgress = 0;
$promoProduct = null;
$hasFreeCoffee = false;

if ($promo && $promo['is_active'] && $promo['product_id']) {
    // Получаем инфу о кофе
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute(array($promo['product_id']));
    $promoProduct = $stmt->fetch();

    // Берем прогресс прямо из профиля пользователя
    $stmtUser = $pdo->prepare("SELECT promo_progress FROM users WHERE user_id = ?");
    $stmtUser->execute(array($userId));
    $userData = $stmtUser->fetch();
    
    $promoProgress = $userData ? (int)$userData['promo_progress'] : 0;

    // Если накопилось 5 или больше
    if ($promoProgress >= 5) {
        $hasFreeCoffee = true;
        $promoProgress = 5; // Ограничиваем визуально
    }
}

require_once '../includes/header.php';
?>

    <main class="container">
        <div class="section-container orders-list">

            <?php if ($promo && $promo['is_active'] && $promoProduct): ?>
                <div class="promo-block" style="background: #FFF; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin-top: 0; font-size: 22px; color: #773A25;">Акция «5+1»</h3>
                    <p style="color: #666; font-size: 15px;">Купите 5 <strong><?php echo htmlspecialchars($promoProduct['title']); ?></strong> и получите 6-й в подарок!</p>
                    
                    <div style="display: flex; justify-content: center; gap: 15px; margin: 20px 0;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div style="width: 50px; height: 60px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: <?php echo ($i <= $promoProgress) ? '#773A25' : '#EBEBEB'; ?>; transition: 0.3s;">
                                <!-- Иконка кофе (можно заменить на img) -->
                                <img src="<?php echo $base_path . $promoProduct['image_path']; ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; <?php echo ($i <= $promoProgress) ? '' : 'filter: grayscale(100%) opacity(0.3);'; ?>">
                            </div>
                        <?php endfor; ?>
                    </div>

                    <?php if ($hasFreeCoffee): ?>
                        <form method="POST" action="catalog.php">
                            <input type="hidden" name="action" value="redeem_free_coffee">
                            <input type="hidden" name="product_id" value="<?php echo $promoProduct['product_id']; ?>">
                            <button type="submit" class="main-btn-grey" style="background: #5CB85C; color: #FFF; border: none; font-weight: 500; font-size: 18px;">Забрать бесплатный кофе</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h3 class="primary-title">Мои заказы</h3>

            <?php if (empty($orders)): ?>
                <p class="primary-p">У вас пока нет заказов.</p>
                <a href="catalog.php" class="main-btn-grey">Перейти в меню</a>
            <?php else: ?>
                <div class="orders-list__cards-container">
                    <?php foreach ($orders as $order): ?>
                        <div class="orders-list__card"
                            data-order-id="<?php echo $order['order_id']; ?>"
                            data-pickup-code="<?php echo htmlspecialchars($order['pickup_code']); ?>">
                            <div class="orders-list__card-logo"></div>
                            <div class="orders-list__card-info">
                                <h4 class="orders-list__order-number">
                                    Заказ №<span><?php echo $order['order_id']; ?></span>
                                </h4>
                                <p class="orders-list__order-status"
                                data-status="<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </p>

                                <?php if ($order['pickup_code'] && $order['status'] !== 'Выдан'): ?>
                                    <div class="order-pickup-code" style="text-align: center; margin-top: 10px;">
                                        <span style="font-size: 14px; color: #777;">Код для выдачи:</span>
                                        <strong style="font-size: 24px; letter-spacing: 4px; font-family: monospace; display: block; margin: 5px 0;">
                                            <?php echo htmlspecialchars($order['pickup_code']); ?>
                                        </strong>
                                        <!-- ДОБАВИТЬ СТРОКУ НИЖЕ -->
                                        <span style="font-size: 14px; color: #773A25; font-weight: 500;">
                                            Забрать: <?php echo date('d.m в H:i', strtotime($order['pickup_time'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Модалка заказа -->
            <dialog class="orders-list__modal-window" id="modalProfileOrderWindow">
                <div class="order-list__modal-number-and-status">
                    <p class="order-list__modal-number">Заказ №<span></span></p>
                    <p class="order-list__modal-status" data-status=""></p>
                </div>

                <div class="order-list__modal-products-list" id="orderModalContent"></div>

                <button class="main-btn-white orders-list__modal-close-btn" id="closeProfileOrderModalBtn">Закрыть</button>
            </dialog>
        </div>
    </main>

    <?php
        require_once '../includes/footer.php';
    ?>