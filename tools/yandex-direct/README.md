# Yandex Direct — Kazakhstan (MRI Animal + city landings)

CLI tools for the **KAZAKHSTAN_MRT** cabinet (`mrtlider.kazakhstan@yandex.ru`).  
Pattern copied from `mrt-lider.ru` → `tools/yandex-direct/` (API v5).

## Setup

```bash
cd tools/yandex-direct
cp .env.example .env
# fill TOKEN_KAZAKHSTAN_MRT (OAuth access_token, scope direct:api)
```

| Variable | Description |
|----------|-------------|
| `DIRECT_CLIENT_ID` | OAuth app id (reference; `14dc0a3affa647a397188a2dc56dd2e5` on RU project) |
| `DIRECT_CLIENT_LOGIN` | Agency `Client-Login` header if needed (usually empty for direct advertiser token) |
| `TOKEN_KAZAKHSTAN_MRT` | Token for `mrtlider.kazakhstan@yandex.ru` |

Alternative path (same as RU prod): `wp-content/mrt-secrets/yandex-direct.env` — **never commit**.

## Scripts

| Script | Purpose |
|--------|---------|
| `list-kz-campaigns.php` | Connection test + list campaigns/states |
| `create-animals-campaigns.php` | Create/update MRI Animal search campaigns from `seov/yandex-direct-ads.csv` |
| `audit-kz-landing-urls.php` | Check ad Hrefs point to `mrt-lider.kz` (not `.ru`) |

```bash
# dry-run (no API writes)
php create-animals-campaigns.php --dry-run

# apply (creates campaigns, ad groups, ads, keywords; sends to moderation)
php create-animals-campaigns.php --apply

# list cabinet
php list-kz-campaigns.php
```

## seov/ materials (local, gitignored)

Campaign copy and semantics live in repo root `seov/`:

- `yandex-direct-ads.csv` — 13 ads, 4 campaigns
- `semantic-core.csv` — ~60 keywords
- `minus-words.txt`, `campaign-setup.md`, `utm-templates.md`

Public agent memory: `docs/rag/chunks/08-animals-contextual-ads.md`, `docs/rag/chunks/13-yandex-direct-kz-api.md`.

## Metrika counter for animals

Use **110465113** (Almaty / MRI Animal) in campaign `CounterIds`, not RU counters.

Landing: `https://mrt-lider.kz/almaty_aubakirova/` with UTM from `seov/utm-templates.md`.

## Reports

`reports/` is gitignored. Logs written on `--apply`.
