// Проверка элемента на существование для EventListener

function setEventListener(el, func) {
    if (el) {
        el.addEventListener('click', func)
    }
}

// sliderReview

const reviews = document.querySelectorAll('.reviews-slider__information');
const nextBtn = document.getElementById('reviews-slider__next-btn');
const prevBtn = document.getElementById('reviews-slider__prev-btn');
let currentReviewIndex = 0;

function switchReview(index) {
    reviews.forEach((review, i) => {
        currentReviewIndex = (currentReviewIndex + 1) % reviews.length;
        review.classList.toggle('reviews-slider__information--active', i === index);
    });
}

setEventListener(nextBtn, () => {
    currentReviewIndex = (currentReviewIndex + 1) % reviews.length;
    switchReview(currentReviewIndex);
});

setEventListener(prevBtn, () => {
    currentReviewIndex = (currentReviewIndex - 1 + reviews.length) % reviews.length;
    switchReview(currentReviewIndex);
});

// sideMenu

const sideMenuBtn = document.getElementById('sideMenuBtn');
const sideMenu = document.getElementById('sideMenu');

setEventListener(sideMenuBtn, () => {
    sideMenu.classList.toggle('side-menu--active')
});


// modalCartInfoWindow

const cartCards = document.querySelectorAll('.cart-products-list__card');

const modalCartInfoWindow = document.getElementById('modalCartInfoWindow');
const closeCartInfoModalBtn = document.getElementById('closeCartInfoModalBtn');
const modalCartInfoWindowImg = document.querySelector('.cart-products-list__modal-img');

