<?php
if (!isset($base_path)) $base_path    = '';
if (!isset($home_path)) $home_path = '../../';
?>
<footer class="main-footer">
    <div class="main-footer__top-block">
        <img src="<?= $base_path ?>images/logo-primary.svg" alt="Логотип">
        <div class="main-footer__navigation">
            <h3 class="main-footer__title">Навигация</h3>
            <ul>
                <li><a href="<?= $home_path ?>index.php">Главная</a></li>
                <li><a href="<?= $base_path ?>pages/catalog.php">Меню</a></li>
                <li><a href="<?= $base_path ?>pages/gallery.php">Галерея</a></li>
                <li><a href="<?= $base_path ?>pages/about.php">О нас</a></li>
            </ul>
        </div>

        <div class="main-footer__contacts">
            <h3 class="main-footer__title">Контакты</h3>
            <ul>
                <li>с 9:00 до 20:00</li>
                <li>+7 (923) 342-34-53</li>
                <li>g-croissant@mail.ru</li>
            </ul>
        </div>

        <div class="main-footer__map"><iframe src="https://yandex.ru/map-widget/v1/?indoorLevel=1&ll=104.150202%2C52.350925&mode=whatshere&whatshere%5Bpoint%5D=104.150481%2C52.351509&whatshere%5Bzoom%5D=17&z=17.89" frameborder="0" allowfullscreen="true"></iframe></div>
    </div>    <!-- /main-footer__block -->
    <div class="main-footer__separator"></div>
    <div class="main-footer__bottom-block">
        <P>
            Golden Croissant &copy; 2026
        </P>

        <div class="main-footer__social-media">
            <a href="https://www.instagram.com/"><img src="<?= $base_path ?>images/instagram.svg" alt="Instagram"></a>
            <a href="https://m.vk.com/"><img src="<?= $base_path ?>images/vkontakte.svg" alt="VK"></a>
            <a href="https://ru.pinterest.com/"><img src="<?= $base_path ?>images/pinterest.svg" alt="Pinterest"></a>
            <a href="https://x.com/?lang=ru"><img src="<?= $base_path ?>images/twitter.svg" alt="Twitter"></a>
        </div>
    </div>    <!-- main-footer__block -->
</footer>

<script src="<?= $base_path ?>js/script.js"></script>
</body>
</html>