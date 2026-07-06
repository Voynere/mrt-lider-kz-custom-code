# MRT Lider KZ — обзор проекта

## Что это

Сайт сети диагностических центров **МРТ Лидер** для Казахстана: https://mrt-lider.kz

Репозиторий содержит **только кастомный код** WordPress. Ядро WP, ACF, uploads и `wp-config.php` — локально/на сервере.

## Что в git

| Путь | Назначение |
|------|------------|
| `wp-content/themes/MRT Lider/` | Тема: шаблоны, стили, JS, логика филиалов |
| `wp-content/plugins/services-importer/` | Импорт прайса из Excel в CPT `service` |
| `.github/workflows/deploy.yml` | Rsync темы и плагина на прод при push в `main` |
| `docs/` | Документация, RAG, доступы |

## Прод-сервер

- Хост: `91.207.75.94`
- Путь: `/var/www/mrt-lider.kz/data/www/mrt-lider.kz`
- Деплой: push в `main` → GitHub Actions

## Активные филиалы KZ (2026)

| Slug | Город/направление |
|------|-------------------|
| `almaty` | Алматы |
| `astana` | Астана |
| `karaganda` | Караганда |
| `taldykorgan` | Талдыкорган |
| `almaty_aubakirova` | МРТ для животных, с. Отеген батыра |

## Ключевой конфиг

Центральная конфигурация филиалов: `wp-content/themes/MRT Lider/inc/mrt-city-config.php`

Функции: `mrt_get_branches()`, `mrt_resolve_selected_city()`, `mrt_is_animals_branch()`.
