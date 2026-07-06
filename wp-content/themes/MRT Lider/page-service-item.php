<?php
/*
Template Name: uslugi
*/

// --- Список валидных слагов городов ---
$known_city_slugs = array(
    'almaty', 'astana', 'karaganda', 'taldykorgan'
);

// --- Определяем город: URL > кука > fallback ---
$selected_city = 'almaty'; // fallback

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = $path ? explode('/', $path) : array();
$url_city = !empty($path_parts[0]) ? sanitize_text_field($path_parts[0]) : '';

if ($url_city && in_array($url_city, $known_city_slugs, true)) {
    $selected_city = $url_city;
} elseif (isset($_COOKIE['selected_city'])) {
    $cookie_city = sanitize_text_field($_COOKIE['selected_city']);
    if (in_array($cookie_city, $known_city_slugs, true)) {
        $selected_city = $cookie_city;
    }
}

// --- Карта slug → название филиала (для совместимости) ---
$city_to_branch = array(
    'samara' => 'Самара',
    'khabarovsk' => 'Хабаровск',
    'habarovsk' => 'Хабаровск',
    'komsomolsk' => 'Комсомольск-на-Амуре',
    'rostov' => 'Ростов-на-Дону',
    'vladivostok' => 'Владивосток',
    'vladivostock' => 'Владивосток',
    'petropavlovsk-kamchatskij' => 'Петропавловск-Камчатский',
    'petropavlovsk_kamchatskij' => 'Петропавловск-Камчатский',
    'naberezhnye_chelny' => 'Набережные Челны',
    'nizhnij_tagil' => 'Нижний Тагил',
    'nizhnij_novgorod' => 'Нижний Новгород',
    'krasnoyarsk' => 'Красноярск',
    'tumen' => 'Тюмень',
    'serov' => 'Серов',
);

// --- Определяем название филиала ---
$branch_name = $city_to_branch[$selected_city] ?? null;

if (!$branch_name) {
    $all_branch_terms = get_terms(array('taxonomy' => 'branch', 'hide_empty' => false));
    foreach ($all_branch_terms as $term) {
        if (stripos($term->name, $selected_city) !== false || sanitize_title($term->name) === $selected_city) {
            $branch_name = $term->name;
            break;
        }
    }
}

if (!$branch_name) {
    $branch_name = $selected_city;
}

// --- Получаем термин филиала ---
$branch_term = get_term_by('name', $branch_name, 'branch');

if (!$branch_term) {
    get_header();
    echo '<main class="main"><div class="container"><p>Для данного филиала данных не найдено</p></div></main>';
    get_footer();
    exit;
}

// --- Определяем slug услуги из URL ---
$service_slug = get_query_var('service_type', '');
if (empty($service_slug)) {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $parts = explode('/', $path);
    $idx = array_search('uslugi', $parts);
    if ($idx !== false && isset($parts[$idx + 1]) && !empty($parts[$idx + 1])) {
        $service_slug = sanitize_text_field($parts[$idx + 1]);
    } else {
        $service_slug = sanitize_text_field(end($parts));
    }
}

// --- Формируем tax_query ---
$tax_query = array(
    'relation' => 'AND',
    array(
        'taxonomy' => 'branch',
        'field'    => 'slug',
        'terms'    => $branch_term->slug,
    )
);

$meta_query = null;

if (!empty($service_slug)) {
    $service_term = get_term_by('slug', $service_slug, 'service_type');
    
    if ($service_term) {
        $tax_query[] = array(
            'taxonomy' => 'service_type',
            'field'    => 'slug',
            'terms'    => $service_term->slug,
        );
    } else {
        // Попытка найти каноническое имя (если функция существует)
        if (function_exists('si_find_canonical_name_by_slug')) {
            $canonical_name = si_find_canonical_name_by_slug($service_slug);
            if ($canonical_name) {
                $tax_query[] = array(
                    'taxonomy' => 'service_type',
                    'field'    => 'name',
                    'terms'    => $canonical_name,
                );
            }
        }
        
        // Fallback: поиск по метаполю si_type
        if (!isset($canonical_name)) {
            $human = trim(str_replace('-', ' ', $service_slug));
            if ($human !== '') {
                $meta_query = array(
                    'key'     => 'si_type',
                    'value'   => $human,
                    'compare' => 'LIKE',
                );
            }
        }
    }
}

// --- Запрос услуг ---
$args = array(
    'post_type'      => 'service',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => 'si_category',
    'order'          => 'ASC',
    'tax_query'      => $tax_query,
);

if ($meta_query) {
    $args['meta_query'] = array($meta_query);
}

$query = new WP_Query($args);