cartCards.forEach(card => {
    card.addEventListener('click', (event) => {
        const checkbox = card.querySelector('.cart-products-list__checkbox');
        const isButtonClick = event.target.closest('.cart-products-list__card-btn');

        if (event.target !== checkbox && !isButtonClick) {
            checkbox.checked = !checkbox.checked;

            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (isButtonClick) {
            const cardImgSrc = card.querySelector('img');
            modalCartInfoWindowImg.src = cardImgSrc.src;
            //
            const cardTitle = card.querySelector('h4').textContent;
            const modalCardTitle = document.querySelector('.cart-products-list__modal-info-title');
            modalCardTitle.textContent = cardTitle;
            //
            modalCartInfoWindow.showModal();
        }
    });
});

setEventListener(closeCartInfoModalBtn, () => {
    modalCartInfoWindow.close();
});

// modalCartOrderWindow

const modalCartOrderWindow = document.getElementById('modalCartOrderWindow');
const modalCartOrderContent = document.querySelector('.cart-order-modal-window__modal-products-list');
const openCartOrderModalBtn = document.getElementById('openCartOrderModalBtn');
const closeCartOrderModalBtn = document.getElementById('closeCartOrderModalBtn');

setEventListener(openCartOrderModalBtn, () => {
    if (!modalCartOrderContent) return;
    modalCartOrderContent.innerHTML = '';

    const checkedCards = document.querySelectorAll('.cart-products-list__checkbox:checked');

    if (checkedCards.length === 0) {
        modalCartOrderContent.innerHTML = '<p>Ничего не выбрано</p>';
    } else {
        checkedCards.forEach(checkbox => {
            const card = checkbox.closest('.cart-products-list__card');
            const productId = card.dataset.id;
            const price = parseInt(card.dataset.price);
            const isFree = card.dataset.isFree === '1'; 
            const cardImgSrc = card.querySelector('img').src;
            const cardTitle = card.querySelector('h4').textContent.trim();

            // Если это бесплатный товар, блокируем кнопки и поле ввода
            const disabledAttr = isFree ? 'disabled' : '';
            const readonlyAttr = isFree ? 'readonly' : '';
            const btnStyle = isFree ? 'pointer-events: none; opacity: 0.5;' : '';

            const cardInModal = `
                <div class="cart-order-modal-window__modal-product" data-price="${price}">
                    <img class="cart-order-modal-window__modal-product-img" src="${cardImgSrc}" alt="">
                    <div class="cart-order-modal-window__modal-product-info">
                        <div class="cart-order-modal-window__modal-info-item cart-order-modal-window__modal-info-item-title">
                            <h5>Название</h5>
                            <p>${cardTitle} ${isFree ? '<span style="color: #5CB85C; font-size: 12px;">(Бесплатно)</span>' : ''}</p>
                        </div>
                        <div class="cart-order-modal-window__modal-info-item">
                            <h5>Кол-во</h5>
                            <div class="cart-order-modal-window__product-value">
                                ${isFree ? `
                                    <span style="font-size: 20px; font-weight: 500; color: #5CB85C;">1 шт. (в подарок)</span>
                                    <input type="hidden" form="cartProductList" name="quantity[${productId}]" value="1">
                                ` : `
                                    <button type="button"
                                            onclick="changeProductValue(this, -1)"
                                            class="cart-order-modal-window__value-minus-btn"></button>
                                    <input type="number"
                                            form="cartProductList"
                                            name="quantity[${productId}]"
                                            value="1"
                                            min="1"
                                            max="99"
                                            step="1">
                                    <button type="button"
                                            onclick="changeProductValue(this, 1)"
                                            class="cart-order-modal-window__value-plus-btn"></button>
                                `}
                            </div>
                        </div>
                        <div class="cart-order-modal-window__modal-info-item">
                            <h5>Цена/шт</h5>
                            <p>${price.toLocaleString('ru-RU')} ₽</p>
                        </div>
                    </div>
                </div>
            `;

            modalCartOrderContent.innerHTML += cardInModal;
        });
    }

    modalCartOrderWindow.showModal();
});

setEventListener(closeCartOrderModalBtn, () => {
    modalCartOrderWindow.close();
});

// Функция изменения количества в модалке
function changeProductValue(button, delta) {
    const inputField = button.parentNode.querySelector('input[type=number]');
    let value = parseInt(inputField.value) + delta;
    if (value < 1) value = 1;
    if (value > 99) value = 99;
    inputField.value = value;
}

// modalMainWindow

const openMainModalBtn = document.querySelectorAll('.popular-products__product-card');
const closeMainModalBtn = document.getElementById('closeMainModalBtn');
const modalMainWindow = document.getElementById('modalMainWindow');
const modalMainWindowImg = document.querySelector('.popular-products__modal-img');

openMainModalBtn.forEach(card => {
    card.addEventListener('click', () => {
        const cardImgSrc = card.querySelector('img');
        modalMainWindowImg.src = cardImgSrc.src;
        //
        const cardTitle = card.querySelector('p').textContent;
        const modalCardTitle = document.querySelector('.popular-products__modal-info-title');
        modalCardTitle.textContent = cardTitle;
        //
        modalMainWindow.showModal();
    });
});

setEventListener(closeMainModalBtn, () => {
    modalMainWindow.close();
});


// modalCatalogWindow

const openCatalogModalBtn  = document.querySelectorAll('.product-catalog__card');
const closeCatalogModalBtn = document.getElementById('closeCatalogModalBtn');
const modalCatalogWindow   = document.getElementById('modalCatalogWindow');

openCatalogModalBtn.forEach((card) => {
    card.addEventListener('click', function(event) {
        const isButtonClick = event.target.closest('.main-btn-white');
        if (isButtonClick) return;

        const data = card.dataset;

        // Картинка и заголовок
        document.querySelector('.product-catalog__modal-img').src = '../' + data.image;
        document.querySelector('.product-catalog__modal-info-title').textContent = data.title;
        const modalBtn = document.getElementById('modalAddToCartBtn');
        modalBtn.dataset.id = data.id;

        // Проверяем, что товар уже в корзине — берём с карточки
        const sourceCard = event.currentTarget;
        const wasAdded = sourceCard.querySelector('.add-to-cart-btn')?.dataset.added === '1';

        if (wasAdded) {
            modalBtn.dataset.added = '1';
            modalBtn.textContent = 'Добавлено';
            modalBtn.style.pointerEvents = 'none';
            modalBtn.style.opacity = '0.6';
        } else {
            modalBtn.dataset.added = '0';
            modalBtn.textContent = 'В корзину';
            modalBtn.style.pointerEvents = '';
            modalBtn.style.opacity = '';
        }

        // Цена
        const price = parseInt(data.price, 10);
        document.querySelector('.product-catalog__modal-info-price').textContent =
            price.toLocaleString('ru-RU') + ' ₽';

        // Описание
        const descEl = document.querySelector('.product-catalog__modal-info-description p');
        descEl.textContent = data.description || '';

        // КБЖУ и масса
        const items = document.querySelectorAll('.product-catalog__modal-info-item');
        if (items[0]) items[0].querySelectorAll('p')[1].textContent = data.weight   || '—';
        if (items[1]) items[1].querySelectorAll('p')[1].textContent = data.proteins || '—';
        if (items[2]) items[2].querySelectorAll('p')[1].textContent = data.fats     || '—';
        if (items[3]) items[3].querySelectorAll('p')[1].textContent = data.carbs    || '—';

        modalCatalogWindow.showModal();
    });
});

setEventListener(closeCatalogModalBtn, () => {
    modalCatalogWindow.close();
});

// catalogSorting ?сделать анимации?

document.addEventListener('DOMContentLoaded', () => {
    const navButtons = document.querySelector('.product-catalog .sorting');
    const productSections = document.querySelectorAll('.product-catalog__category');
    const infoBlock = document.querySelector('.preorder-info');

    setEventListener(navButtons, (e) => {
        const targetId = e.target.dataset.target;

        if (!targetId) return;

        productSections.forEach(section => {
            const isVisible = (targetId === 'all' || section.id === targetId);
            section.style.display = isVisible ? 'flex' : 'none';
        });

        infoBlock.style.display = (targetId === 'all') ? 'flex' : 'none';

        navButtons.querySelectorAll('.sorting__btn').forEach(btn => btn.classList.remove('sorting__btn--active'));
        e.target.classList.add('sorting__btn--active');
    });
});

// catalogSearch

let searchTimeout = null;

function handleSearch() {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        searchCatalogProducts();
    }, 300);
}


