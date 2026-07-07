<?php
/*
Template Name: tax
*/
?>

<?php get_header(); ?>

<main class="main">
    <div class="main-background">
        <?php 
            if (!is_front_page()) {
                custom_breadcrumbs();
            }
        ?>

            <section class="tax">
                <div class="container">
                    <div class="tax__inner">
                        <h1 class="tax__title page-title">
                            ЗАЯВКА НА СПРАВКУ <br> ДЛЯ НАЛОГОВОГО ВЫЧЕТА
                        </h1>
                        <form class="tax__form">
                            <div class="tax__head">
                                <p class="tax__section-title">Кем приходится налогоплательщик пациенту?</p>
                                <div class="tax__radio-group">
                                    <label class="tax__radio-item">
                                        <input type="radio" name="relation" value="patient" class="tax__radio-input" checked>
                                        <span class="tax__radio-label">Я и есть пациент</span>
                                    </label>
                                    <label class="tax__radio-item">
                                        <input type="radio" name="relation" value="representative" class="tax__radio-input">
                                        <span class="tax__radio-label">Я — законный представитель пациента</span>
                                    </label>
                                </div>
                            </div>

                            <div class="tax__section tax__section_patient">
                                <h3 class="tax__section-title">Данные пациента</h3>
                                <div class="tax__field-group">
                                    <div class="tax__field">
                                        <input type="text" name="patient_city" placeholder="Город" class="tax__input" required>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="patient_inn" placeholder="ИНН" class="tax__input" required>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="patient_name" placeholder="ФИО" class="tax__input" required>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="patient_dob" placeholder="Дата рождения" class="tax__input" required>
                                        <button type="button" class="tax__calendar-icon"></button>
                                    </div>
                                    <div class="tax__field-group tax__field-group_inline">
                                        <div class="tax__field">
                                            <input type="text" name="patient_doc_type" placeholder="Документ, удостоверяющий личность" class="tax__input" required>
                                        </div>
                                        <div class="tax__field">
                                            <input type="text" name="patient_doc_number" placeholder="Серия и номер" class="tax__input" required>
                                        </div>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="patient_doc_date" placeholder="Дата выдачи документа, удостоверяющего личность" class="tax__input" required>
                                        <button type="button" class="tax__calendar-icon"></button>
                                    </div>
                                </div>
                            </div>

                            <div class="tax__section tax__section_taxpayer">
                                <h3 class="tax__section-title">Данные налогоплательщика (кому выдаётся справка)</h3>
                                <div class="tax__field-group">
                                    <div class="tax__field">
                                        <input type="text" name="taxpayer_name" placeholder="ФИО" class="tax__input" required>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="taxpayer_dob" placeholder="Дата рождения" class="tax__input" required>
                                        <button type="button" class="tax__calendar-icon"></button>
                                    </div>
                                    <div class="tax__field-group tax__field-group_inline">
                                        <div class="tax__field">
                                            <input type="text" name="taxpayer_doc_type" placeholder="Документ, удостоверяющий личность" class="tax__input"required>
                                        </div>
                                        <div class="tax__field">
                                            <input type="text" name="taxpayer_doc_number" placeholder="Серия и номер" class="tax__input" required>
                                        </div>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="taxpayer_doc_date" placeholder="Дата выдачи документа, удостоверяющего личность" class="tax__input" required>
                                        <button type="button" class="tax__calendar-icon"></button>
                                    </div>
                                    <div class="tax__field-group tax__field-group_inline">
                                        <div class="tax__field">
                                            <input type="text" name="taxpayer_inn" placeholder="ИНН" class="tax__input" required>
                                        </div>
                                        <div class="tax__field">
                                            <input type="text" name="taxpayer_relation_to_patient" placeholder="Кем налогоплательщик приходится пациенту (отец / мать)" class="tax__input" required>
                                        </div>
                                    </div>
                                    <div class="tax__field">
                                        <input type="text" name="taxpayer_phone" placeholder="Контактный номер телефона" class="tax__input" required>
                                    </div>
                                    <div class="tax__field">
                                        <textarea name="taxpayer_comment" placeholder="Дополнительная информация (комментарий)" class="tax__textarea"></textarea>
                                    </div>
                                    <div class="tax__field tax__field-years">
                                        <p class="tax__years-label">За какие годы нужна справка (вы можете выбрать несколько вариантов)</p>
                                        <div class="tax__checkbox-group">
                                            <label class="tax__checkbox-item">
                                                <input type="checkbox" name="years[]" value="2022" class="tax__checkbox-input">
                                                <span class="tax__checkbox-label">2022</span>
                                            </label>
                                            <label class="tax__checkbox-item">
                                                <input type="checkbox" name="years[]" value="2023" class="tax__checkbox-input">
                                                <span class="tax__checkbox-label">2023</span>
                                            </label>
                                            <label class="tax__checkbox-item">
                                                <input type="checkbox" name="years[]" value="2024" class="tax__checkbox-input">
                                                <span class="tax__checkbox-label">2024</span>
                                            </label>
                                            <label class="tax__checkbox-item">
                                                <input type="checkbox" name="years[]" value="2025" class="tax__checkbox-input">
                                                <span class="tax__checkbox-label">2025</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tax__actions">
                                <button type="submit" class="tax__submit-button btn-blue">
                                    Отправить заявку
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0.5" y="0.5" width="23" height="23" rx="11.5" stroke="white" />
                                        <path d="M9.08108 8H16V14.9189M14.8108 9.18919L8 16" stroke="white" stroke-width="1.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <div class="tax__disclaimer">
                                <p class="tax__disclaimer-text">
                                    Нажимая на кнопку, вы автоматически соглашаетесь с
                                    <a href="<?php echo site_url('') ?>/privacy/" class="tax__disclaimer-link">Политикой обработки персональных данных</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <div class="container">
                <div class="tax-text">
                    <p>
                        ВЫ МОЖЕТЕ ОФОРМИТЬ НАЛОГОВЫЙ ВЫЧЕТ ЗА ЛЕЧЕНИЕ В МРТ ЛИДЕР. СРОК ГОТОВНОСТИ СПРАВКИ — 14 ДНЕЙ.
                    </p>
                </div>
            </div>

            <div class="tax-info">
                <div class="container">
                    <div class="tax-info__inner">
                        <div class="tax-info__grid">
                            <div class="tax-info__item tax-info__item_image">
                                <img src="<?php bloginfo('template_url'); ?>/assets/img/tax-info.jpg">
                            </div>
                            <div class="tax-info__item tax-info__item_text">
                                <div class="tax-info__header">
                                    <svg width="38" height="40" viewBox="0 0 38 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M29.2932 16.3158C31.6682 14.9817 33.25 12.6525 33.25 10C33.25 5.86417 29.4139 2.5 24.7 2.5C23.8858 2.5 23.0802 2.60667 22.2955 2.81083C20.5532 1.09167 18.0224 0 15.2 0C9.9617 0 5.7 3.73833 5.7 8.33333C5.7 11.1925 7.35205 13.72 9.861 15.2208C4.23985 16.2342 0 20.6075 0 25.8333V40H30.4V36.6667H38V26.6667C38 21.805 34.3302 17.6825 29.2932 16.3158ZM23.4859 4.26667C23.8868 4.2 24.2924 4.16667 24.7 4.16667C28.367 4.16667 31.35 6.78417 31.35 10C31.35 13.2158 28.367 15.8333 24.7 15.8333C23.3956 15.8333 22.1397 15.5 21.0653 14.88C21.0795 14.87 21.091 14.8592 21.1052 14.8492C21.9203 14.2792 22.6242 13.595 23.1904 12.8233C23.2123 12.7933 23.2323 12.7617 23.2541 12.7317C23.4137 12.5075 23.5619 12.2767 23.6968 12.0383C23.7215 11.9942 23.7462 11.9492 23.7699 11.905C23.8934 11.6783 24.0046 11.4458 24.1053 11.2083C24.1281 11.155 24.1509 11.1017 24.1727 11.0475C24.264 10.8175 24.3409 10.5833 24.4093 10.3442C24.4273 10.2825 24.4473 10.2208 24.4634 10.1592C24.5233 9.92417 24.5698 9.685 24.6059 9.4425C24.6164 9.3775 24.6316 9.31417 24.6392 9.24917C24.6772 8.94917 24.7 8.64333 24.7 8.33333C24.7 8.00333 24.6725 7.67917 24.6297 7.35917C24.6164 7.25833 24.5945 7.15917 24.5774 7.05833C24.5375 6.83333 24.49 6.61167 24.4302 6.39333C24.4017 6.28833 24.3713 6.18417 24.338 6.08083C24.2601 5.83833 24.1699 5.60167 24.0682 5.36833C24.0407 5.30583 24.0189 5.24167 23.9894 5.17917C23.845 4.87 23.6797 4.57083 23.4954 4.28167C23.4916 4.27667 23.4888 4.27167 23.4859 4.26667ZM7.6 8.33333C7.6 4.6575 11.0096 1.66667 15.2 1.66667C17.5532 1.66667 19.6583 2.61 21.0539 4.08667L21.1147 4.15583C21.1612 4.20583 21.2021 4.26 21.2467 4.31083C21.7293 4.86833 22.0989 5.47583 22.3592 6.11667C22.3772 6.16083 22.3972 6.20333 22.4134 6.2475C22.5264 6.545 22.611 6.85083 22.6746 7.16083C22.6879 7.22667 22.6984 7.29333 22.7098 7.36C22.763 7.68083 22.8 8.00417 22.8 8.33333C22.8 8.60667 22.7762 8.87667 22.7382 9.14417C22.7297 9.2075 22.7164 9.26917 22.7059 9.33167C22.667 9.55167 22.6185 9.76917 22.5549 9.98333C22.5416 10.0292 22.5293 10.075 22.515 10.1208C22.3431 10.6567 22.0979 11.1708 21.7826 11.6508C21.7759 11.66 21.7693 11.6692 21.7635 11.6783C21.6106 11.9083 21.4415 12.1292 21.2582 12.3425C21.2411 12.3625 21.2239 12.3833 21.2069 12.4033C20.8193 12.8433 20.368 13.2417 19.8607 13.5883C19.8341 13.6067 19.8065 13.6233 19.7799 13.6417C19.5386 13.8017 19.2869 13.9517 19.0228 14.0867C18.9924 14.1025 18.9629 14.1192 18.9326 14.135L18.7853 14.21C17.7156 14.7125 16.4958 15 15.2 15C11.0096 15 7.6 12.0092 7.6 8.33333ZM28.5 38.3333H1.9V25.8333C1.9 20.7792 6.58825 16.6667 12.35 16.6667H18.05C19.4702 16.6667 20.8249 16.9183 22.0609 17.3708L22.2433 17.4425C22.3487 17.4833 22.4504 17.53 22.5539 17.5742C22.8361 17.6925 23.1087 17.8217 23.3748 17.96C23.4954 18.0233 23.618 18.085 23.7358 18.1525C23.8621 18.2242 23.9837 18.3 24.1062 18.3767C24.2326 18.4558 24.357 18.5375 24.4796 18.6217C24.5889 18.6967 24.6971 18.7708 24.8026 18.8492C24.9632 18.9692 25.118 19.0942 25.27 19.2225C25.3897 19.3233 25.5065 19.4267 25.6206 19.5325C25.7488 19.6517 25.8761 19.7717 25.9977 19.8958C26.088 19.9892 26.1744 20.0842 26.2599 20.18C26.3568 20.2883 26.4499 20.3983 26.542 20.5108C26.6256 20.6125 26.7093 20.7142 26.7881 20.8192C26.8964 20.9633 26.9961 21.1125 27.0949 21.2617C27.1985 21.4183 27.2944 21.5775 27.3875 21.7392C27.4559 21.8575 27.5224 21.9767 27.5851 22.0983C27.6469 22.2183 27.7049 22.3408 27.76 22.4633C27.8188 22.5942 27.873 22.7267 27.9262 22.86C27.9699 22.9717 28.0174 23.0825 28.0564 23.1958C28.1362 23.4283 28.2055 23.6642 28.2644 23.9033C28.2872 23.995 28.3033 24.0892 28.3233 24.1817C28.3622 24.3667 28.3955 24.5533 28.4212 24.7417C28.4335 24.83 28.4458 24.9183 28.4544 25.0075C28.48 25.28 28.5 25.555 28.5 25.8333V38.3333ZM36.1 35H30.4V25.8333C30.4 25.4375 30.3725 25.0475 30.325 24.6625C30.3136 24.57 30.2917 24.48 30.2774 24.3883C30.2318 24.0925 30.1796 23.7992 30.1074 23.5117C30.0827 23.415 30.0504 23.3208 30.0229 23.225C29.9431 22.945 29.8556 22.6675 29.7521 22.3967C29.716 22.3025 29.6742 22.21 29.6353 22.1167C29.5241 21.85 29.4035 21.5883 29.2695 21.3317C29.2201 21.2383 29.1688 21.1467 29.1165 21.0542C28.9759 20.805 28.8258 20.5608 28.6644 20.3225C28.6026 20.2317 28.5409 20.1417 28.4772 20.0517C28.3081 19.8183 28.1285 19.5925 27.9404 19.3708C27.8692 19.2875 27.8008 19.2025 27.7276 19.1208C27.5205 18.8917 27.3011 18.6733 27.075 18.46C27.0085 18.3975 26.9477 18.3325 26.8793 18.2708C26.5791 18.0017 26.2665 17.7433 25.9359 17.5025C31.5656 17.6358 36.1 21.6958 36.1 26.6667V35Z" fill="#6180A1"/>
                                    </svg>
                                    <h3 class="tax-info__title">
                                        МОЖНО ЛИ ПОЛУЧИТЬ ВЫЧЕТ, ЕСЛИ Я ПЛАТИЛ ЗА ЛЕЧЕНИЕ ДРУГОГО ЧЕЛОВЕКА?
                                    </h3>
                                </div>
                                <p class="tax-info__text">
                                    Вы можете получить налоговый вычет, если платили за лечение своих детей (до 18 лет),
                                    родителей или супруга, и платежные документы оформлены на вас.
                                </p>
                            </div>
                        </div>
        
                        <div class="tax-info__grid">
                            <div class="tax-info__item tax-info__item_text">
                                <div class="tax-info__header">
                                    <svg width="42" height="40" viewBox="0 0 42 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M32.4258 9.99414C33.4388 9.81548 34.4887 9.907 35.4414 10.2568C36.3942 10.6068 37.2041 11.1983 37.7715 11.9512C38.3384 12.7035 38.6386 13.5844 38.6387 14.4824C38.637 15.6857 38.0981 16.8468 37.127 17.708C36.1548 18.57 34.8305 19.059 33.4443 19.0605L33.0586 19.0479C32.1613 18.9889 31.2954 18.7247 30.5469 18.2812C29.6919 17.7746 29.0299 17.0578 28.6406 16.2246C28.2516 15.3918 28.1509 14.4774 28.3486 13.5957C28.5465 12.7136 29.0361 11.8984 29.7607 11.2559C30.4857 10.6132 31.4127 10.1728 32.4258 9.99414ZM33.4443 12.1641C32.9397 12.1641 32.4441 12.2969 32.0205 12.5479C31.5969 12.7989 31.2618 13.1584 31.0625 13.585C30.8629 14.0123 30.8098 14.4853 30.9121 14.9414C31.0144 15.397 31.2659 15.8109 31.6289 16.1328C31.9917 16.4544 32.4508 16.6704 32.9463 16.7578C33.4417 16.8451 33.9556 16.8009 34.4238 16.6289C34.8921 16.4569 35.2968 16.1634 35.584 15.7822C35.8715 15.4006 36.0273 14.9482 36.0273 14.4824C36.0265 13.8573 35.7466 13.2647 35.2598 12.833C34.7738 12.4022 34.1201 12.1647 33.4443 12.1641ZM8.16309 0.352539C9.32696 0.14729 10.5331 0.252263 11.6279 0.654297C12.7229 1.05645 13.6548 1.73621 14.3076 2.60254C14.9192 3.41416 15.2613 4.35635 15.3018 5.32324L15.3057 5.51758C15.3038 6.90351 14.6822 8.23916 13.5654 9.22949C12.4474 10.2208 10.9255 10.7827 9.33301 10.7842C8.14571 10.7841 6.98709 10.4721 6.00391 9.88965C5.02079 9.30718 4.25969 8.48185 3.81152 7.52246C3.36375 6.56383 3.24697 5.51133 3.47461 4.49609C3.70249 3.48025 4.26617 2.54174 5.09961 1.80273C5.93331 1.06358 6.99914 0.55783 8.16309 0.352539ZM9.33301 2.50879C8.67459 2.50885 8.02902 2.68205 7.47754 3.00879C6.9259 3.33564 6.49067 3.80276 6.23242 4.35547C5.97391 4.90886 5.90575 5.52043 6.03809 6.11035C6.17036 6.69979 6.49597 7.23693 6.96777 7.65527C7.43919 8.07319 8.03646 8.35471 8.68262 8.46875C9.32904 8.58276 9.99993 8.52498 10.6104 8.30078C11.2208 8.07655 11.7474 7.69478 12.1201 7.2002C12.493 6.70524 12.6943 6.1196 12.6943 5.51758V5.5166C12.6932 4.70893 12.3307 3.94177 11.6982 3.38086C11.0666 2.82079 10.2158 2.50978 9.33398 2.50879H9.33301ZM3.36133 27.3369L3.11133 27.3359C2.34368 27.3351 1.61287 27.064 1.07812 26.5898C0.544465 26.1166 0.250848 25.4819 0.25 24.8271V16.5518L0.255859 16.3613C0.310143 15.4095 0.760404 14.5006 1.53418 13.8145C2.36057 13.0817 3.4869 12.6652 4.66699 12.6641H16.1758L23.8799 20.8613L23.9541 20.9395H37.333C38.5131 20.9406 39.6394 21.3571 40.4658 22.0898C41.2396 22.7759 41.6897 23.685 41.7441 24.6367L41.75 24.8281V28.9658C41.749 29.6204 41.4555 30.2554 40.9219 30.7285C40.3871 31.2026 39.6562 31.4738 38.8887 31.4746H38.6387V37.2412L38.625 37.4854C38.561 38.052 38.2776 38.5897 37.8105 39.0039C37.2758 39.478 36.545 39.7492 35.7773 39.75H31.1113L30.8252 39.7373C30.1638 39.6784 29.546 39.4187 29.0781 39.0039C28.5444 38.5306 28.2509 37.896 28.25 37.2412V29.2158H30.8613V37.4912H36.0273V29.2158H39.1387V24.8271C39.1382 24.385 38.9396 23.9684 38.5986 23.666C38.2586 23.3645 37.8035 23.1987 37.334 23.1982H22.7129L15.0088 15.001L14.9355 14.9229H4.66602C4.19659 14.9233 3.74139 15.0882 3.40137 15.3896C3.06025 15.6921 2.86178 16.1095 2.86133 16.5518V25.0771H5.97266V37.4912H12.6943V27.8359H15.3057V37.2412L15.292 37.4854C15.228 38.052 14.9446 38.5898 14.4775 39.0039C13.9428 39.478 13.2119 39.7492 12.4443 39.75H6.22266C5.45502 39.7492 4.72421 39.478 4.18945 39.0039C3.65571 38.5306 3.36222 37.896 3.36133 37.2412V27.3369Z" fill="#6180A1" stroke="white" stroke-width="0.5"/>
                                    </svg>
                                    <h3 class="tax-info__title">КТО МОЖЕТ ПОЛУЧИТЬ НАЛОГОВЫЙ ВЫЧЕТ ЗА ЛЕЧЕНИЕ РЕБЕНКА?</h3>
                                </div>
                                <p class="tax-info__text">
                                    Социальный вычет по расходам на лечение ребенка могут получить родители, усыновители,
                                    опекуны или попечители. При условии, что они имеют доход, который облагается НДФЛ по ставке
                                    13%, расходы подтверждены документами и у медорганизации есть лицензия. Ребенку должно быть
                                    не больше 18 лет.
                                </p>
                                <p class="tax-info__text">
                                    Изменения в части получения налогового вычета за медицинские услуги внесены в Налоговый
                                    кодекс РФ​ Федеральным законом от 14.07.2022 № 323-ФЗ.
                                </p>
                                <p class="tax-info__text">
                                    Ранее официально трудоустроенные граждане могли получить социальный вычет по НДФЛ за
                                    медицинские услуги по расходам на детей в возрасте до 18 лет (ст. 219 НК РФ). Теперь же
                                    получить такой вычет можно на детей в возрасте до 24 лет при условии, что они обучаются в
                                    образовательных организациях в очной форме. Остальные условия для получения вычета остались
                                    прежними
                                </p>
                                <p class="tax-info__text">
                                    Поправка применяется к расходам на лечение, понесенным с 2022 года.
                                </p>
                            </div>
                            <div class="tax-info__grid-wrapper">
                                <div class="tax-info__item tax-info__item_text">
                                    <div class="tax-info__header">
                                        <svg width="30" height="40" viewBox="0 0 30 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4706 40H3.52941C1.58029 40 0 38.4426 0 36.5217V3.47826C0 1.55739 1.58029 0 3.52941 0H20.2703C20.2721 0 20.2747 0 20.2765 0H20.2941C20.5765 0 20.8174 0.14 20.9788 0.343478L29.6515 8.89043C29.8588 9.04957 30 9.28696 30 9.56522V9.58348C30 9.58522 30 9.58609 30 9.58783V36.5217C30 38.4426 28.4197 40 26.4706 40ZM21.1765 2.94V8.69565H27.0168L21.1765 2.94ZM28.2353 10.4348H20.2941C19.8062 10.4348 19.4118 10.0452 19.4118 9.56522V1.73913H3.52941C2.55529 1.73913 1.76471 2.51826 1.76471 3.47826V36.5217C1.76471 37.4817 2.55529 38.2609 3.52941 38.2609H26.4706C27.4447 38.2609 28.2353 37.4817 28.2353 36.5217V10.4348ZM22.9412 33.0435H7.05882C6.57088 33.0435 6.17647 32.6548 6.17647 32.1739C6.17647 31.6939 6.57088 31.3043 7.05882 31.3043H22.9412C23.4291 31.3043 23.8235 31.6939 23.8235 32.1739C23.8235 32.6548 23.4291 33.0435 22.9412 33.0435ZM22.9412 26.087H7.05882C6.57088 26.087 6.17647 25.6983 6.17647 25.2174C6.17647 24.7374 6.57088 24.3478 7.05882 24.3478H22.9412C23.4291 24.3478 23.8235 24.7374 23.8235 25.2174C23.8235 25.6983 23.4291 26.087 22.9412 26.087ZM22.9412 19.1304H7.05882C6.57088 19.1304 6.17647 18.7417 6.17647 18.2609C6.17647 17.7809 6.57088 17.3913 7.05882 17.3913H22.9412C23.4291 17.3913 23.8235 17.7809 23.8235 18.2609C23.8235 18.7417 23.4291 19.1304 22.9412 19.1304Z" fill="#6180A1"/>
                                        </svg>
                                        <h3 class="tax-info__title">КАК ПОДАТЬ ДОКУМЕНТЫ?</h3>
                                    </div>
                                    <p class="tax-info__text">
                                        Документы на налоговый вычет можно подать через работодателя (в этом случае сумма вычета
                                        будет присоединена к вашей заработной плате) или самостоятельно в налоговую. Необходимо
                                        заполнить декларацию 3-НДФЛ, собрать нужные документы и представить их в налоговую
                                        — лично или на сайте
                                    </p>
                                </div>
                                <div class="tax-info__item tax-info__item_text">
                                    <div class="tax-info__header">
                                        <svg width="40" height="48" viewBox="0 0 40 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M34.8733 13.7097C38.05 17.352 40 22.1497 40 27.4286C40 38.7897 31.0456 48 20 48C8.95444 48 0 38.7897 0 27.4286C0 17.2377 7.21111 8.80114 16.6667 7.16572V2.28571H14.4444C13.83 2.28571 13.3333 1.77371 13.3333 1.14286C13.3333 0.512 13.83 0 14.4444 0H25.5556C26.17 0 26.6667 0.512 26.6667 1.14286C26.6667 1.77371 26.17 2.28571 25.5556 2.28571H23.3333V7.16C27.1411 7.824 30.5822 9.58743 33.3389 12.1314L35.9267 9.46972C36.35 9.03429 37.0367 9.03429 37.4611 9.46972C37.8844 9.90514 37.8844 10.6126 37.4611 11.048L34.8733 13.7097ZM21.1111 2.28571H18.8889V6.91543C19.2589 6.89371 19.6244 6.85714 20 6.85714C20.3756 6.85714 20.7411 6.89029 21.1111 6.91086V2.28571ZM20 9.14286C10.1822 9.14286 2.22222 17.3291 2.22222 27.4286C2.22222 37.528 10.1822 45.7143 20 45.7143C29.8189 45.7143 37.7778 37.528 37.7778 27.4286C37.7778 17.3303 29.8189 9.14286 20 9.14286ZM20 32C17.5456 32 15.5556 29.9531 15.5556 27.4286C15.5556 26.5726 15.7989 25.7806 16.1978 25.096L10.3167 19.048C9.89333 18.6126 9.89333 17.9051 10.3167 17.4697C10.7411 17.0343 11.4278 17.0343 11.8511 17.4697L17.7322 23.5177C18.3989 23.1074 19.1678 22.8571 20 22.8571C22.4544 22.8571 24.4444 24.904 24.4444 27.4286C24.4444 29.9531 22.4544 32 20 32ZM20 25.1429C18.7733 25.1429 17.7778 26.1669 17.7778 27.4286C17.7778 28.6903 18.7733 29.7143 20 29.7143C21.2267 29.7143 22.2222 28.6903 22.2222 27.4286C22.2222 26.1669 21.2267 25.1429 20 25.1429Z" fill="#6180A1"/>
                                        </svg>
                                        <h3 class="tax-info__title">
                                            КАК ПОЛУЧИТЬ СПРАВКУ ИЗ КЛИНИКИ МРТ ЛИДЕР?
                                        </h3>
                                    </div>
                                    <p class="tax-info__text">
                                        Готовую справку можно получить только лично. Обязательно нужен документ, удостоверяющий
                                        личность (паспорт). По закону такую справку может получить только сам налогоплательщик,
                                        и ее нельзя отправлять по почте, обычной или электронной.
                                    </p>
                                </div>
                            </div>
                        </div>
        
                        <div class="tax-info__grid">
                            <div class="tax-info__item tax-info__item_text">
                                <div class="tax-info__header">
                                    <svg width="40" height="42" viewBox="0 0 40 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 41L39 32.3043V14.913M20 41L1 32.3043V14.913M20 41V23.6087M39 14.913L20 23.6087M39 14.913L30.3636 10.9617M20 23.6087L1 14.913M1 14.913L9.63636 10.9617M20 14.913V1M20 14.913L26.9091 7.95652M20 14.913L13.0909 7.95652" stroke="#6180A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <h3 class="tax-info__title">
                                        КАКОВ СРОК ПОДАЧИ ДОКУМЕНТОВ НА НАЛОГОВЫЙ ВЫЧЕТ?
                                    </h3>
                                </div>
                                <p class="tax-info__text">
                                    Срок подачи документов – 3 года, то есть в 2022 году вы можете подать документы за лечение в
                                    2019-2021 годах.
                                </p>
                            </div>
                            <div class="tax-info__item tax-info__item_text">
                                <div class="tax-info__header">
                                    <svg width="45" height="42" viewBox="0 0 45 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 15.392V14.392C0.447715 14.392 0 14.8398 0 15.392H1ZM1 27.4428H0C0 27.9951 0.447715 28.4428 1 28.4428L1 27.4428ZM22.6286 10.0381L21.918 9.3345C21.5321 9.72421 21.5321 10.352 21.918 10.7417L22.6286 10.0381ZM21.8814 1.83488V0.834878H3.98305V1.83488V2.83488H21.8814V1.83488ZM3.98305 1.83488V0.834878C1.77333 0.834878 0 2.64013 0 4.84758H1H2C2 3.726 2.8965 2.83488 3.98305 2.83488V1.83488ZM1 4.84758H0V37.9873H1H2V4.84758H1ZM1 37.9873H0C0 40.1948 1.77333 42 3.98305 42V41V40C2.8965 40 2 39.1089 2 37.9873H1ZM3.98305 41V42H30.8305V41V40H3.98305V41ZM30.8305 41V42C33.0402 42 34.8136 40.1948 34.8136 37.9873H33.8136H32.8136C32.8136 39.1089 31.9171 40 30.8305 40V41ZM33.8136 37.9873H34.8136V21.4174H33.8136H32.8136V37.9873H33.8136ZM9.94915 15.392V14.392H1V15.392V16.392H9.94915V15.392ZM1 15.392H0V27.4428H1H2V15.392H1ZM1 27.4428V28.4428H9.94915V27.4428V26.4428H1V27.4428ZM9.94915 27.4428V28.4428C13.8055 28.4428 16.9153 25.2879 16.9153 21.4174H15.9153H14.9153C14.9153 24.202 12.6823 26.4428 9.94915 26.4428V27.4428ZM15.9153 21.4174H16.9153C16.9153 17.547 13.8055 14.392 9.94915 14.392V15.392V16.392C12.6823 16.392 14.9153 18.6328 14.9153 21.4174H15.9153ZM11.4407 21.4174H10.4407C10.4407 21.7064 10.2113 21.9238 9.94915 21.9238V22.9238V23.9238C11.3345 23.9238 12.4407 22.7923 12.4407 21.4174H11.4407ZM9.94915 22.9238V21.9238C9.68699 21.9238 9.45763 21.7064 9.45763 21.4174H8.45763H7.45763C7.45763 22.7923 8.56382 23.9238 9.94915 23.9238V22.9238ZM8.45763 21.4174H9.45763C9.45763 21.1284 9.68699 20.9111 9.94915 20.9111V19.9111V18.9111C8.56382 18.9111 7.45763 20.0426 7.45763 21.4174H8.45763ZM9.94915 19.9111V20.9111C10.2113 20.9111 10.4407 21.1284 10.4407 21.4174H11.4407H12.4407C12.4407 20.0426 11.3345 18.9111 9.94915 18.9111V19.9111ZM45 10.1197V9.11975H22.6271V10.1197V11.1197H45V10.1197ZM31.5777 1L30.8671 0.296399L21.918 9.3345L22.6286 10.0381L23.3392 10.7417L32.2883 1.7036L31.5777 1ZM22.6286 10.0381L21.918 10.7417L30.8671 19.7798L31.5777 19.0762L32.2883 18.3726L23.3392 9.3345L22.6286 10.0381Z" fill="#6180A1"/>
                                    </svg>
                                    <h3 class="tax-info__title">
                                        КАКУЮ СУММУ МОЖНО ПОЛУЧИТЬ В КАЧЕСТВЕ НАЛОГОВОГО ВЫЧЕТА ЗА ЛЕЧЕНИЕ РЕБЕНКА?
                                    </h3>
                                </div>
                                <p class="tax-info__text">
                                    Можно вернуть 13% от суммы, потраченной на медицинские услуги. Лимит суммы 120 000 рублей в
                                    год за обычное лечение. Значит вы сможете вернуть 15 600 рублей.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php get_template_part('template-parts/tour-or-animals-map'); ?>

    </div>
