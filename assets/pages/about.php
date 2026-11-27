<?php
$base_path   = '../';
$home_path   = '../../';
$header_type = 'about';
require_once '../includes/header.php';
?>

    <main class="container">
        <section class="section-container full-about-info">
            <h3 class="primary-title">15 лет с Вами</h3>

            <div class="full-about-info__content">
                <img src="../images/about/about-full-about-info.webp" alt="">

                <p class="primary-p">
                    Всё началось с маленькой пекарни на углу и мечты - дарить людям тепло через выпечку. 15 лет назад мы впервые открыли двери для первых гостей, и с тех пор аромат свежего хлеба здесь не умолкает ни на минуту.<br>
                    Сейчас у нас несколько пекарен и множество любимых клиентов, но главное осталось неизменным - мы не перешли на конвейер. Наши круассаны до сих пор вручную сворачивает пекарь, а начинку для пирожков мы готовим так же, как 15 лет назад - с душой и без хитростей.    
                </p>
            </div>    <!-- /short-about-info_content -->
        </section>    <!-- /short-about-info -->

        <div class="section-container our-team">
            <h3 class="primary-title">Наша команда</h3>

            <div class="our-team__cards-container">
                <div class="our-team__card">
                    <img src="../images/about/team/one.webp" alt="" class="our-team__card-img">
                    <div class="our-team__card-info">
                        <h4 class="our-team__person-name">Наталья</h4>
                        <p class="our-team__person-info">
                            Технолог производства<br>
                            Следит за рецептурами и качеством. Любит говорить: «Мы не едим химию, и вам не советуем»
                        </p>
                    </div>
                </div>


                <div class="our-team__card">
                    <img src="../images/about/team/two.webp" alt="" class="our-team__card-img">
                    <div class="our-team__card-info">
                        <h4 class="our-team__person-name">Татьяна</h4>
                        <p class="our-team__person-info">
                            Управляющая<br>
                            Мирит пекарей и кондитеров, закупает продукты и всегда знает, сколько килограммов муки уйдёт на завтра
                        </p>
                    </div>
                </div>

                <div class="our-team__card">
                    <img src="../images/about/team/three.webp" alt="" class="our-team__card-img">
                    <div class="our-team__card-info">
                        <h4 class="our-team__person-name">Екатерина</h4>
                        <p class="our-team__person-info">
                            Главный пекарь<br>
                            Встаёт рано утром, чтобы к открытию у вас был горячий хлеб. Говорит, что тесто любит тишину
                        </p>
                    </div>
                </div>

                <div class="our-team__card">
                    <img src="../images/about/team/four.webp" alt="" class="our-team__card-img">
                    <div class="our-team__card-info">
                        <h4 class="our-team__person-name">Алексей</h4>
                        <p class="our-team__person-info">
                            Кондитер<br>
                            Отвечает за то, чтобы утром у витрины с пирожными и десертами выстраивалась большая очередь
                        </p>
                    </div>
                </div>
            </div>    <!-- /our-team__cards-container -->
        </div>    <!-- /our-team -->
    </main>

    <?php
        require_once '../includes/footer.php';
    ?>