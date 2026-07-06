# Custom code scope

## Included in git

- `wp-content/themes/MRT Lider/`
- `wp-content/plugins/services-importer/`

## Local-only (ignored by .gitignore)

- WordPress core (`wp-admin/`, `wp-includes/`, root `wp-*.php`)
- Vendor plugins under `wp-content/plugins/`
- `wp-config.php`, `.htaccess`
- uploads, cache, backups, logs
- `llms.txt`

## Adding custom plugins later

Un-ignore the plugin in `.gitignore` and extend `.github/workflows/deploy.yml`.
