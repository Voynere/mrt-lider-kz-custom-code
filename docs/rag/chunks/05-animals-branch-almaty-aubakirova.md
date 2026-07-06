# Филиал МРТ для животных — almaty_aubakirova

## Назначение

Специализированный филиал **МРТ для животных** (собаки, кошки, другие питомцы).

## Адрес

- Краткий: ул. Аубакирова, 17/1
- Полный: ул. Аубакирова, 17/1, село Отеген батыра, Илийский район, Алматинская область

## URL

```
https://mrt-lider.kz/almaty_aubakirova/
```

## Slug и тип

| Параметр | Значение |
|----------|----------|
| Slug | `almaty_aubakirova` |
| type | `animals` |
| label | МРТ животным |
| branch_taxonomy | `almaty_aubakirova` |

## Шаблон

Файл: `wp-content/themes/MRT Lider/home-animals.php`  
Template Name: `home-animals`

Отличия от стандартной `home.php`:
- Зелёная «pet-friendly» вёрстка (`assets/css/animals.css`)
- Контент про подготовку питомца, FAQ для владельцев
- Форма записи с полем «Кличка и вид питомца»
- Карта: ACF `contacts_map` или fallback Yandex embed по адресу
- Класс body: `mrt-animals-branch`

## Стили и скрипты

- CSS: `assets/css/animals.css` (подключается в `functions.php` для animals-филиала)
- Модалка записи: `template-parts/booking-modal.php`
- Кнопки `.booking-btn` → `assets/js/booking.js`

## Навигация

Филиал в модальном выборе городов: **«МРТ животным · Отеген батыра»**

City-specific страницы работают с префиксом:
`/almaty_aubakirova/kontakty/`, `/almaty_aubakirova/uslugi-i-ceny/` и т.д.

## Что настроить в WP Admin

1. Создать рубрику `almaty_aubakirova`
2. Создать contact-пост (рубрики `almaty_aubakirova` + `contacty`) с телефоном, WhatsApp, картой
3. Создать страницу с slug `almaty_aubakirova`, шаблон **home-animals**
4. Импортировать прайс: лист Excel `almaty_aubakirova`
5. (Опционально) FAQ-посты с рубрикой `almaty_aubakirova`

## Конфиг в коде

```php
// inc/mrt-city-config.php
'almaty_aubakirova' => array(
    'label'           => 'МРТ животным',
    'type'            => 'animals',
    'address_full'    => 'ул. Аубакирова, 17/1, село Отеген батыра...',
    'branch_taxonomy' => 'almaty_aubakirova',
    'home_template'   => 'home-animals.php',
),
```