</main>

<!-- Обработчик формы -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const taxForm = document.querySelector('.tax__form');
        if (taxForm) {
            // --- Инициализация маски для телефона в taxForm ---
            const taxpayerPhoneInput = taxForm.querySelector('input[name="taxpayer_phone"]');
            let taxpayerPhoneMaskInstance = null;
            if (taxpayerPhoneInput) {
                taxpayerPhoneMaskInstance = IMask(taxpayerPhoneInput, {
                    mask: '+{7} (000) 000-00-00',
                    lazy: false,
                    placeholderChar: '_'
                });
            }

            // Обработка отправки формы
            taxForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // --- Проверки ---
                const relationInput = taxForm.querySelector('input[name="relation"]:checked');
                const relationValue = relationInput ? relationInput.value : '';

                // Проверка телефона: убедимся, что это российский номер
                let phone = '';
                if (taxpayerPhoneInput) {
                    if (taxpayerPhoneMaskInstance && taxpayerPhoneMaskInstance.unmaskedValue) {
                        // Получаем "сырое" значение из маски
                        phone = taxpayerPhoneMaskInstance.unmaskedValue;
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
                        phone = taxpayerPhoneInput.value.replace(/[^\d\+]/g, '');
                    }
                }

                // Проверка телефона: убедимся, что это российский номер
                const phoneDigits = phone.replace(/[^\d]/g, ''); // Оставляем только цифры
                if (phoneDigits.length < 10 || phoneDigits.length > 11) {
                    alert('Пожалуйста, введите корректный номер телефона (например, +7 (999) 999-99-99).');
                    if (taxpayerPhoneInput) taxpayerPhoneInput.focus();
                    return;
                }
                // --- Конец проверок ---

                const formData = new FormData();

                formData.append('action', 'send_tax_form');
                formData.append('nonce', '<?php echo wp_create_nonce("tax_form_nonce"); ?>');

                // Собираем данные формы по name атрибутам
                formData.append('relation', relationValue);

                // Данные пациента
                formData.append('patient_city', taxForm.querySelector('input[name="patient_city"]')?.value || '');
                formData.append('patient_inn', taxForm.querySelector('input[name="patient_inn"]')?.value || '');
                formData.append('patient_name', taxForm.querySelector('input[name="patient_name"]')?.value || '');
                formData.append('patient_dob', taxForm.querySelector('input[name="patient_dob"]')?.value || '');
                formData.append('patient_doc_type', taxForm.querySelector('input[name="patient_doc_type"]')?.value || '');
                formData.append('patient_doc_number', taxForm.querySelector('input[name="patient_doc_number"]')?.value || '');
                formData.append('patient_doc_date', taxForm.querySelector('input[name="patient_doc_date"]')?.value || '');

                // Данные налогоплательщика
                formData.append('taxpayer_name', taxForm.querySelector('input[name="taxpayer_name"]')?.value || '');
                formData.append('taxpayer_dob', taxForm.querySelector('input[name="taxpayer_dob"]')?.value || '');
                formData.append('taxpayer_doc_type', taxForm.querySelector('input[name="taxpayer_doc_type"]')?.value || '');
                formData.append('taxpayer_doc_number', taxForm.querySelector('input[name="taxpayer_doc_number"]')?.value || '');
                formData.append('taxpayer_doc_date', taxForm.querySelector('input[name="taxpayer_doc_date"]')?.value || '');
                formData.append('taxpayer_inn', taxForm.querySelector('input[name="taxpayer_inn"]')?.value || '');
                formData.append('taxpayer_relation_to_patient', taxForm.querySelector('input[name="taxpayer_relation_to_patient"]')?.value || '');
                // Отправляем нормализованный номер телефона
                formData.append('taxpayer_phone', phone);
                formData.append('taxpayer_comment', taxForm.querySelector('textarea[name="taxpayer_comment"]')?.value || '');

                // Года 
                const yearCheckboxes = taxForm.querySelectorAll('input[name="years[]"]:checked');
                yearCheckboxes.forEach(cb => {
                    formData.append('years[]', cb.value);
                });

                // Отправка данных через AJAX
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                        });
                    }
                    return response.text();
                })
                .then(result => {
                    alert(result);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch(error => {
                    console.error('Ошибка при отправке формы:', error);
                    alert(`Произошла ошибка при отправке формы: ${error.message || 'Неизвестная ошибка'}`);
                });
            });
        } else {
            console.warn('Форма с классом .tax__form не найдена на странице.');
        }
    });
</script>

<?php get_footer(); ?>ц