function scrollToResults() {
    // 1. Убираем фокус с поля ввода (скрывает клавиатуру на смартфонах)
    document.getElementById('searchInput').blur();

    // 2. Ищем первую карточку, которая НЕ скрыта
    // Используем селектор :not с вашим классом скрытия
    const firstCard = document.querySelector('.product-catalog__card:not(.hidden-completely)');

    if (firstCard) {
        // 3. Плавно прокручиваем к ней
        firstCard.scrollIntoView({
            behavior: 'smooth',
            block: 'center' // 'center' лучше, чтобы карточка была в середине экрана
        });
    }
}


function searchCatalogProducts() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
    const catalogCategories = document.querySelectorAll('.product-catalog__category');

    //
    const infoBlock = document.querySelector('.preorder-info');
    infoBlock.style.display = (searchQuery.length > 0) ? 'none' : 'flex';
    // 

    catalogCategories.forEach(category => {
        const cards = category.querySelectorAll('.product-catalog__card');
        let hasVisibleCards = false;

        cards.forEach(card => {
            const title = card.querySelector('h4').textContent.toLowerCase();

            if (title.includes(searchQuery)) {
                card.style.display = '';
                hasVisibleCards = true;
            } else {
                card.style.display = 'none';
            }
        });

        category.style.display = hasVisibleCards ? "" : "none";
    });
}

// ============ modalProfileWindow ============

const openProfileModalBtn = document.querySelectorAll('.orders-list__card');
const closeProfileModalBtn = document.getElementById('closeProfileOrderModalBtn');
const modalProfileWindow = document.getElementById('modalProfileOrderWindow');

