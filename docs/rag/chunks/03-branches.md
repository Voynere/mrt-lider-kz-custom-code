# Филиалы и города

## Модель данных (3 слоя)

### 1. Slug филиала (роутинг)

Хранится в `inc/mrt-city-config.php` → `mrt_get_branches()`.

Поля ветки:
- `label` — отображаемое имя
- `type` — `standard` | `animals`
- `currency` — `tenge` для KZ
- `address_full`, `address_short` — для animals-филиала
- `branch_taxonomy` — slug таксономии `branch` для прайса
- `home_template` — имя файла шаблона (только animals)

### 2. WordPress category (контент)

Посты привязаны к рубрикам:
- `{city_slug}` — город/филиал
- `contacty` — контактный пост
- `specialisty` — врачи
- `mrt`, `kt`, `uzi`… — FAQ

### 3. Таксономия branch на service (цены)

Импорт из Excel: лист = имя филиала. На KZ slug листа обычно совпадает с city slug.

## Cookie

- `selected_city` — текущий филиал
- `city_chosen_confirmed` — баннер «Ваш город» подтверждён

## Выбор города в UI

`header.php` → модальное окно `.modal-city` → `main.js` перезагружает страницу с новым URL.

## Мультифилиальность внутри города

Для нескольких юр. адресов в одном городе — индексированные ACF-поля на contact-посте:
`contacts_address_1..N`, `contacts_juridicheskij_adress_N`, табы в `page-license.php`.

Справочник sub-филиалов (legacy): `all city.php` (`almaty_begalina`, `almaty_gagarina`…).

## Добавление нового филиала (код)

1. Добавить запись в `mrt_get_branches()` в `inc/mrt-city-config.php`
2. Добавить slug в массивы `known_city_slugs` (или полагаться на config в header)
3. Обновить `main.js` / `city-chosen.js` (или `mrtCityConfig`)
4. Добавить пункт в модальное окно городов в `header.php`
5. Создать контент в WP Admin (см. chunk `07-wp-admin-checklist`)
