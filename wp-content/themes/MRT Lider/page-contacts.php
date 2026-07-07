<?php
/*
Template Name: contacts
*/

// Получаем выбранный город из URL / cookie
$selected_city = mrt_resolve_selected_city('almaty', true);

// Запрос поста для выбранного города с рубрикой "Контакты"
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
            'terms'    => 'contacty',
        )
    )
);
$contacts_query = new WP_Query($args);
?>

<?php get_header(); ?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>

        <section class="contacts">
            <div class="container">
                <div class="contacts__inner">
                    <h1 class="contacts__title page-title"><?php echo esc_html(mrt_get_contacts_page_title($selected_city)); ?></h1>
                    <div class="contacts__map">
                        <div class="contacts__map-frame">
                            <?php
                            // ACF поле contacts_map для выбранного города (зависит от $selected_city)
                            $map_html = '';

                            // Если $contacts_query уже задан и содержит посты -используем его (без дополнительного запроса)
                            if ( isset($contacts_query) && $contacts_query instanceof WP_Query && ! empty( $contacts_query->posts ) ) {
                                $first_post = $contacts_query->posts[0];
                                $map_html = get_field( 'contacts_map', $first_post->ID );
                            } else {
                                // делаем вспомогательный запрос
                                $selected_city = isset($_COOKIE['selected_city']) ? sanitize_text_field($_COOKIE['selected_city']) : 'almaty';
                                $tmp_args = array(
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
                                            'terms'    => 'contacty',
                                        ),
                                    ),
                                );
                                $tmp_q = new WP_Query( $tmp_args );
                                if ( $tmp_q->have_posts() ) {
                                    $tmp_q->the_post();
                                    $map_html = get_field( 'contacts_map' );
                                    wp_reset_postdata();
                                }
                            }

                            // Выводим карту 
                            if ( ! empty( $map_html ) ) {
                                echo $map_html;
                            } else {
                                echo '<!-- Карта не задана для выбранного филиала -->';
                            }
                            ?>

                        </div>
                        <div class="contacts__content">
                            <?php if ($contacts_query->have_posts()) : ?>
                                <?php while ($contacts_query->have_posts()) : $contacts_query->the_post(); ?>
                                    <?php
                                    // Получаем все группы полей
                                    $addresses_group = get_field('contacts_addresses') ?: [];
                                    $phones_group = get_field('contacts_phones') ?: [];
                                    $emails_group = get_field('contacts_emails') ?: [];
                                    $opening_hours_group = get_field('contacts_opening_hours') ?: [];
                                    ?>
                                    
                                    <!-- Адреса -->
                                    <div class="contacts__content-item">
                                        <svg width="20" height="26" viewBox="0 0 20 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10.0039 0.75C11.2521 0.75 12.4641 1.00535 13.6045 1.50781C14.1529 1.74937 14.6822 2.04812 15.1768 2.39648H15.1758C15.6652 2.74068 16.126 3.13582 16.5439 3.57129L16.8506 3.90625C17.1492 4.24835 17.4242 4.61338 17.6719 4.99512C18.0054 5.50932 18.2923 6.0585 18.5234 6.62793C19.0053 7.81361 19.25 9.07275 19.25 10.3691C19.25 10.6998 19.2262 11.0479 19.1787 11.4053C19.1259 11.8027 18.7933 12.1152 18.3877 12.1152C18.3512 12.1152 18.3146 12.1133 18.2783 12.1084L18.2764 12.1074C17.832 12.0438 17.5368 11.6222 17.5947 11.1836L17.6377 10.7646C17.6471 10.6288 17.6514 10.4966 17.6514 10.3691C17.6514 8.23666 16.8538 6.23341 15.4072 4.72559L15.1299 4.45117C13.7192 3.1199 11.9139 2.39258 10.0039 2.39258C7.96237 2.39271 6.04225 3.22008 4.59473 4.72559C3.14728 6.23227 2.34917 8.23575 2.34863 10.3691L2.35742 10.7344C2.45096 12.5853 3.26814 14.8459 4.70898 17.1484L4.97461 17.5645C6.29859 19.5879 8.08057 21.6249 10.0449 23.3594C12.0477 21.6061 14.2643 18.8161 15.7236 16.1885C15.7265 16.1833 15.7299 16.1782 15.7334 16.1729C15.8755 15.9295 16.1342 15.7736 16.418 15.7734C16.5249 15.7734 16.6305 15.7959 16.7275 15.8379L16.8223 15.8867L16.9102 15.9482C17.1043 16.1045 17.2172 16.3451 17.2168 16.5977C17.2168 16.7396 17.1798 16.8796 17.1113 17.0029V17.0039C16.2515 18.5466 15.1728 20.1217 13.9922 21.5615C12.8452 22.96 11.6485 24.1762 10.5293 25.0752C10.3886 25.1885 10.218 25.25 10.0381 25.25C9.85237 25.25 9.67269 25.1824 9.53125 25.0635L9.53027 25.0625C8.32389 24.0433 7.18366 22.9261 6.1416 21.7422C5.21582 20.6897 4.38424 19.6035 3.66504 18.5068L3.36328 18.0361C2.54325 16.7191 1.90352 15.4206 1.46484 14.1768C0.994344 12.8433 0.752259 11.5617 0.75 10.3691L0.761719 9.88477C0.815076 8.75908 1.05495 7.66544 1.47656 6.62793C1.70815 6.05862 1.99377 5.50867 2.32812 4.99414C2.65849 4.48498 3.03872 4.00658 3.45703 3.57129C3.87546 3.13589 4.33625 2.74026 4.82617 2.39551L5.20312 2.14355C5.58649 1.90159 5.98765 1.68898 6.39941 1.50781C7.54079 1.00542 8.75375 0.750066 10.0039 0.75ZM10 5.08887C10.6638 5.08887 11.3081 5.22486 11.9141 5.49121C12.4998 5.74866 13.0251 6.11815 13.4756 6.58691C13.9255 7.05507 14.2791 7.60015 14.5254 8.20703C14.781 8.83512 14.9102 9.5026 14.9102 10.1885C14.9101 10.875 14.7811 11.5427 14.5254 12.1709C14.2791 12.777 13.9255 13.3229 13.4756 13.791C13.0257 14.2591 12.5004 14.6278 11.915 14.8857H11.9141C11.308 15.1521 10.6639 15.2881 10 15.2881C9.33612 15.2881 8.69191 15.1521 8.08594 14.8857H8.08496C7.57281 14.6601 7.10732 14.3494 6.69727 13.9619L6.52441 13.791C6.13063 13.3813 5.81108 12.9116 5.57227 12.3945L5.47461 12.1709C5.21895 11.5428 5.08986 10.875 5.08984 10.1885C5.08984 9.50258 5.21901 8.83511 5.47461 8.20703L5.57227 7.98242C5.81109 7.4651 6.13076 6.99655 6.52441 6.58691C6.97489 6.11817 7.50013 5.74866 8.08594 5.49121C8.69188 5.22486 9.33616 5.08887 10 5.08887ZM10 6.73242C9.17423 6.73242 8.393 7.04552 7.78125 7.62207L7.66113 7.74121C7.03471 8.39426 6.68848 9.26273 6.68848 10.1885C6.68851 11.1146 7.03479 11.9821 7.66113 12.6357C8.2889 13.2879 9.11894 13.6449 10 13.6455L10.1641 13.6406C10.9291 13.6015 11.6477 13.2931 12.2188 12.7549L12.3389 12.6357C12.9651 11.9828 13.3119 11.1147 13.3125 10.1885L13.3086 10.0156C13.2705 9.21236 12.9703 8.46063 12.4531 7.86621L12.3389 7.74121C11.711 7.08892 10.8814 6.73242 10 6.73242Z"
                                                fill="#A99E98" stroke="#A99E98" stroke-width="0.5" />
                                        </svg>
                                        <h4 class="contacts__content-title">Наш адрес:</h4>
                                        <?php
                                        $address_index = 1;
                                        while ($address_index <= 10) {
                                            $field_key = 'contacts_address_' . $address_index;
                                            if (!empty($addresses_group[$field_key])) {
                                                echo '<p class="contacts__content-text">' . esc_html($addresses_group[$field_key]) . '</p>';
                                            }
                                            $address_index++;
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Телефоны -->
                                    <div class="contacts__content-item">
                                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M3.71582 0.801758C5.05789 -0.487253 7.34183 -0.465528 8.66211 0.855469L11.0449 3.24121C12.4234 4.62144 12.4234 6.86684 11.0449 8.24707L9.67188 9.62207L12.4961 12.4512L15.3564 15.3164L16.7314 13.9414C17.3981 13.273 18.2855 12.9043 19.2305 12.9043C20.1754 12.9043 21.0639 13.273 21.7314 13.9404L24.1143 16.3281C24.7818 16.9957 25.1504 17.885 25.1504 18.8301C25.1504 19.745 24.8039 20.6074 24.1758 21.2676C23.844 21.7149 22.1288 23.8089 18.9229 24.1123C18.6631 24.1377 18.3994 24.1504 18.1357 24.1504C14.7207 24.1503 10.9693 22.1047 6.96875 18.1172C6.95915 18.1083 6.94878 18.1002 6.93945 18.0908C4.82614 15.9748 3.23939 13.904 2.22656 11.9355C1.97197 11.4407 2.16576 10.8332 2.66016 10.5781C3.15654 10.3231 3.76192 10.5188 4.0166 11.0127C4.92365 12.7759 6.37105 14.6645 8.3252 16.626L9.0498 17.3311C12.6548 20.7394 15.9828 22.3616 18.7344 22.1055C20.0247 21.9833 20.9818 21.4728 21.6182 20.9902C22.2429 20.5165 22.5561 20.0717 22.5674 20.0557L22.5693 20.0537C22.6045 20.0014 22.6448 19.9509 22.6914 19.9053C22.9778 19.6187 23.1357 19.2362 23.1357 18.8301C23.1357 18.4237 22.9775 18.0416 22.6904 17.7539V17.7529L20.3076 15.3672C20.0205 15.0797 19.6383 14.9219 19.2305 14.9219C18.8236 14.9219 18.4425 15.0788 18.1562 15.3662V15.3672L16.0693 17.4551C15.6757 17.849 15.0392 17.8489 14.6455 17.4551L11.0713 13.877C11.0679 13.8735 11.0649 13.8697 11.0615 13.8662L7.53516 10.3359C7.3459 10.1464 7.24024 9.89018 7.24023 9.62305C7.24023 9.35623 7.34558 9.09906 7.53516 8.91016L9.62109 6.82227C10.214 6.22858 10.214 5.26166 9.62109 4.66797L7.23828 2.28223C6.66505 1.70926 5.65989 1.7086 5.08691 2.28223C5.04211 2.32708 4.9923 2.36607 4.94141 2.40137C4.85712 2.46008 2.52397 4.12832 2.90723 7.58203C2.96856 8.13526 2.57065 8.63281 2.01758 8.69434L2.01855 8.69531C1.46914 8.75902 0.96766 8.35789 0.90625 7.80469C0.427349 3.49605 3.16509 1.2138 3.71582 0.801758Z"
                                                fill="#A99E98" stroke="#A99E98" stroke-width="0.3" />
                                        </svg>
                                        <h4 class="contacts__content-title">Телефоны:</h4>
                                        <?php
                                        $phone_index = 1;
                                        while ($phone_index <= 10) {
                                            $field_key = 'contacts_phone_' . $phone_index;
                                            if (!empty($phones_group[$field_key])) {
                                                echo '<p class="contacts__content-text">' . esc_html($phones_group[$field_key]) . '</p>';
                                            }
                                            $phone_index++;
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="contacts__content-item">
                                        <svg width="24" height="20" viewBox="0 0 24 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M23.0771 6.90039C23.3509 6.90046 23.6117 7.01788 23.8027 7.22461C23.9936 7.4314 24.0996 7.71073 24.0996 8V16C24.0989 16.8194 23.798 17.6062 23.2607 18.1875C22.7239 18.7697 21.9938 19.0989 21.2305 19.0996H2.76953C2.00616 19.0989 1.27609 18.7697 0.739258 18.1875C0.202001 17.6062 -0.0989435 16.8194 -0.0996094 16V8C-0.0996094 7.71073 0.00638158 7.4314 0.197266 7.22461C0.388309 7.01788 0.649089 6.90046 0.922852 6.90039C1.19681 6.90039 1.4583 7.01766 1.64941 7.22461C1.8403 7.4314 1.94629 7.71073 1.94629 8V16C1.94629 16.2412 2.03509 16.4714 2.19043 16.6396C2.34548 16.8074 2.55412 16.9004 2.76953 16.9004H21.2305C21.4459 16.9004 21.6545 16.8074 21.8096 16.6396C21.9649 16.4714 22.0537 16.2412 22.0537 16V8C22.0537 7.71073 22.1597 7.4314 22.3506 7.22461C22.5417 7.01766 22.8032 6.90039 23.0771 6.90039ZM21.2305 0.900391C21.9938 0.901119 22.7239 1.23026 23.2607 1.8125C23.4913 2.06235 23.6815 2.35174 23.8232 2.66895L23.8242 2.66992C23.9247 2.89778 23.9486 3.15569 23.8936 3.40039C23.8453 3.61463 23.739 3.8089 23.5869 3.95703L23.5186 4.01758L12.6172 12.8779C12.4402 13.0218 12.2234 13.0996 12 13.0996C11.7766 13.0996 11.5598 13.0218 11.3828 12.8779L0.481445 4.01758C0.293224 3.86382 0.161581 3.64529 0.106445 3.40039C0.0513533 3.15569 0.0752697 2.89778 0.175781 2.66992L0.176758 2.66895C0.318531 2.35174 0.508745 2.06235 0.739258 1.8125C1.27609 1.23026 2.00616 0.901119 2.76953 0.900391H21.2305ZM12 10.6201L21.209 3.09961H2.79102L12 10.6201Z"
                                                fill="#A99E98" stroke="#A99E98" stroke-width="0.2" />
                                        </svg>
                                        <h4 class="contacts__content-title">E-mail:</h4>
                                        <?php
                                        $email_index = 1;
                                        while ($email_index <= 10) {
                                            $field_key = 'contacts_email_' . $email_index;
                                            if (!empty($emails_group[$field_key])) {
                                                echo '<p class="contacts__content-text">' . esc_html($emails_group[$field_key]) . '</p>';
                                            }
                                            $email_index++;
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Часы работы -->
                                    <div class="contacts__content-item">
                                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.5 0.5V7.66699C13.4999 8.06574 13.1761 8.38867 12.7773 8.38867C12.379 8.3884 12.0558 8.06579 12.0557 7.66699V1.98633C9.83705 2.1765 7.71893 3.0322 5.98633 4.4541C4.00935 6.07671 2.6562 8.33521 2.15723 10.8438C1.65838 13.3522 2.04441 15.9563 3.25 18.2119C4.45569 20.4675 6.40673 22.2351 8.76953 23.2139C11.1324 24.1925 13.7617 24.3223 16.209 23.5801C18.6565 22.8376 20.7714 21.2692 22.1924 19.1426C23.6134 17.0159 24.2527 14.4615 24.002 11.916C23.7592 9.45211 22.6974 7.14304 20.9893 5.35742L20.8135 5.17871L20.7988 5.16406L20.7852 5.14746L20.7568 5.1123L20.7393 5.0918L20.7246 5.06934C20.5394 4.78824 20.5712 4.40757 20.8174 4.16113C21.0641 3.91449 21.4447 3.8833 21.7256 4.06836L21.748 4.08301L21.7686 4.10059L21.8037 4.12891L21.8242 4.14648L21.8428 4.16504L22.0244 4.35059L22.0283 4.35449C23.9623 6.37407 25.1648 8.98647 25.4395 11.7744C25.7229 14.6523 25 17.5398 23.3936 19.9443C21.7869 22.3488 19.3961 24.1224 16.6289 24.9619C13.8616 25.8014 10.8885 25.6554 8.2168 24.5488C5.54511 23.4421 3.33981 21.443 1.97656 18.8926C0.61337 16.3422 0.176079 13.3979 0.740234 10.5615C1.30441 7.7252 2.83495 5.17251 5.07031 3.33789C7.30577 1.50327 10.1081 0.500022 13 0.5H13.5ZM18.2666 5.37793C18.5486 5.09589 19.0061 5.09636 19.2881 5.37793C19.5701 5.65998 19.5701 6.11737 19.2881 6.39941L13.0664 12.6221C12.7845 12.9039 12.327 12.9041 12.0449 12.6221C11.763 12.3401 11.763 11.8826 12.0449 11.6006L18.2666 5.37793Z"
                                                fill="#A99E98" stroke="#A99E98" />
                                        </svg>
                                        <h4 class="contacts__content-title">Часы работы:</h4>
                                        <?php
                                        $hours_index = 1;
                                        while ($hours_index <= 10) {
                                            $field_key = 'contacts_opening_hours_' . $hours_index;
                                            if (!empty($opening_hours_group[$field_key])) {
                                                echo '<p class="contacts__content-text">' . esc_html($opening_hours_group[$field_key]) . '</p>';
                                            }
                                            $hours_index++;
                                        }
                                        
                                        // Вывод дней работы
                                        if (!empty($opening_hours_group['contacts_opening_days'])) {
                                            echo '<p class="contacts__content-text">' . esc_html($opening_hours_group['contacts_opening_days']) . '</p>';
                                        }
                                        ?>
                                    </div>
                                <?php endwhile; ?>
                                <?php wp_reset_postdata(); ?>
                            <?php else : ?>
                                <div class="contacts__content-item">
                                    <p class="contacts__content-text">Контакты для выбранного города не найдены</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="form">
            <div class="container">
                <div class="form__inner">
                    <form action="#">
                        <input type="text" class="form__inp" placeholder="Введите имя" required>
                        <input type="text" class="form__inp" placeholder="Введите телефон" required>
                        <input type="text" class="form__inp" placeholder="Тема вопроса" required>
                        <textarea name="" id="" class="form__inp textarea" placeholder="Текст вопроса"></textarea>
                        <button class="form__btn">
                            <p>Записаться на приём</p>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="#404040" />
                                <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="#404040"
                                    stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        </button>
                        <p class="form__privacy">
                            Нажимая на кнопку, вы автоматически соглашаетесь с
                            <a href="<?php echo site_url('') ?>/privacy/">Политикой обработки персональных данных.</a>
                        </p>
                    </form>
                </div>
            </div>
        </section>

        <section class="photos">
            <div class="container">
                <div class="photos__inner">
                    <h2 class="page-title">НАШ ЦЕНТР</h2>
                    <div class="photos__content">
                        <?php
                        // Запрашиваем пост контактов для выбранного города
                        $photos_args = array(
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
                                    'terms'    => 'contacty',
                                )
                            )
                        );
                        $photos_query = new WP_Query( $photos_args );

                        // Временный массив для хранения обработанных изображений
                        $centre_photos = array();

                        if ( $photos_query->have_posts() ) :
                            while ( $photos_query->have_posts() ) : $photos_query->the_post();
                                // Получаем группу полей с фотографиями центра
                                $photos_group = get_field( 'photos_centre' ) ?: array();

                                // Проверяем до 8 полей и собираем реальные URL/миниатюры/alt
                                for ( $i = 1; $i <= 8; $i++ ) {
                                    $field_key = 'photos_centre_' . $i;
                                    if ( empty( $photos_group[ $field_key ] ) ) {
                                        continue;
                                    }

                                    $item = $photos_group[ $field_key ];

                                    $full_url  = '';
                                    $thumb_url = '';
                                    $alt_text  = '';

                                    // ID (число)
                                    if ( is_int( $item ) || ctype_digit( (string) $item ) ) {
                                        $attach_id = (int) $item;
                                        $full_url  = wp_get_attachment_image_url( $attach_id, 'full' );
                                        // Подберите размер миниатюры под вашу вёрстку — 'medium' / 'thumbnail' / 'large'
                                        $thumb_url = wp_get_attachment_image_url( $attach_id, 'medium' ) ?: $full_url;
                                        $alt_text  = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
                                    }
                                    // Если ACF вернул массив
                                    elseif ( is_array( $item ) ) {
                                        if ( ! empty( $item['url'] ) ) {
                                            $full_url = $item['url'];
                                        }
                                        if ( ! empty( $item['sizes']['medium'] ) ) {
                                            $thumb_url = $item['sizes']['medium'];
                                        } elseif ( ! empty( $item['sizes']['thumbnail'] ) ) {
                                            $thumb_url = $item['sizes']['thumbnail'];
                                        } else {
                                            $thumb_url = $full_url;
                                        }
                                        if ( ! empty( $item['alt'] ) ) {
                                            $alt_text = $item['alt'];
                                        } elseif ( ! empty( $item['ID'] ) ) {
                                            $alt_text = get_post_meta( $item['ID'], '_wp_attachment_image_alt', true );
                                        }
                                    }
                                    // Прямая строка — URL
                                    elseif ( is_string( $item ) ) {
                                        $full_url  = $item;
                                        $thumb_url = $item;
                                    }

                                    // Если получили URL — добавляем в массив
                                    if ( $full_url ) {
                                        $centre_photos[] = array(
                                            'full'  => esc_url_raw( $full_url ),
                                            'thumb' => esc_url_raw( $thumb_url ?: $full_url ),
                                            'alt'   => sanitize_text_field( $alt_text ?: get_the_title() ),
                                        );
                                    }
                                } // end for
                            endwhile;
                            wp_reset_postdata();
                        endif;

                        // Обрезаем до 5 изображений
                        if ( ! empty( $centre_photos ) ) :
                            $centre_photos = array_slice( $centre_photos, 0, 5 );

                            // Разбиваем на верх/низ (3 + 2)
                            $top_photos    = array_slice( $centre_photos, 0, 3 );
                            $bottom_photos = array_slice( $centre_photos, 3, 2 );
                            ?>
                            <div class="photos__top">
                                <?php foreach ( $top_photos as $index => $img ) : ?>
                                    <a href="<?php echo esc_url( $img['full'] ); ?>"
                                    class="photos__item"
                                    data-fancybox="photos-gallery">
                                        <img src="<?php echo esc_url( $img['thumb'] ); ?>"
                                            alt="<?php echo esc_attr( $img['alt'] ); ?>"
                                            class="photos__item-img" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <div class="photos__bottom">
                                <?php foreach ( $bottom_photos as $index => $img ) : ?>
                                    <a href="<?php echo esc_url( $img['full'] ); ?>"
                                    class="photos__item"
                                    data-fancybox="photos-gallery">
                                        <img src="<?php echo esc_url( $img['thumb'] ); ?>"
                                            alt="<?php echo esc_attr( $img['alt'] ); ?>"
                                            class="photos__item-img" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <div class="photos__more">
                                <a href="" class="photos__more-link">
                                    Ещё фото
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="photos__empty">
                                <p>Фотографий центра для выбранного города не найдено.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php get_template_part('template-parts/tour-or-animals-map'); ?>

    </div>
</main>

<!-- Обработчик формы -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Обработчик для формы .form form ---
        const contactForm = document.querySelector('.form form');
        if (contactForm) {
            // --- Инициализация маски для телефона в contactForm ---
            const contactPhoneInput = contactForm.querySelector('input[placeholder="Введите телефон"]');
            let contactPhoneMaskInstance = null;
            if (contactPhoneInput) {
                contactPhoneMaskInstance = IMask(contactPhoneInput, {
                    mask: '+{7} (000) 000-00-00',
                    lazy: false,
                    placeholderChar: '_'
                });
            }

            contactForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const nameInput = contactForm.querySelector('input[placeholder="Введите имя"]');
                const phoneInputElement = contactPhoneInput; // Используем уже найденное поле
                const subjectInput = contactForm.querySelector('input[placeholder="Тема вопроса"]');
                const messageInput = contactForm.querySelector('textarea');

                const name = nameInput ? nameInput.value.trim() : '';
                const subject = subjectInput ? subjectInput.value.trim() : '';
                const message = messageInput ? messageInput.value.trim() : '';

                // Усиленная обработка номера телефона
                let phone = '';
                if (phoneInputElement) {
                    if (contactPhoneMaskInstance && contactPhoneMaskInstance.unmaskedValue) {
                        // Получаем "сырое" значение из маски
                        phone = contactPhoneMaskInstance.unmaskedValue;
                        // Нормализуем: заменяем 8 на +7, добавляем +7 если нужно
                        if (phone.startsWith('7')) {
                            phone = '+7' + phone.substring(1);
                        } else if (phone.startsWith('8')) {
                            phone = '+7' + phone.substring(1);
                        } else {
                            phone = '+7' + phone;
                        }
                    } else {
                        // Если маска не инициализирована, очищаем вручную
                        phone = phoneInputElement.value.replace(/[^\d\+]/g, '');
                    }
                }

                // --- Проверки ---
                if (!name) {
                    alert('Пожалуйста, введите Ваше имя.');
                    if (nameInput) nameInput.focus();
                    return;
                }

                // Проверка телефона: убедимся, что это российский номер
                const phoneDigits = phone.replace(/[^\d]/g, ''); // Оставляем только цифры
                if (phoneDigits.length < 10 || phoneDigits.length > 11) {
                    alert('Пожалуйста, введите корректный номер телефона (например, +7 (999) 999-99-99).');
                    if (phoneInputElement) phoneInputElement.focus();
                    return;
                }

                if (!subject) {
                    alert('Пожалуйста, введите тему вопроса.');
                    if (subjectInput) subjectInput.focus();
                    return;
                }

                if (!message) {
                    alert('Пожалуйста, введите текст сообщения.');
                    if (messageInput) messageInput.focus();
                    return;
                }
                // --- Конец проверок ---

                const data = new FormData();
                data.append('action', 'send_contact_form');
                data.append('name', name);
                data.append('phone', phone); // Отправляем нормализованный номер
                data.append('subject', subject);
                data.append('message', message);
                data.append('nonce', '<?php echo wp_create_nonce("contact_form_nonce"); ?>');

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
                    contactForm.reset();
                })
                .catch(error => {
                    console.error('Ошибка отправки формы контактов:', error);
                    alert('Ошибка отправки формы. Пожалуйста, попробуйте еще раз.');
                });
            });
        }
    });
</script>
<?php get_footer(); ?>