# RAG — база знаний проекта mrt-lider-kz

Структурированная документация для AI-агентов и разработчиков.

## Как искать

```bash
# По всем чанкам
rg -i "animals|филиал|almaty_aubakirova" docs/rag/chunks/

# Список чанков
cat docs/rag/manifest.json
```

## Индекс чанков

| ID | Файл | Тема |
|----|------|------|
| overview | `chunks/01-overview.md` | Обзор репозитория и филиалов |
| architecture | `chunks/02-architecture.md` | Роутинг, тема, плагины, формы |
| branches | `chunks/03-branches.md` | Модель филиалов, cookie, добавление |
| content-model | `chunks/04-content-model.md` | WP Admin: рубрики, ACF, прайс |
| animals-branch | `chunks/05-animals-branch-almaty-aubakirova.md` | МРТ для животных, Аубакирова |
| templates | `chunks/06-templates.md` | PHP-шаблоны страниц |
| deploy-wp-admin | `chunks/07-wp-admin-checklist.md` | Чеклист настройки на проде |
| animals-contextual-ads | `chunks/08-animals-contextual-ads.md` | Контекстная реклама МРТ животным, seov, UTM, Метрика |
| seo-module-kz | `chunks/09-seo-module-kz.md` | SEO + Schema.org без AIOSEO |
| metrika-forms-routing | `chunks/10-metrika-forms-routing.md` | Метрика по городам, email форм, routing |
| session-2026-07-07 | `chunks/11-session-2026-07-07.md` | Итоги сессии 07.07.2026 |

## Локальная папка seov (не в Git)

Кампании, CSV семантики и объявления — в `seov/` (см. `.gitignore`). RAG-чанк `08-animals-contextual-ads.md` хранит историю и ссылки.

## Ключевые файлы кода

- `wp-content/themes/MRT Lider/inc/mrt-city-config.php` — конфиг филиалов, метрика, form_email
- `wp-content/themes/MRT Lider/inc/mrt-city-routing.php` — rewrite и city URLs
- `wp-content/themes/MRT Lider/home-animals.php` — лендинг для животных
- `wp-content/themes/MRT Lider/seo-config.php` — SEO, OG, JSON-LD, sitemap (без AIOSEO)
- `wp-content/themes/MRT Lider/assets/js/mrt-metrika.js` — UTM + reachGoal
- `wp-content/themes/MRT Lider/assets/css/animals.css` — стили animals-филиала

## Обновление RAG

При изменении архитектуры или добавлении филиала:
1. Обновить соответствующий chunk в `docs/rag/chunks/`
2. Добавить запись в `docs/rag/manifest.json` при новом топике
