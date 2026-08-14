# Прайс KZ: льготный режим и CLI-апдейты

## Льготный режим (concessional)

Города: `almaty`, `astana`, `karaganda`, `taldykorgan`  
Не применяется к `almaty_aubakirova` (animals).

Функции в `inc/mrt-service-helpers.php`:

- `mrt_city_concessional_pricing_slugs()`
- `mrt_city_uses_concessional_pricing()`
- `mrt_city_concessional_price_notice_paragraphs()` — текст 10% / после 20:00 и 00:00
- `mrt_render_price_table_cell()` / `mrt_render_price_hero_block()` — mode `concessional`
- `mrt_city_discount_marketing_phrase()` → «Скидки 10%»
- `mrt_city_discount_benefit_desc()` → «Скидка 10% льготным категориям и ночные цены по прайсу»

Шаблон прайса: `page-service-item.php` — класс `price--concessional`, без колонки «Скидка*», плашка notice.

Та же логика на RU в `mrt-lider` `functions.php` (`mrt_get_kz_cities()` + concessional slugs).

## Поля услуги

| Meta | Смысл в льготном городе |
|------|-------------------------|
| `si_price` | Базовая цена |
| `si_discount` | Льготная (меньше базы) |
| `si_oblast` | Название области исследования |
| `si_category` | Категория (МРТ Головы, …) |

## CLI апдейта (2026-08-14)

`cli-update-kz-cholesteatoma-dotagita.php`:

1. Rename Кларискан → Дотагита (до/от 90 кг, цены 16000/32000)
2. Создать недостающие тиры Дотагита
3. Добавить «Головной мозг + холестеатома» (40500/34500), клон от нейроваскулярного шаблона

Запуск только на сервере (через GHA workflow), не из локальной машины без SSH-ключа.
