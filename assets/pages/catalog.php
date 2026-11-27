<?php
session_start();

// Обработка получения бесплатного кофе
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'redeem_free_coffee') {
    $productId = (int)$_POST['product_id'];
    
    // 1. Добавляем в корзину
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
    $_SESSION['cart'][$productId] = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] + 1 : 1;
    
    // 2. ЗАПИСЫВАЕМ МЕТКУ, ЧТО ЭТОТ КОФЕ БЕСПЛАТНЫЙ
    $_SESSION['free_promo_id'] = $productId;
    
    // 3. ОБНУЛЯЕМ ПРОГРЕСС В БАЗЕ ДАННЫХ
    require_once '../php/db.php'; 
    $stmt = $pdo->prepare("UPDATE users SET promo_progress = 0 WHERE user_id = ?");
    $stmt->execute(array($_SESSION['user_id']));

    // 4. Перекидываем в корзину
    header('Location: cart.php');
    exit;
}

$base_path   = '../';
$home_path   = '../../';
$header_type = 'catalog';

require_once '../php/db.php';                    // подключение к БД
require_once '../includes/db_helpers.php';          // функции

$categories = getAllCategories($pdo);
$productsByCategory = getProductsGroupedByCategory($pdo);

$cartProductIds = array();
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartProductIds = array_map('intval', array_keys($_SESSION['cart']));
}

require_once '../includes/header.php';
?>

    <main class="container">
        <section class="product-catalog">
            <div class="sorting">
                <button data-target="all" class="sorting__btn sorting__btn--active">Все продукты</button>
                <?php foreach ($categories as $cat): ?>
                    <button data-target="<?php echo htmlspecialchars($cat['slug']); ?>" class="sorting__btn">
                        <?php echo htmlspecialchars($cat['title']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="preorder-info">
                <h3 class="primary-title">Ваш заказ уже ждёт</h3>

                <div class="preorder-info__content">
                    <img src="../images/catalog/catalog-preorder-info.webp" alt="baker">

                    <p class="primary-p">
                        Не любите ждать? Просто забронируйте любимые позиции заранее и укажите удобное время визита.<br>
                        Вы приезжаете ровно к назначенному часу, а мы уже держим для вас горячий пакет со свежей выпечкой.<br>
                        Быстро, удобно, без ожидания у витрины.
                    </p>
                </div>
            </div>

            <dialog class="product-catalog__modal-window" id="modalCatalogWindow">
                <div class="product-catalog__modal-close-btn" id="closeCatalogModalBtn"></div>
                <img class="product-catalog__modal-img" src="" alt="">
                <div class="product-catalog__modal-info">
                    <h4 class="product-catalog__modal-info-title"></h4>
                    <div class="modal-info-container">
                        <div class="product-catalog__modal-info-description">
                            <p></p>
                        </div>
                        <div class="product-catalog__modal-info-item">
                            <p>масса нетто</p>
                            <p>-</p>
                        </div>
                        <div class="product-catalog__modal-info-item">
                            <p>белки</p>
                            <p>-</p>
                        </div>
                        <div class="product-catalog__modal-info-item">
                            <p>жиры</p>
                            <p>-</p>
                        </div>
                        <div class="product-catalog__modal-info-item">
                            <p>углеводы</p>
                            <p>-</p>
                        </div>
                    </div>
                    <div class="product-catalog__modal-info-btn">
                        <button class="main-btn-white product-catalog__add-to-cart-btn"
                            id="modalAddToCartBtn"
                            data-id=""
                            data-added="0">
                            В корзину
                        </button>
                        <p class="product-catalog__modal-info-price"></p>
                    </div>
                </div>
            </dialog>

            <?php foreach ($categories as $cat): ?>
                <?php
                $slug = $cat['slug'];
                $catTitle = $cat['title'];
                $catProducts = isset($productsByCategory[$slug]) ? $productsByCategory[$slug] : array();
                ?>
                <div id="<?php echo htmlspecialchars($slug); ?>" class="product-catalog__category">
                    <h3 class="primary-title"><?php echo htmlspecialchars($catTitle); ?></h3>

                    <div class="product-catalog__cards-grid">
                        <?php foreach ($catProducts as $product): ?>
                            <div class="product-catalog__card"
                                data-id="<?php echo $product['product_id']; ?>"
                                data-title="<?php echo htmlspecialchars($product['title']); ?>"
                                data-price="<?php echo $product['price']; ?>"
                                data-image="<?php echo htmlspecialchars($product['image_path']); ?>"
                                data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                data-weight="<?php echo htmlspecialchars($product['weight']); ?>"
                                data-proteins="<?php echo htmlspecialchars($product['proteins']); ?>"
                                data-fats="<?php echo htmlspecialchars($product['fats']); ?>"
                                data-carbs="<?php echo htmlspecialchars($product['carbs']); ?>">

                                <img src="<?php echo $base_path . htmlspecialchars($product['image_path']); ?>" alt="">

                                <h4><?php echo htmlspecialchars($product['title']); ?></h4>

                                <div class="product-catalog__card-price-and-btn">
                                    <?php if (in_array((int)$product['product_id'], $cartProductIds)): ?>
                                        <a class="main-btn-white product-catalog__card-btn add-to-cart-btn added"
                                        href="#"
                                        data-id="<?php echo $product['product_id']; ?>"
                                        data-added="1">Добавлено</a>
                                    <?php else: ?>
                                        <a class="main-btn-white product-catalog__card-btn add-to-cart-btn"
                                            href="#"
                                            data-id="<?php echo $product['product_id']; ?>"
                                            data-added="0">
                                            В корзину
                                        </a>
                                    <?php endif; ?>
                                    <p><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>    <!-- /product-catalog -->
    </main>

    <?php
        require_once '../includes/footer.php';
    ?>