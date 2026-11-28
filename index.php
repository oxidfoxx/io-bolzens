<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if (session_id() === '') {
    session_start();
}

$base_path   = 'assets/';
$home_path   = '';
$header_type = 'main';
require_once 'assets/includes/header.php';
?>

    <main class="container">

        <section class="section-container short-about-info">
            <h3 class="primary-title">Мы - булочная с историей</h3>

            <div class="short-about-info__content">
                <img src="assets/images/main/main-short-about-info.webp" alt="baker">

                <p class="primary-p">
                    Больше 15 лет мы печём для вас.<br>
                    Начав с маленькой пекарни на углу, мы выросли, но сохранили главное: ручную работу, внимание к деталям и любовь к своему делу.<br>
                    Каждый наш продукт - это кусочек тепла, который мы дарим вам.
                </p>
            </div>    <!-- /short-about-info_content -->

            <a class="main-btn-grey" href="assets/pages/about.php">Подробнее...</a>
        </section>    <!-- /short-about-info -->

        <section class="section-container popular-products">
            <h3 class="primary-title">Хиты продаж</h3>

            <div class="popular-products__cards-block">
                <div class="popular-products__product-card">
                    <img class="popular-products__card-img" src="assets/images/main/cards/cinnamon-twist.webp" alt="">
                    <p>Булочка с корицей</p>
                </div>
                <div class="popular-products__product-card">
                    <img class="popular-products__card-img" src="assets/images/main/cards/chocolate-brioche.webp" alt="">
                    <p>Бриошь с шоколадом</p>
                </div>
                <div class="popular-products__product-card">
                    <img src="assets/images/main/cards/cheesecake-new-york.webp" alt="">
                    <p>Чизкейк "Нью-Йорк"</p>
                </div>
                <div class="popular-products__product-card">
                    <img src="assets/images/main/cards/cherry-turnover.webp" alt="">
                    <p>Слойка с вишней</p>
                </div>
                <div class="popular-products__product-card">
                    <img src="assets/images/main/cards/latte.webp" alt="">
                    <p>Латте</p>
                </div>
                <div class="popular-products__product-card">
                    <img src="assets/images/main/cards/hot-chocolate-with-marshmallows.webp" alt="">
                    <p>Какао с маршмэллоу</p>
                </div>
            </div>

            <dialog class="popular-products__modal-window" id="modalMainWindow">
                <img class="popular-products__modal-img" src="" alt="">
                <div class="popular-products__modal-info">
                    <h4 class="popular-products__modal-info-title">
                        Title Title Title Title Title Title Title Title
                    </h4>
                    <div class="modal-info-container">
                        <div class="popular-products__modal-info-description">
                            <p>
                                Мука пшеничная, мука ржаная обдирная, вода, соль, закваска спонтанного брожения. Без промышленных дрожжей
                            </p>
                        </div>
                        <div class="popular-products__modal-info-item">
                            <p>масса нетто</p>
                            <p>158 г</p>
                        </div>
                        <div class="popular-products__modal-info-item">
                            <p>белки</p>
                            <p>1.8 г</p>
                        </div>
                        <div class="popular-products__modal-info-item">
                            <p>жиры</p>
                            <p>5.6 г</p>
                        </div>
                        <div class="popular-products__modal-info-item">
                            <p>углеводы</p>
                            <p>24.2 г</p>
                        </div>
                    </div>
                    <button class="main-btn-white popular-products__modal-info-btn" id="closeMainModalBtn">Закрыть</button>
                </div>
            </dialog>
        </section>    <!-- /popular-products -->
        
        <section class="section-container advantages">
            <h3 class="primary-title">Почему выбирают нас</h3>
            <p class="primary-p">
                Потому что для нас булочная - это не просто бизнес. Это место, где пахнет детством, где каждый каравай выпекается с мыслью о том, как его встретят за семейным столом. Мы не экономим на качестве, не используем полуфабрикаты и не продаём вчерашнее. Только свежая выпечка и искренняя радость от вашего визита.
            </p>

            <div class="advantages__cards-block">
                <div class="advantages__card">
                    <img src="assets/images/main/clock-icon.svg" alt="">
                    <p>Печём<br>ежедневно</p>
                </div>
                <div class="advantages__separator"></div>
                <div class="advantages__card">
                    <img src="assets/images/main/wheat-ear-icon.svg" alt="">
                    <p>Натуральные<br>ингредиенты</p>
                </div>
                <div class="advantages__separator"></div>
                <div class="advantages__card">
                    <img src="assets/images/main/book-icon.svg" alt="">
                    <p>Проверенные<br>рецепты</p>
                </div>
                <div class="advantages__separator"></div>
                <div class="advantages__card">
                    <img src="assets/images/main/cup-icon.svg" alt="">
                    <p>Уютная<br>атмосфера</p>
                </div>
            </div>    <!-- /advantage-cards-block -->
        </section>    <!-- /why-choose-us -->

        <section class="reviews-slider">
            <button class="reviews-slider__prev-btn" id="reviews-slider__prev-btn"><img src="assets/images/main/main-slider-button-left.svg" alt="предыдущий"></button>
            <div class="reviews-slider__content"> <!--сюда max-width накинуть-->
                <h3 class="primary-title">Что говорят наши гости</h3>
                <div class="reviews-slider__information reviews-slider__information--active">
                    <img src="assets/images/main/reviews/one.webp" alt="Фото клиента">
                    <p class="primary-p reviews-slider__name">
                        Сергей
                    </p>
                    <p class="reviews-slider__review-text">
                        <span class="reviews-slider__quotes">“”</span><br>
                        Хожу сюда за утренним круассаном уже полгода. Ни разу не разочаровали — слоёное тесто и правда тает во рту. Кофе тоже отличный
                    </p>
                </div>

                <div class="reviews-slider__information">
                    <img src="assets/images/main/reviews/two.webp" alt="Фото клиента">
                    <p class="primary-p reviews-slider__name">
                        Игорь
                    </p>
                    <p class="reviews-slider__review-text">
                        <span class="reviews-slider__quotes">“”</span><br>
                        Работаю рядом, захожу на обеденный перерыв. Беру пирог и кофе — лучший вариант, чтобы быстро и сытно поесть. И отдельное спасибо за вежливых бариста
                    </p>
                </div>

                <div class="reviews-slider__information">
                    <img src="assets/images/main/reviews/three.webp" alt="Фото клиента">
                    <p class="primary-p reviews-slider__name">
                        Мария
                    </p>
                    <p class="reviews-slider__review-text">
                        <span class="reviews-slider__quotes">“”</span><br>
                        Впервые зашла случайно, теперь специально еду через полгорода. Творожные кексы — как в детстве у бабушки. И атмосфера очень тёплая: уютно, чисто, хочется задержаться.
                    </p>
                </div>
            </div>
            <button class="reviews-slider__next-btn" id="reviews-slider__next-btn"><img src="assets/images/main/main-slider-button-right.svg" alt="следующий"></button>
        </section>    <!-- /reviews-slider -->
        
        <section class="section-container location">
            <h3 class="primary-title">Ждём вас в гости</h3>
            <div class="location__address-and-map-block">
                <div class="location__information">
                    <div class="location__address">
                        <h4 class="primary-p">Адрес</h4>
                        <p>
                            г. Иркутск, улица Баумана, строение 233Б
                        </p>
                    </div>

                    <div class="location__worktime">
                        <h4 class="primary-p">Режим работы</h4>
                        <p>
                            Пн - Пт: 8:00 - 20:00<br>Сб: 9:00 - 16:00<br>Вс: Выходной
                        </p>
                    </div>
                </div>
                <div class="location__map"><iframe src="https://yandex.ru/map-widget/v1/?indoorLevel=1&ll=104.150202%2C52.350925&mode=whatshere&whatshere%5Bpoint%5D=104.150481%2C52.351509&whatshere%5Bzoom%5D=17&z=17.89" frameborder="0" allowfullscreen="true"></iframe></div>
            </div>
        </section>

    </main>

    <?php
        require_once 'assets/includes/footer.php';
    ?>
