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

## Ключевые файлы кода

- `wp-content/themes/MRT Lider/inc/mrt-city-config.php` — конфиг филиалов
- `wp-content/themes/MRT Lider/home-animals.php` — лендинг для животных
- `wp-content/themes/MRT Lider/assets/css/animals.css` — стили animals-филиала

## Обновление RAG

При изменении архитектуры или добавлении филиала:
1. Обновить соответствующий chunk в `docs/rag/chunks/`
2. Добавить запись в `docs/rag/manifest.json` при новом топике