// --- Функция сортировки категорий ---
function sort_service_categories($a, $b) {
    $priority_order = array(
        'голов' => 1,
        'позвоночник' => 2,
        'сердц' => 3,
        'грудн' => 4,
        'сустав' => 5,
        'сосуд' => 6,
        'живот' => 7,
        'таз' => 8
    );
    
    $a_lower = mb_strtolower($a, 'UTF-8');
    $b_lower = mb_strtolower($b, 'UTF-8');
    
    $a_is_complex = (strpos($a_lower, 'комплекс') !== false);
    $b_is_complex = (strpos($b_lower, 'комплекс') !== false);
    $a_is_additional = (strpos($a_lower, 'дополнительн') !== false || strpos($a_lower, 'комплексн') !== false);
    $b_is_additional = (strpos($b_lower, 'дополнительн') !== false || strpos($b_lower, 'комплексн') !== false);
    
    if (($a_is_complex || $a_is_additional) && !($b_is_complex || $b_is_additional)) return 1;
    if (($b_is_complex || $b_is_additional) && !($a_is_complex || $a_is_additional)) return -1;
    
    if (($a_is_complex || $a_is_additional) && ($b_is_complex || $b_is_additional)) {
        if ($a_is_complex && $b_is_additional) return -1;
        if ($a_is_additional && $b_is_complex) return 1;
        return strcasecmp($a, $b);
    }
    
    $a_priority = 999;
    $b_priority = 999;
    foreach ($priority_order as $keyword => $priority) {
        if (strpos($a_lower, $keyword) !== false) { $a_priority = $priority; break; }
    }
    foreach ($priority_order as $keyword => $priority) {
        if (strpos($b_lower, $keyword) !== false) { $b_priority = $priority; break; }
    }
    
    return ($a_priority != $b_priority) ? ($a_priority - $b_priority) : strcasecmp($a, $b);
}

// --- тенге вместо руб. ---
$kazakhstan_cities = array('almaty', 'astana', 'karaganda', 'taldykorgan');
$use_tenge = in_array($selected_city, $kazakhstan_cities, true);
$currency_symbol = $use_tenge ? '₸' : '₽';

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>
        <section class="price">
            <div class="container">
                <h1 class="price__title page-title">УСЛУГИ И ЦЕНЫ</h1>
                <div class="price__content">
                    <?php if ($query->have_posts()) : 
                        $grouped = array();
                        while ($query->have_posts()) : $query->the_post();
                            $category = get_post_meta(get_the_ID(), 'si_category', true);
                            $oblast  = get_post_meta(get_the_ID(), 'si_oblast', true);
                            $price   = get_post_meta(get_the_ID(), 'si_price', true);
                            $discount = get_post_meta(get_the_ID(), 'si_discount', true);
                            
                            if (empty($oblast)) $oblast = get_the_title();
                            $key = !empty($category) ? $category : 'Без категории';
                            if (!isset($grouped[$key])) $grouped[$key] = array();
                            $grouped[$key][] = compact('oblast', 'price', 'discount');
                        endwhile;
                        wp_reset_postdata();

                        uksort($grouped, 'sort_service_categories');

                        foreach ($grouped as $category => $items) : ?>
                            <div class="price__item">
                                <button class="price__item-category">
                                    <p class="price__item-category-title"><?php echo esc_html($category); ?></p>
                                    <div class="price__item-arrow">
                                        <svg width="16" height="9" viewBox="0 0 16 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.03 0.5L16 1.3625L8 8.5L0 1.3625L0.965 0.5L8 6.77083L15.03 0.5Z" fill="#404040"/>
                                        </svg>
                                    </div>
                                </button>
                                <div class="price__item-wrapper">
                                    <div class="price__item-head">
                                        <p>Область исследования</p>
                                        <p>Цена</p>
                                        <p>Скидка*</p>
                                    </div>
                                    <?php foreach ($items as $item) : ?>
                                        <div class="price__item-body">
                                            <p class="price__item-oblast"><?php echo esc_html($item['oblast']); ?></p>
                                            <p class="price__item-cena">
                                                <?php 
                                                    echo !empty($item['price']) ? esc_html($item['price']) . ' ' . esc_html($currency_symbol) : 'Цена не указана'; 
                                                ?>
                                            </p>
                                            <p class="price__item-skidka">
                                                <?php echo !empty($item['discount']) ? esc_html($item['discount']) . ' ' . esc_html($currency_symbol) : ''; ?>
                                            </p>
                                            <button class="price__item-button btn-blue booking-btn">
                                                <p>Записаться</p>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="#404040" />
                                                    <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040" stroke-width="1.5" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php else : ?>
                        <p>Для данного филиала данных не найдено</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="tour">
            <div class="container">
                <div class="tour__inner">
                    <img src="<?php bloginfo('template_url')?>/assets/img/3d_tour.jpg" alt="">
                    <a href="#" class="tour__content">
                        <h3 class="tour__title">
                            <span>ПРОЙДИТЕ 3D ТУР</span> 
                            <span>ПО КЛИНИКЕ</span> 
                            <span>«МРТ ЛИДЕР»</span>
                        </h3>
                        <div class="tour__play">
                            <img src="<?php bloginfo('template_url')?>/assets/img/play_video.svg" alt="">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="booking" id="booking">
    <div class="booking__overlay">
        <form class="booking__form" action="#">
            <button type="button" class="booking__close-btn" aria-label="Закрыть">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="#404040" stroke-width="2"
                        stroke-linecap="round" />
                </svg>
            </button>

            <div class="booking__form-wrapper">
                <input type="text" class="booking__form-input" placeholder="Введите имя" required>
                <input type="text" class="booking__form-input" placeholder="Введите телефон" required>
                <button class="booking__form-btn btn-blue">
                    <p>Записаться на приём</p>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.5" y="0.5" width="23" height="23" rx="11.5"
                            stroke="#404040" />
                        <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040"
                            stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <p class="booking__form-privacy">
                Нажимая на кнопку, вы автоматически соглашаетесь с
                <a href="<?php echo site_url('') ?>/privacy/">Политикой обработки персональных данных.</a>
            </p>
        </form>
    </div>
