# Метрика, формы, city-routing

## Яндекс.Метрика (источник истины — тема)

ID счётчиков заданы в `inc/mrt-city-config.php` → `yandex_metrika_id`.

| Филиал | Slug | Counter ID |
|--------|------|------------|
| Алматы | `almaty` | 110465113 |
| Астана | `astana` | 110466202 |
| Караганда | `karaganda` | **110468879** |
| Талдыкорган | `taldykorgan` | **110469944** |

Подключение: `functions.php` → `insert_city_specific_metrics_from_options()` вызывает `mrt_get_branch_yandex_metrika_id()` и `mrt_build_yandex_metrika_snippet()`. Конфиг темы **перекрывает** WP Admin (`city_metrics_data` / «Метрики городов»).

Проверка на проде:
```bash
curl -s 'https://mrt-lider.kz/karaganda/' | rg 'ym\(110468879'
```

## Формы — маршрутизация писем

Хелпер: `mrt_get_form_notification_settings($city_slug)` в `mrt-city-config.php`.

Приоритет email:
1. `form_email` в конфиге филиала
2. ACF `contacts_emails.contacts_email_1` на contact-посте
3. Fallback `prooo100mix@yandex.ru`

**Animals branch** (`almaty_aubakirova`):
- `form_email` => `mri-animal@mail.ru`
- Отображение на контактах: `mrt_get_contact_display_emails()` — тот же fallback, если ACF пуст

Handlers (все используют хелпер):
- `template-parts/send-contact-form.php` — контакты, FAQ-модалка
- `template-parts/send-popup-form.php` — модалка записи
- `template-parts/send-home-form.php`, `send-service-form.php`, `send-tax-form.php`

Telegram chat IDs — только из ACF `telegram_chats` на contact-посте.

## City routing (rewrite)

Файл: `inc/mrt-city-routing.php` (подключается из `functions.php`).

- Query var `mrt_city` — slug филиала из URL
- Rewrite: `/{city}/`, `/{city}/{page}/`, `/{city}/uslugi-i-ceny/price/{type}/`
- `redirect_canonical` filter — не срезает city prefix
- `template_redirect` — редирект коротких URL на city-prefixed
- `mrt_city_home_template()` — `home-animals.php` для animals

Хелперы: `inc/mrt-header-helpers.php` — `mrt_get_selected_city_slug()`, `mrt_build_city_switch_url()`.

JS: `assets/js/main.js` — префикс города в ссылках меню, логотипа, хлебных крошек.

## Посадочные страницы услуг

- `inc/mrt-service-routing.php`, `inc/mrt-service-helpers.php`
- Шаблоны: `page-service-landing.php`, `page-subservice.php`, `service-content.php`, `subservice-content.php`
- Стили: `assets/css/service-landing.css`

## Поиск по сайту

- `template-parts/search-overlay.php`, `assets/js/search.js`
- AJAX: `mrt_site_search` в `functions.php`

## Интерактив главной (стандартные филиалы)

Скрипты (только city home, не animals):
- `assets/js/about-numbers.js` — счётчики статистики
- `assets/js/about-why.js` — блок «Почему мы?»
