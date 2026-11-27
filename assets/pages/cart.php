<?php
if (session_id() === '') {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

if (!empty($_SESSION['is_baker'])) {
    header('Location: baker.php');
    exit;
}

require_once '../php/db.php';

$base_path   = '../';
$home_path   = '../../';
$header_type = 'cart';

// Обработка удаления
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    
    // Если удалили бесплатный кофе, сбрасываем метку, чтобы он не стал бесплатным в следующий раз
    if (isset($_SESSION['free_promo_id']) && $_SESSION['free_promo_id'] == $removeId) {
        unset($_SESSION['free_promo_id']);
    }
    
    header('Location: cart.php');
    exit;
}

// Собираем корзину из БД
$cartItems = array();

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Подтягиваем остатки со склада (stock)
    $stmt = $pdo->prepare("
        SELECT p.*, s.quantity AS stock_qty 
        FROM products p 
        LEFT JOIN stock s ON s.product_id = p.product_id 
        WHERE p.product_id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $prod) {
        $qty = (int)$_SESSION['cart'][$prod['product_id']];
        
        // Если в сессии есть метка, и ID товара совпадает — делаем его бесплатным
        if (isset($_SESSION['free_promo_id']) && $_SESSION['free_promo_id'] == $prod['product_id'] && $qty > 0) {
            $prod['price'] = 0;
            $qty = 1; // Бесплатный кофе всегда 1 шт.
        }

        // Вычисляем наличие
        $stockQty = isset($prod['stock_qty']) ? (int)$prod['stock_qty'] : 0;
        $prod['is_available'] = ($stockQty >= $qty);

        $prod['quantity'] = $qty;
        $cartItems[] = $prod;
    }
}

require_once '../includes/header.php';
?>

    <main class="container">
        <div class="section-container">

        <?php if (empty($cartItems)): ?>

            <p class="primary-p">
                Ваша корзина пуста.
            </p>
            <a href="catalog.php" class="main-btn-grey">Перейти в меню</a>

        <?php else: ?>

            <form id="cartProductList" class="cart-products-list" method="POST" action="../php/create_order.php">
                <?php foreach ($cartItems as $item): ?>
                    <?php 
                        // Если товара не хватает, добавляем класс для серого фона
                        $disabledClass = !$item['is_available'] ? 'cart-products-list__card--disabled' : '';
                    ?>
                    
                    <div class="cart-products-list__card <?php echo $disabledClass; ?>"
                        data-id="<?php echo $item['product_id']; ?>"
                        data-price="<?php echo $item['price']; ?>"
                        data-is-free="<?php echo ($item['price'] == 0) ? '1' : '0'; ?>">
                        
                        <!-- Блокируем чекбокс, если товара нет -->
                        <input type="checkbox"
                            name="selected_items[]"
                            value="<?php echo $item['product_id']; ?>"
                            class="cart-products-list__checkbox"
                            <?php echo $item['is_available'] ? 'checked' : 'disabled'; ?>>

                        <input type="hidden" name="is_free[<?php echo $item['product_id']; ?>]" value="<?php echo ($item['price'] == 0) ? '1' : '0'; ?>">

                        <input type="hidden" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>">

                        <img src="<?php echo $base_path . htmlspecialchars($item['image_path']); ?>" alt="">

                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>

                        <?php if (!$item['is_available']): ?>
                            <p style="color: red; font-size: 14px; font-weight: 500; margin-bottom: 5px;">
                                Нет в наличии
                            </p>
                        <?php endif; ?>

                        <div class="cart-products-list__card-price-and-btn">
                            <a href="?remove=<?php echo $item['product_id']; ?>"
                            class="main-btn-white cart-products-list__card-btn"
                            style="font-size: 14px;">Удалить</a>
                            <p><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>

            <!-- Установка часового пояса и вычисление текущего времени -->
            <?php
            //date_default_timezone_set('Asia/Irkutsk');
            $currentHour = (int)date('H');
            $currentMinute = (int)date('i');
            ?>

            <div style="margin-top: 30px; display: flex; gap: 20px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                <label style="font-size: 18px; font-weight: 500;">Время готовности:</label>
                
                <select name="pickup_hour" id="pickup_hour" form="cartProductList" required style="font-size: 16px; padding: 10px 15px; border-radius: 8px; border: 1px solid #CCC;">
                    <option value="">Часы</option>
                    <?php for ($h = 9; $h <= 19; $h++): 
                        $disabled = '';
                        // Блокируем прошедшие часы
                        if ($h < $currentHour) $disabled = 'disabled';
                    ?>
                        <option value="<?= $h ?>" <?= $disabled ?>><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
                
                <select name="pickup_minute" id="pickup_minute" form="cartProductList" required style="font-size: 16px; padding: 10px 15px; border-radius: 8px; border: 1px solid #CCC;">
                    <option value="">Минуты</option>
                    <?php for ($m = 0; $m <= 55; $m += 5): ?>
                        <option value="<?= $m ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Скрипт, который блокирует прошедшие минуты, если выбран текущий час -->
            <script>
                const currentHour = <?= $currentHour ?>;
                const currentMinute = <?= $currentMinute ?>;
                const hourSelect = document.getElementById('pickup_hour');
                const minuteSelect = document.getElementById('pickup_minute');

                if (hourSelect && minuteSelect) {
                    hourSelect.addEventListener('change', function() {
                        const selectedHour = parseInt(this.value);
                        Array.from(minuteSelect.options).forEach(opt => {
                            if (opt.value === "") return;
                            const min = parseInt(opt.value);
                            // Если выбран текущий час, скрываем прошедшие минуты
                            if (selectedHour === currentHour && min <= currentMinute) {
                                opt.disabled = true;
                            } else {
                                opt.disabled = false;
                            }
                        });
                    });
                }
            </script>

            <button type="button"
                    form="cartProductList"
                    class="main-btn-grey cart-order-open-btn"
                    id="openCartOrderModalBtn">
                Оформить заказ
            </button>

            <dialog class="cart-order-modal-window" id="modalCartOrderWindow">
                <div class="cart-order-modal-window__close-btn" id="closeCartOrderModalBtn"></div>
                <h4 class="cart-order-modal-window__title">Ваш заказ</h4>

                <div class="cart-order-modal-window__modal-products-list" id="modalOrderList"></div>

                <button type="submit"
                        form="cartProductList"
                        class="main-btn-white cart-order-modal-window__send-btn">
                    Подтвердить заказ
                </button>
            </dialog>

        <?php endif; ?>

        </div>
    </main>

    <?php
        require_once '../includes/footer.php';
    ?>