<?php
if (!isset($base_path)) $base_path    = '';
if (!isset($header_type)) $header_type  = 'main';
if (!isset($home_path)) $home_path = '../../';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Карта: тип хедера → CSS-класс <header> и заголовок h1
$headerConfig = [
    'main'    => ['class' => 'main-header',    'title' => 'Мы выпекаем для Вас!'],
    'catalog' => ['class' => 'catalog-header', 'title' => 'Все самое свежее - в одном месте'],
    'gallery' => ['class' => 'gallery-header', 'title' => 'Галерея'],
    'about'   => ['class' => 'about-header',   'title' => 'Больше, чем просто булочная'],
    'cart'    => ['class' => 'cart-header',    'title' => 'Корзина'],
    'profile' => ['class' => 'profile-header', 'title' => null], // у profile нет h1 в хедере
];

$currentHeader = isset($headerConfig[$header_type]) ? $headerConfig[$header_type] : $headerConfig['main'];
$headerClass   = $currentHeader['class'];
$headerTitle   = $currentHeader['title'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Croissant</title>
    <link rel="icon" href="<?= $base_path ?>images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $base_path ?>css/main.css">
</head>
<body>
    <header class="<?= $headerClass ?>">

    <?php if ($header_type === 'main' || $header_type === 'gallery' || $header_type === 'about'): ?>
        <?php /* ========== Общий navbar для main / gallery / about ========== */ ?>
        <nav class="navbar">
            <a class="navbar__logo-image-link" href="<?= $home_path ?>index.php">
                <img class="logo-image" src="<?= $base_path ?>images/logo-primary.svg" alt="logo">
            </a>

            <ul class="navbar__elements">
                <li class="navbar-elements_item"><a href="<?= $base_path ?>pages/catalog.php">Меню</a></li>
                <li class="navbar-elements_item"><a href="<?= $base_path ?>pages/gallery.php">Галерея</a></li>
                <li class="navbar-elements_item"><a href="<?= $base_path ?>pages/about.php">О нас</a></li>
            </ul>

            <button id="sideMenuBtn" class="navbar__menu-btn">
                <img src="<?= $base_path ?>images/menu-icon.svg" alt="">
            </button>

            <div class="navbar__social-media">
                <a href="https://www.instagram.com/"><img src="<?= $base_path ?>images/instagram.svg" alt="Instagram"></a>
                <a href="https://m.vk.com/"><img src="<?= $base_path ?>images/vkontakte.svg" alt="VK"></a>
                <a href="https://ru.pinterest.com/"><img src="<?= $base_path ?>images/pinterest.svg" alt="Pinterest"></a>
                <a href="https://x.com/?lang=ru"><img src="<?= $base_path ?>images/twitter.svg" alt="Twitter"></a>
            </div>
        </nav>

    <?php elseif ($header_type === 'catalog'): ?>
        <?php /* ========== Navbar каталога (с поиском) ========== */ ?>
        <nav class="catalog-navbar">
            <a class="navbar__logo-image-link" href="<?= $home_path ?>index.php">
                <img class="logo-image" src="<?= $base_path ?>images/logo-primary.svg" alt="logo">
            </a>

            <div class="catalog-navbar__catalog-search">
                <input id="searchInput" type="search" oninput="handleSearch()" onkeydown="if(event.key === 'Enter') scrollToResults()" placeholder="Поиск">
                <button onclick="scrollToResults()">
                    <img src="<?= $base_path ?>images/catalog/catalog-seacrh-icon.svg" alt="искать">
                </button>
            </div>

            <div class="catalog-navbar__nav-elements">
                <a class="catalog-navbar__nav-item" href="cart.php">
                    <img src="<?= $base_path ?>images/catalog/catalog-cart-icon.svg" alt="Корзина">
                </a>
                <button class="catalog-navbar__nav-item" id="sideMenuBtn">
                    <img src="<?= $base_path ?>images/menu-icon.svg" alt="Меню">
                </button>
            </div>
        </nav>

    <?php elseif ($header_type === 'cart' || $header_type === 'profile'): ?>
        <?php /* ========== Минимальный navbar для cart и profile ========== */ ?>
        <nav class="navbar">
            <a class="navbar__logo-image-link" href="<?= $home_path ?>index.php">
                <img class="logo-image" src="<?= $base_path ?>images/logo-primary.svg" alt="logo">
            </a>

            <div class="cart-navbar__nav-elements">
                <button class="cart-navbar__nav-item" id="sideMenuBtn">
                    <img src="<?= $base_path ?>images/menu-icon.svg" alt="Меню">
                </button>
            </div>
        </nav>
    <?php endif; ?>

    <?php /* ========== Контент хедера (h1 и т.п.) ========== */ ?>
    <?php if ($header_type === 'main'): ?>
        <div class="main-header__content">
            <h1 class="main-header__title"><?= $headerTitle ?></h1>
            <p class="main-header__text">
                Каждое утро мы трудимся, чтобы наполнить город ароматом тёплого хлеба, хрустящих круассанов и домашних пирогов
            </p>
            <a href="<?= $base_path ?>pages/catalog.php" class="main-btn-white main-header__btn">
                Смотреть каталог
            </a>
        </div>

    <?php elseif ($header_type === 'catalog'): ?>
        <div class="catalog-header__content">
            <h1 class="catalog-header__title"><?= $headerTitle ?></h1>
        </div>

    <?php elseif ($header_type === 'gallery'): ?>
        <div class="gallery-header__content">
            <h1 class="gallery-header__title"><?= $headerTitle ?></h1>
        </div>

    <?php elseif ($header_type === 'about'): ?>
        <div class="about-header__content">
            <h1 class="about-header__title"><?= $headerTitle ?></h1>
        </div>

    <?php elseif ($header_type === 'cart'): ?>
        <div class="cart-header__content">
            <h1 class="cart-header__title"><?= $headerTitle ?></h1>
        </div>

    <?php elseif ($header_type === 'profile'): ?>
        <?php
        $userName = isset($_SESSION['user_name'])
            ? htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8')
            : 'Гость';
        ?>
        <div class="profile-header__content">
            <div class="profile-header__user-photo"></div>
            <h1 class="profile-header__title"><?= $userName ?></h1>
        </div>
    <?php endif; ?>

    <?php /* ========== Боковое меню ========== */ ?>
    <div class="side-menu <?= $header_type === 'catalog' ? 'catalog-side-menu' : '' ?>" id="sideMenu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a class="side-menu__item" href="<?= $base_path ?>pages/profile.php">Профиль</a>
        <?php else: ?>
            <a class="side-menu__item" href="<?= $base_path ?>pages/auth.php">Войти</a>
        <?php endif; ?>

        <?php if ($header_type === 'main'): ?>
            <a class="side-menu__item" href="<?= $base_path ?>pages/catalog.php">Меню</a>
            <a class="side-menu__item" href="<?= $base_path ?>pages/gallery.php">Галерея</a>
            <a class="side-menu__item" href="<?= $base_path ?>pages/about.php">О нас</a>

        <?php elseif ($header_type === 'catalog'): ?>
            <a class="side-menu__item" href="about.php">О нас</a>
            <a class="side-menu__item" href="gallery.php">Галерея</a>

        <?php elseif ($header_type === 'cart'): ?>
            <a class="side-menu__item" href="catalog.php">Меню</a>

        <?php elseif ($header_type === 'profile'): ?>
            <a class="side-menu__item" href="catalog.php">Меню</a>
            <a class="side-menu__item" href="cart.php">Корзина</a>
            <a class="side-menu__item" href="about.php">О нас</a>
            <a class="side-menu__item" href="gallery.php">Галерея</a>
            <a class="side-menu__item" href="../php/logout.php">Выйти</a>

        <?php elseif ($header_type === 'gallery'): ?>
            <a class="side-menu__item" href="catalog.php">Меню</a>
            <a class="side-menu__item" href="about.php">О нас</a>

        <?php elseif ($header_type === 'about'): ?>
            <a class="side-menu__item" href="catalog.php">Меню</a>
            <a class="side-menu__item" href="gallery.php">Галерея</a>
        <?php endif; ?>
    </div>
</header>