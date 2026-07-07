# Контекстная реклама — МРТ для животных (seov)

## Статус

| Дата | Событие |
|------|---------|
| 2026-07-07 | Анализ ниши, создана папка `seov/`, семантика, объявления, правки лендинга и метрики |

## Локальные материалы (не в Git)

Папка **`seov/`** в корне репозитория — в `.gitignore`, на GitHub не выгружается.

```
seov/
├── README.md
├── semantic-core.csv      # ~60 ключей, 5 групп intent
├── yandex-direct-ads.csv  # 13 объявлений, 4 кампании
├── minus-words.txt
├── utm-templates.md
└── campaign-setup.md
```

## Продукт

| Параметр | Значение |
|----------|----------|
| Бренд | **MRI Animal** · дескриптор «мрт животным» |
| На сайте (шапка) | дескриптор логотипа: «мрт животным» (`header.php`) |
| Сайт | МРТ Лидер, филиал `almaty_aubakirova` |
| URL | https://mrt-lider.kz/almaty_aubakirova/ |
| Адрес | ул. Аубакирова, 17/1, с. Отеген батыра |
| Оборудование | Philips Achieva 1,5 Т |
| Базовая цена | 50 000 ₸ |
| Седация / контраст | +25 000 ₸ каждый |
| Телефоны (листовка) | +7 777 000 30 39 / 38 / 35 / 34 |

## Конкурентная ниша

- В Алматы ветклиники чаще рекламируют **КТ** (ЦВМ), не МРТ.
- УТП в рекламе: «Именно **МРТ** (не КТ) · **1,5 Т** · ветеринарное заключение в день».
- Гео-ограничение: филиал ~30 км от Алматы — обязательно указывать в объявлениях.

## Кампании Яндекс Директ (KZ)

| utm_campaign | Доля бюджета | Фокус |
|--------------|--------------|-------|
| `animals_mrt_commercial` | 70% | мрт для животных, собаке, кошке, цена |
| `animals_mrt_zones` | 20% | мозг, позвоночник, суставы, онкология |
| `animals_mrt_symptoms` | 10% | хромота, эпилепсия, грыжа (низкие ставки) |
| `animals_mrt_b2b` | опц. | ветклиники, партнёры |
| `animals_mrt_retarget` | после 2 нед | РСЯ, 14 дней |

Стартовый бюджет: **150 000–300 000 ₸/мес**.

## UTM-шаблон

```
https://mrt-lider.kz/almaty_aubakirova/?utm_source=yandex&utm_medium=cpc&utm_campaign={campaign}&utm_content={group}&utm_term={keyword}
```

UTM сохраняются в `sessionStorage` (`mrt_utm`) и попадают в email/Telegram заявки.

## Яндекс.Метрика

| Slug | Counter ID |
|------|------------|
| `almaty` | **110465113** |
| `almaty_aubakirova` | **110465113** (тот же; fallback на `almaty` в коде) |

Snippet: `seov/metrika-counters.md` → WP Admin → **Метрики городов** → `almaty`.

## Цели Метрики (reachGoal)

Настроить в кабинете для счётчика **110465113**:

| Цель | JS ID |
|------|-------|
| Отправка формы записи | `animals_booking_submit` |
| Открытие модалки | `animals_booking_open` |
| Клик по телефону | `animals_phone_click` |
| Клик WhatsApp | `animals_whatsapp_click` |
| Переход на прайс | `animals_price_click` |

## Изменения в коде (2026-07-07)

| Файл | Что сделано |
|------|-------------|
| `inc/mrt-animals-seo.php` | Title, meta description, Open Graph для animals |
| `home-animals.php` | MRI Animal · мрт животным, цена в hero, гео-подсказка |
| `assets/css/animals.css` | Стили блока цены |
| `assets/js/mrt-metrika.js` | UTM + reachGoal |
| `functions.php` | Метрика по URL, `mrtMetrikaId`, fallback animals → almaty (110465113) |
| `template-parts/booking-modal.php` | UTM в форме, goal при успехе |
| `template-parts/send-popup-form.php` | UTM в теле письма |

Паттерн метрики — как на `.ru` (`.tmp-ru-functions.php`: `window.mrtMetrikaId` + reachGoal).

## KPI (ориентир)

- CTR поиск > 8%
- CPC 200–600 ₸
- CR лендинг 3–5%
- CPA заявка < 15 000 ₸

## Органическое промо

На всех KZ-филиалах (кроме animals) в шапке показывается блок «МРТ животным» — `mrt_should_show_animals_promo()` в `inc/mrt-header-helpers.php`.

## Чеклист перед запуском Директа

1. [ ] Код Метрики **110465113** в WP Admin для `almaty` (см. `seov/metrika-counters.md`)
2. [ ] Создать 5 JS-целей в Метрике 110465113
3. [ ] Импортировать CSV из `seov/` в кабинет KZ
4. [ ] Проверить title на проде после деплоя
5. [ ] Запустить с ручными ставками, через 2 нед — ретаргетинг

## Связанные чанки

- `05-animals-branch-almaty-aubakirova.md` — филиал, шаблон, WP Admin
- `03-branches.md` — type `animals`, slug `almaty_aubakirova`
