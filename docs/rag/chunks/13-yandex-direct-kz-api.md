# Яндекс Директ API — Казахстан

## Кабинет

| Метка | Логин | Env var |
|-------|-------|---------|
| `KAZAKHSTAN_MRT` | mrtlider.kazakhstan@yandex.ru | `TOKEN_KAZAKHSTAN_MRT` |

OAuth Client ID (общий с RU проектом): `14dc0a3affa647a397188a2dc56dd2e5`.

Токены **не в Git**. Локально:

```bash
tools/yandex-direct/.env          # copy from .env.example
# или
wp-content/mrt-secrets/yandex-direct.env
```

## CLI (`tools/yandex-direct/`)

| Скрипт | Назначение |
|--------|------------|
| `list-kz-campaigns.php` | Проверка токена + список РК |
| `create-animals-campaigns.php` | Создание MRI Animal кампаний из `seov/yandex-direct-ads.csv` |
| `audit-kz-landing-urls.php` | Аудит Href → должен быть `mrt-lider.kz` |

```bash
cd tools/yandex-direct
cp .env.example .env   # заполнить TOKEN_KAZAKHSTAN_MRT

php list-kz-campaigns.php
php create-animals-campaigns.php --dry-run
php create-animals-campaigns.php --apply
php audit-kz-landing-urls.php
```

Библиотеки: `bootstrap-env.php`, `direct-lib.php` (API v5, как на `mrt-lider.ru`).

## MRI Animal — материалы seov/

Папка `seov/` gitignored. См. `08-animals-contextual-ads.md`.

| utm_campaign | CSV / semantic group |
|--------------|---------------------|
| `animals_mrt_commercial` | commercial, brand, geo |
| `animals_mrt_zones` | zones |
| `animals_mrt_symptoms` | symptoms |
| `animals_mrt_b2b` | b2b |

Landing: `https://mrt-lider.kz/almaty_aubakirova/`  
Metrika counter в РК: **110465113** (не RU-счётчики).

## Что нужно от пользователя

1. OAuth `access_token` с scope `direct:api` для KZ-кабинета → `TOKEN_KAZAKHSTAN_MRT`
2. Цели Метрики 110465113 (см. `seov/utm-templates.md`)
3. `--apply` только после `--dry-run` и проверки CSV

## RU reference

Полный набор скриптов (fix-links, launch-kazakhstan, stop-list): репозиторий `mrt-lider` → `tools/yandex-direct/`.  
KZ-репозиторий содержит минимальный набор для animals + аудит домена.
