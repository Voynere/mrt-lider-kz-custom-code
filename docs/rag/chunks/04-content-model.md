# Модель контента (WordPress Admin)

## Для каждого филиала KZ

### 1. Рубрика (Category)

Slug = slug филиала (`almaty`, `almaty_aubakirova`, …).

### 2. Контактный пост

- Рубрики: `{slug}` + `contacty`
- ACF-группы:
  - `contacts_addresses` → `contacts_address_1`
  - `contacts_phones` → `contacts_phone_1`
  - `contacts_emails` → `contacts_email_1`
  - `contacts_opening_hours`
  - `contacts_map` — embed карты
  - `contacts_whatsapp`
  - `telegram_chats` — для уведомлений форм
  - `photos_centre`

### 3. Прайс (services-importer)

Excel-файл, лист с именем филиала (например `almaty_aubakirova`).

Колонки импорта — см. `wp-content/plugins/services-importer/services-importer.php`.

### 4. FAQ (опционально)

Пост с рубриками `{slug}` + `mrt` / `kt` / …  
ACF: `know_vopros_N`, `know_otvet_N` в `page-answers.php`.

### 5. Специалисты (для standard-филиалов)

Посты: `{slug}` + `specialisty`, ACF `specialists_image`, `specialists_job`.

## Валюта

KZ-филиалы: тенге `₸` — массив `$kazakhstan_cities` в `page-service-item.php`.

## Страницы WordPress

Страницы с шаблонами создаются в иерархии или с city-prefix в URL (зависит от настройки на сервере). Типичные slug: `uslugi-i-ceny`, `kontakty`, `specialisty`.
