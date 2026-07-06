# Доступы для агента (mrt-lider.kz)

Документ **не хранит пароли**. Чеклист, чтобы агент не просил пароли и не падал на «sensitive input required».

## SSH prod

| Параметр | Значение |
|----------|----------|
| Host | `91.207.75.94` |
| User | `root` |
| Site path | `/var/www/mrt-lider.kz/data/www/mrt-lider.kz` |
| Theme path | `.../wp-content/themes/MRT Lider` |

### Проверка (агент выполняет сам)

```bash
ssh -o BatchMode=yes root@91.207.75.94 "echo ok"
ssh -o BatchMode=yes root@91.207.75.94 "ls -la '/var/www/mrt-lider.kz/data/www/mrt-lider.kz/wp-content/themes/MRT Lider/style.css'"
curl -sS -o /dev/null -w "%{http_code}\n" https://mrt-lider.kz/
```

## GitHub Actions deploy

- Workflow: `.github/workflows/deploy.yml`
- Secrets: `SERVER_HOST`, `SERVER_USER`, `SERVER_PORT`, `SERVER_SSH_KEY`, `SERVER_PATH`
- Push в `main` → rsync темы и `services-importer` на прод, backup в `/tmp/mrt-lider-kz-deploy-backups/`

Проверка после push:

```bash
gh run list --workflow=deploy.yml --limit 3 --repo Voynere/mrt-lider-kz-custom-code
gh run view <id> --log-failed --repo Voynere/mrt-lider-kz-custom-code
```

Ручной запуск:

```bash
gh workflow run deploy.yml --repo Voynere/mrt-lider-kz-custom-code
```

## WP-CLI на сервере

Deploy workflow выполняет `wp plugin update --all` (кроме `services-importer`) и flush cache после rsync.

## Локальная копия

- Полный WordPress локально; в git только кастомная тема, плагин `services-importer`, `scripts/`, `docs/`, `.github/`
- `wp-config.php`, vendor-плагины, uploads — только локально / на сервере