openProfileModalBtn.forEach(card => {
    card.addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        const code = this.dataset.pickupCode;
        const status = this.querySelector('.orders-list__order-status').textContent.trim();

        document.querySelector('.order-list__modal-number span').textContent = orderId;
        const statusEl = document.querySelector('.order-list__modal-status');
        statusEl.textContent = status;
        statusEl.dataset.status = status;

        // Загружаем товары через AJAX
        const content = document.getElementById('orderModalContent');
        content.innerHTML = '<p style="padding: 20px;">Загрузка...</p>';

        fetch('../php/get_order_items.php?order_id=' + encodeURIComponent(orderId))
            .then(r => r.json())
            .then(items => {
                if (!items.length) {
                    content.innerHTML = '<p style="padding: 20px;">Товары не найдены.</p>';
                    return;
                }
                content.innerHTML = items.map(item => `
                    <div class="order-list__modal-product">
                        <img class="order-list__modal-product-img" src="../${item.image_path}" alt="">
                        <div class="order-list__modal-product-info">
                            <div class="order-list__modal-info-item order-list__modal-info-item-title">
                                <h5>Название</h5>
                                <p>${item.title}</p>
                            </div>
                            <div class="order-list__modal-info-item">
                                <h5>Кол-во</h5>
                                <p>${item.quantity}</p>
                            </div>
                            <div class="order-list__modal-info-item">
                                <h5>Цена/шт</h5>
                                <p>${parseInt(item.price).toLocaleString('ru-RU')} ₽</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(() => {
                content.innerHTML = '<p style="padding: 20px;">Ошибка загрузки.</p>';
            });

        modalProfileWindow.showModal();
    });
});

setEventListener(closeProfileModalBtn, () => {
    modalProfileWindow.close();
});


// ============ Корзина: добавление товара ============

function addToCart(productId, onSuccess) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../php/add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success && onSuccess) onSuccess(response);
            } catch (e) {
                console.error('Ошибка парсинга ответа:', xhr.responseText);
            }
        } else {
            console.error('Ошибка запроса:', xhr.status);
        }
    };
    xhr.send('product_id=' + encodeURIComponent(productId));
}

// Меняем кнопку на "Добавлено" и блокируем
function markAsAdded(btn) {
    btn.textContent = 'Добавлено';
    btn.classList.add('added');
    btn.dataset.added = '1';
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.6';
}

// --- Клик по кнопкам в карточках каталога ---
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    // Если товар уже в корзине — сразу помечаем
    if (btn.dataset.added === '1') {
        markAsAdded(btn);
    }

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (this.dataset.added === '1') return; // уже добавлено

        const id = this.dataset.id;
        const self = this;

        addToCart(id, function() {
            markAsAdded(self);
        });
    });
});

// --- Клик по кнопке в модалке ---
const modalAddBtn = document.getElementById('modalAddToCartBtn');
if (modalAddBtn) {
    modalAddBtn.addEventListener('click', function() {
        const id = this.dataset.id;
        if (!id) return;
        if (this.dataset.added === '1') return;

        const self = this;
        addToCart(id, function() {
            // 1. Обновляем кнопку в модалке
            markAsAdded(self);
            self.dataset.added = '1';

            // 2. Находим карточку в каталоге и обновляем её кнопку
            const catalogBtn = document.querySelector('.add-to-cart-btn[data-id="' + id + '"]');
            if (catalogBtn) {
                markAsAdded(catalogBtn);
                catalogBtn.dataset.added = '1';
            }
        });
    });
}

// Проверка выбора времени перед отправкой
document.addEventListener('DOMContentLoaded', () => {
    const cartForm = document.getElementById('cartProductList');
    const hourSelect = document.getElementById('pickup_hour');
    const minuteSelect = document.getElementById('pickup_minute');

    // Функция проверки
    function checkTimeAndSubmit(event) {
        if (!hourSelect || !minuteSelect) return;

        if (hourSelect.value === '' || minuteSelect.value === '') {
            event.preventDefault(); // Блокируем отправку
            
            // Красивое уведомление (если у вас подключен SweetAlert или своя модалка)
            // Если нет — используем стандартный alert, но с четким текстом:
            alert('Пожалуйста, выберите время готовности (часы и минуты) перед оформлением заказа!');
            
            // Подсветим поля красным, чтобы привлечь внимание
            hourSelect.style.borderColor = 'red';
            minuteSelect.style.borderColor = 'red';
            
            // Убираем красную рамку через 3 секунды
            setTimeout(() => {
                hourSelect.style.borderColor = '#CCC';
                minuteSelect.style.borderColor = '#CCC';
            }, 3000);
        }
    }

    // Вешаем проверку на кнопку в основном окне корзины (если это обычная кнопка submit)
    // и на кнопку в модалке заказа
    const submitBtns = document.querySelectorAll('button[type="submit"][form="cartProductList"]');
    submitBtns.forEach(btn => {
        btn.addEventListener('click', checkTimeAndSubmit);
    });
    
    // Также на всякий случай вешаем на саму форму (если отправка идет через Enter)
    if (cartForm) {
        cartForm.addEventListener('submit', checkTimeAndSubmit);
    }
});