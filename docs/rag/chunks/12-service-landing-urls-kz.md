# Посадочные страницы услуг — KZ

## URL-паттерны

| Тип | Паттерн | Шаблон / роутинг |
|-----|---------|------------------|
| Лендинг услуги | `/{city}/services/{service-post-slug}/` | `page-service-landing.php` via `mrt_route_service_landing()` |
| Подкатегория прайса | `/{city}/uslugi-i-ceny/{subcat}/` | `page-subservice.php` via `mrt_route_uslugi_subpages()` |
| Прайс по типу | `/{city}/uslugi-i-ceny/price/{service_type}/` | `page-service-item.php` |
| Хаб прайса | `/{city}/uslugi-i-ceny/` | `page-services.php` |

Города (`mrt_get_known_city_slugs()`): `almaty`, `astana`, `karaganda`, `taldykorgan`, `almaty_aubakirova`.

## Статус по филиалам (2026-07-08)

| Филиал | Подкатегории | Лендинги `/services/` | Примечание |
|--------|--------------|-------------------------|------------|
| **almaty** | ✅ 200 | ✅ ~127 в sitemap | Работает |
| **astana** | ✅ 200 | ✅ ~93 | Работает |
| **karaganda** | ✅ 200 | ✅ ~63 | Работает |
| **taldykorgan** | ✅ 200 | ✅ ~70 | Работает |
| **almaty_aubakirova** (animals) | ❌ 404 (by design) | ❌ 404 | Прайс на `/uslugi-i-ceny/` (static template) |

## Animals (`almaty_aubakirova`) — особые правила

- **Нет** CPT `service` и **нет** `/services/{slug}/` — `mrt_is_service_hidden_for_city()` и ранний 404 в `mrt_route_service_landing()`.
- **Нет** `/uslugi-i-ceny/{subcat}/` — все подкатегории скрыты через `mrt_city_hidden_subcategory_prefixes()` (`mrt`, `kt`, `uzi`, `densitometriya`).
- Прайс: `page-services.php` → `template-parts/animals-services-content.php`.
- SEO description прайса: `mrt_seo_animals_prices_description()` (не «0 видов диагностики»).

## Sitemap

- `sitemap-landings.xml` — только стандартные филиалы (animals пропускается).
- `sitemap-subcats.xml` — подкатегории с учётом `mrt_is_subcategory_hidden_for_city()`.

## Проверка на проде

```bash
curl -sI 'https://mrt-lider.kz/almaty/services/mrt-golovy-mrt-gipofiz/' | head -1
curl -sI 'https://mrt-lider.kz/almaty/uslugi-i-ceny/mrt-golovy/' | head -1
curl -sI 'https://mrt-lider.kz/almaty_aubakirova/uslugi-i-ceny/kt/' | head -1   # expect 404
```

## Связанные файлы

- `inc/mrt-service-routing.php`, `inc/mrt-service-helpers.php`
- `page-service-landing.php`, `page-subservice.php`, `page-services.php`
- `seo-config.php` — sitemap landings/subcats
