# SEO и микроразметка KZ (без AIOSEO)

## Модуль

Файл: `wp-content/themes/MRT Lider/seo-config.php`  
Портирован с mrt-lider.ru, адаптирован для **только KZ-филиалов**.

AIOSEO **не используется** — после деплоя деактивировать плагин в WP Admin.

## Что выводится

| Элемент | Функция |
|---------|---------|
| `<title>` | `mrt_seo_title_parts`, `mrt_seo_pre_title` |
| meta description | `mrt_seo_meta_tags` |
| canonical | `mrt_seo_canonical_url` |
| Open Graph + Twitter | `mrt_seo_meta_tags` |
| JSON-LD @graph | `mrt_seo_schema_jsonld` |
| sitemap.xml | `mrt_seo_sitemap_*` |
| robots.txt | `mrt_seo_robots_txt` |

## Города

`mrt_seo_get_cities()` → `mrt_get_city_map()` (5 филиалов KZ).

## Animals (MRI Animal)

- Home `/almaty_aubakirova/`: title «МРТ для животных… | MRI Animal»
- Schema: `VeterinaryCare` вместо `MedicalClinic`
- Контакты / прайс — отдельные title и description

## Валюта и страна

- Валюта: **₸** (`mrt_seo_currency`)
- Schema `addressCountry`: **KZ**
- `priceCurrency`: **KZT**

## Верификация (в head)

- Google Search Console
- Яндекс.Вебмастер
- Facebook domain verification

## После деплоя

1. WP Admin → Плагины → **деактивировать All in One SEO**
2. Настройки → Постоянные ссылки → **Сохранить** (flush rewrite для sitemap)
3. Проверить `/almaty/` — title «МРТ в Алматы…», без AIOSEO в source
4. Проверить `/sitemap.xml`

## Связанные файлы

- `functions.php` — `require seo-config.php`, `add_theme_support('title-tag')`
- Удалён: `inc/mrt-animals-seo.php` (логика в seo-config)
