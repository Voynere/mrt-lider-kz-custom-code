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