</div>
<!-- Обработчик для booking -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Обработчик клика по кнопке "Записаться" в таблице услуг ---
        // Используем делегирование события
        document.addEventListener('click', function(e) {
            if (e.target.matches('.booking-btn') || e.target.closest('.booking-btn')) {
                const button = e.target.matches('.booking-btn') ? e.target : e.target.closest('.booking-btn');
                
                // Найти родительский элемент .price__item-body
                const itemBody = button.closest('.price__item-body');
                // Найти родительский элемент .price__item для получения категории
                const itemContainer = button.closest('.price__item');
                
                if (itemBody && itemContainer) {
                    // Получить текст области исследования
                    const oblastElement = itemBody.querySelector('.price__item-oblast');
                    const oblast = oblastElement ? oblastElement.textContent.trim() : 'Не указана';
                    
                    // Получить текст категории
                    const categoryElement = itemContainer.querySelector('.price__item-category-title');
                    const category = categoryElement ? categoryElement.textContent.trim() : 'Без категории';
                    
                    // Найти форму в модальном окне
                    const modalForm = document.querySelector('#booking .booking__form');
                    if (modalForm) {
                        // Сохраняем данные в data-атрибутах формы
                        modalForm.dataset.serviceCategory = category;
                        modalForm.dataset.serviceOblast = oblast;
                    }
                }

                // Открытие модального окна (логика из вашего booking.js)
                const bookingPopup = document.getElementById('booking');
                if (bookingPopup) {
                    // Сохраняем позицию скролла
                    let scrollPosition = window.scrollY || document.documentElement.scrollTop;
                    
                    // Открываем попап
                    bookingPopup.classList.add('show');
                    
                    // Блокируем скролл основного контента
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${scrollPosition}px`;
                    document.body.style.width = '100%';
                }
            }
        });

        // --- Инициализация маски для телефона ---
        const phoneInput = document.querySelector('#booking .booking__form input[type="text"][placeholder="Введите телефон"]');
        let phoneMaskInstance = null;
        if (phoneInput) {
            phoneMaskInstance = IMask(phoneInput, {
                mask: '+{7} (000) 000-00-00',
                lazy: false,
                placeholderChar: '_'
            });
            // Опционально: сохраняем ссылку на экземпляр маски
            // phoneInput.maskInstance = phoneMaskInstance;
        }

        // --- Обработчик отправки формы в модальном окне ---
        const bookingForm = document.querySelector('#booking .booking__form');
        if (bookingForm) {
            // Предотвращаем дублирование обработчиков
            const existingListener = bookingForm.dataset.submitListenerAdded;
            if (!existingListener) {
                bookingForm.dataset.submitListenerAdded = 'true';
                
                bookingForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // Получаем значения обычных полей формы
                    const nameInput = this.querySelector('input[placeholder="Введите имя"]');
                    // Используем уже найденное поле телефона, если оно было инициализировано
                    const phoneInputElement = phoneInput || this.querySelector('input[placeholder="Введите телефон"]');

                    const name = nameInput ? nameInput.value.trim() : '';
                    
                    let phone = '';
                    if (phoneInputElement) {
                        if (phoneMaskInstance && phoneMaskInstance.unmaskedValue) {
                            phone = phoneMaskInstance.unmaskedValue;
                            if (phone.startsWith('7')) {
                                phone = '+7' + phone.substring(1);
                            } else if (phone.startsWith('8')) {
                                phone = '+7' + phone.substring(1);
                            } else {
                                phone = '+7' + phone;
                            }
                        } else {
                            // Если маска не работает, очищаем вручную
                            phone = phoneInputElement.value.replace(/[^\d\+]/g, '');
                        }
                    }

                    // Получаем данные услуги из data-атрибутов формы
                    const category = this.dataset.serviceCategory || '';
                    const oblast = this.dataset.serviceOblast || '';

                    // Проверка на заполненность обязательных полей
                    if (!name) {
                        alert('Пожалуйста, введите Ваше имя.');
                        if (nameInput) nameInput.focus();
                        return;
                    }

                    // Проверка телефона
                    const phoneDigits = phone.replace(/[^\d]/g, ''); // Оставляем только цифры
                    if (phoneDigits.length < 10 || phoneDigits.length > 11) { 
                        alert('Пожалуйста, введите корректный номер телефона (например, +7 (999) 999-99-99).');
                        if (phoneInputElement) phoneInputElement.focus();
                        return;
                    }

                    const data = new FormData();
                    data.append('action', 'send_booking_form_with_service');
                    data.append('name', name);
                    data.append('phone', phone); 
                    data.append('category', category);
                    data.append('oblast', oblast);
                    data.append('nonce', '<?php echo wp_create_nonce("booking_form_with_service_nonce"); ?>');

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: data
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(result => {
                        alert(result);
                        this.reset();
                        // Очищаем data-атрибуты после успешной отправки
                        delete this.dataset.serviceCategory;
                        delete this.dataset.serviceOblast;
                        
                        const bookingPopup = document.getElementById('booking');
                        if (bookingPopup && bookingPopup.classList.contains('show')) {
                            const form = bookingPopup.querySelector('.booking__form');
                            const closeBtn = bookingPopup.querySelector('.booking__close-btn');
                            const overlay = bookingPopup.querySelector('.booking__overlay');
                            
                            // Функция закрытия попапа
                            function closePopup() {
                                if (form) form.classList.remove('show');

                                setTimeout(() => {
                                    bookingPopup.classList.remove('show');

                                    // Возвращаем скролл
                                    document.body.style.position = '';
                                    document.body.style.top = '';
                                    document.body.style.width = '';
                                    
                                }, 500); 
                            }
                            
                            closePopup();
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка отправки:', error);
                        alert('Ошибка отправки формы. Пожалуйста, попробуйте еще раз.');
                    });
                });
            }
        }

        // --- Обработчики для закрытия модального окна ---
        const bookingPopup = document.getElementById('booking');
        if (bookingPopup) {
            const closeBtn = bookingPopup.querySelector('.booking__close-btn');
            const overlay = bookingPopup.querySelector('.booking__overlay');

            function closeModal() {
                if (bookingPopup.classList.contains('show')) {
                    const form = bookingPopup.querySelector('.booking__form');
                    
                    // Функция закрытия попапа
                    function closePopup() {
                        if (form) form.classList.remove('show');

                        setTimeout(() => {
                            bookingPopup.classList.remove('show');

                            // Возвращаем скролл
                            document.body.style.position = '';
                            document.body.style.top = '';
                            document.body.style.width = '';

                        }, 500);
                    }
                    
                    closePopup();
                }
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeModal();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    // Закрываем только если клик был именно по оверлею, а не по его содержимому
                    if (e.target === overlay) {
                        closeModal();
                    }
                });
            }

            // Закрытие по клавише Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && bookingPopup.classList.contains('show')) {
                    closeModal();
                }
            });
        }
    });
</script>

<!-- Аккордеон -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // все элементы аккордеона
        const accordionItems = document.querySelectorAll('.price__item');
        
        accordionItems.forEach(item => {
            const btn = item.querySelector('.price__item-category');
            const content = item.querySelector('.price__item-wrapper');
            
            btn.addEventListener('click', function() {
                accordionItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.price__item-category').classList.remove('active');
                        otherItem.querySelector('.price__item-wrapper').classList.remove('active');
                    }
                });
                
                item.classList.toggle('active');
                btn.classList.toggle('active');
                content.classList.toggle('active');
            });
        });
    });
</script>
<!-- Аккордеон -->
<?php get_footer(); ?>

<?php
/**
 * Функция для поиска канонического названия по slug
 */
function si_find_canonical_name_by_slug($slug) {
    // Парсим файл соответствий
    $path = plugin_dir_path(__FILE__) . '../services-importer/service_types_mapping.txt';
    $mapping = array();
    
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, ':') === false) continue;
            
            list($canonical, $variants) = explode(':', $line, 2);
            $canonical = trim($canonical);
            
            // Провряем, соответствует ли slug названию
            if (sanitize_title($canonical) === $slug) {
                return $canonical;
            }
            
            // Проверяем варианты
            $variants = array_map('trim', explode(',', $variants));
            foreach ($variants as $variant) {
                if (sanitize_title($variant) === $slug) {
                    return $canonical;
                }
            }
        }
    }
    
    return null;
}