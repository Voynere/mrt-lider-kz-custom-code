# Архитектура сайта

## Роутинг по филиалам

Файл: `inc/mrt-city-routing.php` + `inc/mrt-header-helpers.php`.

1. Rewrite rules: `/{city}/`, `/{city}/{page}/`, прайс, посадочные услуг
2. Query var `mrt_city` — slug из URL
3. Cookie `selected_city` (30 дней)
4. Fallback: `almaty`

`redirect_canonical` и `template_redirect` сохраняют city prefix. JS (`main.js`) синхронизирует ссылки меню с активным городом.

Единый источник slug/label — `inc/mrt-city-config.php` + `wp_localize_script('main', 'mrtCityConfig', ...)`.

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
| `send_service_form` | `template-parts/send-service-form.php` |
| `send_tax_form` | `template-parts/send-tax-form.php` |

Email: `mrt_get_form_notification_settings($city)` — config `form_email` → ACF → fallback.  
Telegram: ACF `telegram_chats` на contact-посте.

См. chunk `10-metrika-forms-routing.md`.

## Наследие RU-сети

В формах и `page-license.php` остались длинные списки российских городов. Для KZ в UI активны только 5 филиалов из `mrt-city-config.php`.
