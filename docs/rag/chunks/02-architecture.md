# Архитектура сайта

## Роутинг по филиалам

Город/филиал определяется **без custom rewrite rules** (кроме прайса):

1. Первый сегмент URL: `/{slug}/...`
2. Cookie `selected_city` (30 дней)
3. Fallback: `almaty` (или `almaty_aubakirova` на странице животных)

Логика дублируется в PHP-шаблонах и `assets/js/main.js`. Единый источник — `inc/mrt-city-config.php` + `wp_localize_script('main', 'mrtCityConfig', ...)`.

## Структура URL

```
/{city}/                          — главная (home.php или home-animals.php)
/{city}/uslugi-i-ceny/            — виды услуг
/{city}/uslugi-i-ceny/price/{type}/ — прайс по типу (rewrite в functions.php)
/{city}/kontakty/                 — контакты
/{city}/specialisty/              — врачи
/{city}/vopros-otvet/             — FAQ
```

Глобальные страницы без префикса города: `privacy`, и др.

## Тема MRT Lider

- `functions.php` — assets, breadcrumbs, AJAX-формы, метрики
- `header.php` / `footer.php` — навигация, контакты из ACF
- `vacancies.php` — CPT `vacancy` + таксономия `vacancy_city`

## Плагин services-importer

- CPT: `service`
- Таксономии: `branch` (филиал), `service_type` (МРТ 1.5Т, КТ…)
- Meta: `si_price`, `si_discount`, `si_category`, `si_oblast`, `si_type`
- Импорт: **лист Excel = название филиала** → записи с тегом `branch`

## Формы (AJAX)

| Action | Handler |
|--------|---------|
| `send_booking_form` | `template-parts/send-popup-form.php` |
| `send_home_form` | `template-parts/send-home-form.php` |
| `send_contact_form` | `template-parts/send-contact-form.php` |

Город для маршрутизации писем/Telegram берётся из URL или cookie.

## Наследие RU-сети

В формах и `page-license.php` остались длинные списки российских городов. Для KZ в UI активны только 5 филиалов из `mrt-city-config.php`.
