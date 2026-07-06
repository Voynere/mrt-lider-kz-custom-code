<?php
/*
Template Name: answers
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

// --- Выбранная категория из GET-параметра ---
$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'mrt';

// --- Родительская рубрика "answers" и её дочерние ---
$parent_category = get_term_by('slug', 'answers', 'category');
$child_categories = $parent_category ? get_terms([
    'taxonomy' => 'category',
    'parent'   => $parent_category->term_id,
    'hide_empty' => false,
    'orderby'  => 'name',
    'order'    => 'ASC'
]) : array();

// --- Запрос поста: город + категория ---
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 1,
    'tax_query'      => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $selected_city,
        ),
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $selected_category,
        )
    )
);
$answers_query = new WP_Query($args);

get_header();
?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>
        <section class="answers">
            <div class="container">
                <div class="answers__inner">
                    <div class="answers__head">
                        <h1 class="answers__title page-title">ЧТО НУЖНО ЗНАТЬ?</h1>
                        <div class="answers__search">
                            <input type="text" class="answers__search-inp" placeholder="Поиск по вопросу">
                            <button class="answers__search-btn">
                                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18.7617 15.7344C19.7383 14.1979 20.3112 12.375 20.3112 10.4154C20.3112 4.9401 15.8776 0.5 10.4089 0.5C4.93359 0.5 0.5 4.9401 0.5 10.4154C0.5 15.8906 4.93359 20.3307 10.4023 20.3307C12.388 20.3307 14.237 19.7448 15.7865 18.7422L16.2357 18.4297L23.306 25.5L25.5 23.2669L18.4362 16.1966L18.7617 15.7344ZM15.9557 4.875C17.4336 6.35286 18.2474 8.31901 18.2474 10.4089C18.2474 12.4987 17.4336 14.4648 15.9557 15.9427C14.4779 17.4206 12.5117 18.2344 10.4219 18.2344C8.33203 18.2344 6.36588 17.4206 4.88802 15.9427C3.41016 14.4648 2.59635 12.4987 2.59635 10.4089C2.59635 8.31901 3.41016 6.35286 4.88802 4.875C6.36588 3.39714 8.33203 2.58333 10.4219 2.58333C12.5117 2.58333 14.4779 3.39714 15.9557 4.875Z" fill="white" />
                                </svg>
                            </button>
                        </div>
                        <div class="answers__category-mob">
                            <div class="answers__select">
                                <div class="answers__select-trigger">
                                    <span>
                                        <?php 
                                        $term_obj = get_term_by('slug', $selected_category, 'category');
                                        echo $term_obj ? esc_html($term_obj->name) : esc_html($selected_category);
                                        ?>
                                    </span>
                                    <svg width="16" height="9" viewBox="0 0 16 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.03 0.5L16 1.3625L8 8.5L0 1.3625L0.965 0.5L8 6.77083L15.03 0.5Z" fill="#404040" />
                                    </svg>
                                </div>
                                <div class="answers__select-options">
                                    <?php foreach ($child_categories as $category) : ?>
                                        <div class="answers__select-item <?php echo $selected_category === $category->slug ? 'selected' : ''; ?>" 
                                             data-value="<?php echo esc_attr($category->slug); ?>">
                                            <?php echo esc_html($category->name); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <select name="category" id="answers-category-select" class="original-select">
                                    <?php foreach ($child_categories as $category) : ?>
                                        <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($selected_category, $category->slug); ?>>
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="answers__content">
                        <div class="answers__category answers__content-item">
                            <div class="answers__category-inner">
                                <h5 class="answers__category-title">Выберете категорию вопроса</h5>
                                <ul class="answers__category-list">
                                    <?php foreach ($child_categories as $category) : ?>
                                        <li class="answers__category-item <?php echo $selected_category === $category->slug ? 'active' : ''; ?>">
                                            <a href="<?php echo esc_url(add_query_arg('category', $category->slug)); ?>">
                                                <?php echo esc_html($category->name); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button class="answers__category-button btn-blue booking-btn">
                                <p>Задать свой вопрос</p>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="#404040" />
                                    <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040" stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>

                        <?php if ($answers_query->have_posts()) : ?>
                            <?php while ($answers_query->have_posts()) : $answers_query->the_post(); ?>
                                <div class="answers__specialist answers__content-item">
                                    <div class="answers__specialist-container">
                                        <?php 
                                        $avatar = get_field('know_avatar');
                                        if ($avatar) : 
                                        ?>
                                            <img src="<?php echo esc_url($avatar); ?>" alt="" class="answers__specialist-img">
                                        <?php endif; ?>
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner left-bottom" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-top" alt="">
                                        <img src="<?php bloginfo('template_url')?>/assets/img/rating_corner.svg" class="raiting__corner right-bottom" alt="">
                                    </div>
                                    <p class="answers__specialist-info">
                                        На ваши вопросы ответит <?php the_field('know_descr'); ?>
                                    </p>
                                </div>
                                <div class="answers__tabs answers__content-item">
                                    <?php
                                    $index = 1;
                                    while (get_field('know_vopros_' . $index) && get_field('know_otvet_' . $index)) :
                                        $vopros = get_field('know_vopros_' . $index);
                                        $otvet = get_field('know_otvet_' . $index);
                                    ?>
                                        <div class="answers__tabs-item">
                                            <button class="answers__tabs-btn">
                                                <h3 class="answers__tabs-title"><?php echo esc_html($vopros); ?></h3>
                                                <svg width="16" height="9" viewBox="0 0 16 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M15.03 0.5L16 1.3625L8 8.5L0 1.3625L0.965 0.5L8 6.77083L15.03 0.5Z" fill="#404040" />
                                                </svg>
                                            </button>
                                            <div class="answers__tabs-text">
                                                <p><?php echo esc_html($otvet); ?></p>
                                            </div>
                                        </div>
                                    <?php
                                        $index++;
                                    endwhile;
                                    ?>
                                </div>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <div class="answers__tabs answers__content-item">
                                <p>Информация для выбранной категории и города не найдена.</p>
                            </div>
                        <?php endif; ?>
                    </div>
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
                <input type="text" class="booking__form-input" placeholder="Тема вопроса" required>
                <textarea name="" id="" class="textarea booking__form-input" placeholder="Текст вопроса"></textarea>
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
        const form = document.querySelector('.booking__form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const name = form.querySelector('input[placeholder="Введите имя"]').value;
                const phone = form.querySelector('input[placeholder="Введите телефон"]').value;
                const subject = form.querySelector('input[placeholder="Тема вопроса"]').value;
                const message = form.querySelector('textarea').value;

                const data = new FormData();
                data.append('action', 'send_contact_form');
                data.append('name', name);
                data.append('phone', phone);
                data.append('subject', subject);
                data.append('message', message);
                data.append('nonce', '<?php echo wp_create_nonce("contact_form_nonce"); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.text())
                .then(result => {
                    alert(result);
                    form.reset();
                })
                .catch(error => {
                    alert('Ошибка отправки формы.');
                    console.error('Ошибка:', error);
                });
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector('input[placeholder="Введите телефон"]');
        if (phoneInput) {
            const phoneMask = IMask(phoneInput, {
                mask: '+{7} (000) 000-00-00',
                lazy: false,
                placeholderChar: '_'
            });
        }
    });
</script>

<!-- обработчик изменений категории в select -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('answers-category-select');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                const category = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('category', category);
                window.location.href = url.toString();
            });
        }
    });
</script>

<?php get_footer(); ?>