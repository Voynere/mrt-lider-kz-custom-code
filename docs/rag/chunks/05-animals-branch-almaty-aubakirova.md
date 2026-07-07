# Филиал МРТ для животных — almaty_aubakirova (MRI Animal)

## Брендинг

| Параметр | Значение |
|----------|----------|
| Slug | `almaty_aubakirova` |
| type | `animals` |
| label | МРТ животным «MRI Animal» |
| subtitle | с. Отеген батыра |
| form_email | `mri-animal@mail.ru` |

## Адрес

- Краткий: ул. Аубакирова, 17/1
- Полный: ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область

## URL

```
https://mrt-lider.kz/almaty_aubakirova/
https://mrt-lider.kz/almaty_aubakirova/uslugi-i-ceny/
https://mrt-lider.kz/almaty_aubakirova/about/
https://mrt-lider.kz/almaty_aubakirova/vopros-otvet/
https://mrt-lider.kz/almaty_aubakirova/kontakty/
```

## Определение в коде

```php
mrt_is_animals_branch($selected_city); // type === 'animals'
```

Body class: `mrt-animals-branch`

## Шаблоны и partials

| Страница | Файл | Partial |
|----------|------|---------|
| Главная | `home-animals.php` | — |
| Услуги | `page-services.php` (early return) | `animals-services-content.php` |
| О центре | `page-maps.php` (early return) | `animals-about-content.php` |
| FAQ | `page-answers.php` (early return) | `animals-answers-content.php` |
| Контакты | `page-contacts.php` | классы `animals-contacts-*` |
| Карта вместо 3D | `tour-or-animals-map.php` | `animals-map-block.php` |

Данные FAQ: `inc/mrt-animals-faq.php`

## Стили и скрипты

- CSS: `assets/css/animals.css`, `assets/css/header-ru.css` (promo header)
- Лого: `assets/img/logo-animals.svg`
- Модалка записи: `template-parts/booking-modal.php` + `booking.js`
- Карта: `mrt_get_animals_map_html()` в `mrt-city-config.php`

## Шапка

- Попап городов: секция `.modal-city__animals-*` (отдельно от алфавита)
- Телефоны: основной + «+3 номера» с hover-dropdown (`mrt_render_bottom_phones_extra`)
- Кнопка WhatsApp «Перейти» в promo-блоке animals

## Что НЕ используется на animals

- 3D-тур (`tour-block.php`)
- Боковые категории FAQ (МРТ/КТ/УЗИ)
- Интерактив stats/why-us с `home.php` (только standard cities)
- Нижняя карта на контактах (есть верхняя)

## WP Admin (опционально)

1. Рубрика `almaty_aubakirova`
2. Contact-пост (`almaty_aubakirova` + `contacty`) — телефоны, WhatsApp, `contacts_map`
3. Страница slug `almaty_aubakirova`, шаблон **home-animals**
4. Прайс: импорт листа `almaty_aubakirova`
5. Фото центра для блока «Наш центр» на контактах

## Конфиг

```php
// inc/mrt-city-config.php
'almaty_aubakirova' => array(
    'label'           => 'МРТ животным «MRI Animal»',
    'subtitle'        => 'с. Отеген батыра',
    'type'            => 'animals',
    'form_email'      => 'mri-animal@mail.ru',
    'address_short'   => 'ул. Аубакирова, 17/1',
    'address_full'    => '...',
    'branch_taxonomy' => 'almaty_aubakirova',
    'home_template'   => 'home-animals.php',
),
```

## Контекстная реклама

`seov/` (gitignored), RAG: `08-animals-contextual-ads.md`
