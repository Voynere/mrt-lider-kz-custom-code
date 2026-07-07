# Шаблоны страниц (page templates)

| Файл | Template Name | Назначение |
|------|---------------|------------|
| `home.php` | home | Главная стандартного филиала |
| `home-animals.php` | home-animals | Главная МРТ для животных |
| `page-services.php` | services | Список видов услуг |
| `page-service-item.php` | uslugi | Прайс-таблица |
| `page-contacts.php` | contacts | Контакты + карта |
| `page-specialists.php` | specialists | Врачи |
| `page-answers.php` | answers | FAQ |
| `page-license.php` | license | Юр. информация, табы филиалов |
| `page-organization.php` | organization | Сведения о мед. организации |
| `page-vacancies.php` | vacancies | Вакансии |
| `page-site-map.php` | site-map | HTML-карта сайта |
| `page-tax.php` | (tax) | Справка для налогового вычета |
| `single-specialist.php` | Специалист | Карточка врача |
| `404.php` | — | Страница ошибки с учётом города |

## Общие partials

| Файл | Назначение |
|------|------------|
| `template-parts/booking-modal.php` | Модальное окно записи |
| `template-parts/send-*.php` | Обработчики AJAX-форм |
| `template-parts/search-overlay.php` | Поиск по сайту |
| `template-parts/tour-or-animals-map.php` | 3D-тур (standard) или карта (animals) |
| `template-parts/tour-block.php` | Блок 3D-тура |
| `template-parts/animals-map-block.php` | Яндекс-карта animals |
| `template-parts/animals-services-content.php` | Прайс animals |
| `template-parts/animals-about-content.php` | О центре animals |
| `template-parts/animals-answers-content.php` | FAQ animals |

## Inc-модули

| Файл | Назначение |
|------|------------|
| `inc/mrt-city-config.php` | Филиалы, метрика, form_email, карта |
| `inc/mrt-city-routing.php` | Rewrite, canonical, home template |
| `inc/mrt-header-helpers.php` | Город в шапке, телефоны |
| `inc/mrt-service-routing.php` | Посадочные услуг |
| `inc/mrt-service-helpers.php` | Хелперы прайса/услуг |
| `inc/mrt-animals-faq.php` | Данные FAQ animals |

## Определение города в шаблоне

Паттерн (legacy, постепенно заменяется config):

```php
$known_city_slugs = array('almaty', 'astana', ..., 'almaty_aubakirova');
// URL > cookie > fallback
```

Рекомендуемый паттерн:

```php
$selected_city = mrt_resolve_selected_city('almaty');
```

## Breadcrumbs

`custom_breadcrumbs()` в `functions.php` — учитывает city prefix и родителя «Услуги и цены» для прайса.
