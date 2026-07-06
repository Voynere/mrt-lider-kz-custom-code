# MRT Lider KZ (mrt-lider.kz)

Локально — полная копия WordPress-сайта. В GitHub уходит только кастомный код (через `.gitignore`), как у ferma-dv и liderdent.

## Что в git

- `wp-content/themes/MRT Lider/`
- `wp-content/plugins/services-importer/`
- `.github/workflows/` — деплой на сервер
- `scripts/`, `docs/` (включая `docs/rag/` — база знаний проекта)

## Что только локально / на сервере

- Ядро WordPress
- Сторонние плагины (ACF, AIOSEO, UpdraftPlus и др.)
- `wp-config.php`, `.htaccess`
- uploads, cache, backups

## Деплой

Push в `main` → GitHub Actions rsync темы и плагина `services-importer` на прод.

Секреты: `SERVER_HOST`, `SERVER_USER`, `SERVER_PORT`, `SERVER_SSH_KEY`, `SERVER_PATH`.

Прод: `/var/www/mrt-lider.kz/data/www/mrt-lider.kz` на `91.207.75.94`.
