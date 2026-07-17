# Challenge Picker

Full-stack challenge app: Nuxt frontend + Symfony API (Docker).

## Quick start (local)

```bash
make setup    # root .env, JWT keys, services, migrations, fixtures
npm install
make start    # or: make backend && make dev
```

- Frontend: http://localhost:3000  
- API: http://localhost:8090  
- phpMyAdmin: http://localhost:8080  

Template for env vars: [`.env.example`](.env.example)

---

## Portainer deploy — environment variables

Set these under **Stacks → your stack → Environment variables** (or Advanced → env).  
Compose reads them at deploy time; you do **not** need a `backend/.env` in git.

### Required for a working stack

| Variable | Example / default | Notes |
|----------|-------------------|--------|
| `APP_ENV` | `dev` | Use `prod` only when ready for production hardening |
| `APP_SECRET` | long random string | Generate yourself (e.g. `openssl rand -hex 32`) |
| `APP_DEBUG` | `1` | `0` in production |
| `DATABASE_URL` | `mysql://user:password@mysql:3306/challenge_picker_db?serverVersion=8.0` | Host must be the Compose service name `mysql` |
| `MYSQL_DATABASE` | `challenge_picker_db` | Must match DB name in `DATABASE_URL` |
| `MYSQL_USER` | `user` | |
| `MYSQL_PASSWORD` | `password` | Change for anything beyond local lab |
| `MYSQL_ROOT_PASSWORD` | `rootpassword` | Change for anything beyond local lab |
| `JWT_PASSPHRASE` | random string | **Keep stable.** Used to encrypt the private key on first container start. Changing it later invalidates existing keys unless you regenerate them. |
| `JWT_SECRET_KEY` | `%kernel.project_dir%/config/jwt/private.pem` | Usually leave default |
| `JWT_PUBLIC_KEY` | `%kernel.project_dir%/config/jwt/public.pem` | Usually leave default |
| `JWT_TTL` | `3600` | Access-token lifetime in seconds |
| `FRONTEND_PORT` | `3000` | **Host** port for Nuxt — change if 3000 is taken (e.g. `3001`) |
| `FRONTEND_URL` | _(optional)_ | If unset, defaults to `http://localhost:${FRONTEND_PORT}` |
| `BACKEND_URL` | `http://localhost:8090` | Public URL of the API (match `NGINX_PORT`) |
| `DEFAULT_URI` | `http://localhost:8090` | Symfony router default URI |
| `NGINX_PORT` | `8090` | Host port for API |
| `MYSQL_PORT` | `3307` | Host port for MySQL |
| `PHPMYADMIN_PORT` | `8080` | Host port for phpMyAdmin |
| `API_HOST` | `http://nginx:80` | Nuxt → API proxy (Docker service name; usually leave default) |
| `NODE_ENV` | `development` | |
| `MAILER_DSN` | `null://null` | No mail; replace when you enable mailer |
| `CORS_ALLOW_ORIGIN` | `^https?://(localhost\|127.0.0.1)(:[0-9]+)?$` | Regex; adjust if you use another host/domain |

### Secrets & integrations (with where to get them)

| Variable | Required? | Where to get it |
|----------|-----------|-----------------|
| `DISCORD_CLIENT_ID` | For Discord login / link | [Discord Developer Portal → Applications](https://discord.com/developers/applications) → your app → **OAuth2** → Client ID |
| `DISCORD_CLIENT_SECRET` | For Discord login / link | Same page → **Client Secret** |
| `DISCORD_REDIRECT_URI` | If using Discord | Must match a redirect URL registered in Discord OAuth2. Default: `http://localhost:8090/api/user/connect/discord/callback` (update host/port if you change `NGINX_PORT` / public URL) |
| `TWITCH_CLIENT_ID` | Optional (account linking) | [Twitch Developer Console → Applications](https://dev.twitch.tv/console/apps) → manage app → Client ID |
| `TWITCH_CLIENT_SECRET` | Optional | Same app → New Secret |
| `TWITCH_REDIRECT_URI` | If using Twitch | Must match Twitch OAuth redirect. Default: `http://localhost:8090/api/user/connect/twitch/callback` |
| `STEAM_API_KEY` | Optional | [Steam Web API Key](https://steamcommunity.com/dev/apikey) |
| `STRIPE_SECRET_KEY` | Optional (shop) | [Stripe Dashboard → Developers → API keys](https://dashboard.stripe.com/apikeys) → Secret key |
| `STRIPE_WEBHOOK_SECRET` | Optional (shop webhooks) | [Stripe Dashboard → Developers → Webhooks](https://dashboard.stripe.com/webhooks) → endpoint → Signing secret |

Leave Twitch / Steam / Stripe at placeholders or empty if you do not use those features.

### Minimal Portainer checklist

1. Deploy stack from this repo (`docker-compose.yml`).
2. Set at least: `APP_SECRET`, `JWT_PASSPHRASE`, MySQL passwords, ports (`FRONTEND_PORT` if 3000 is busy).
3. For Discord auth: set `DISCORD_CLIENT_ID` + `DISCORD_CLIENT_SECRET` (+ redirect URI in Discord console).
4. After first start, run migrations once (e.g. Portainer console on `php`):

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

5. Optional fixtures (dev data):

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### JWT keys (no manual `make jwt` on Portainer)

You do **not** run `make jwt` after a Portainer deploy.

1. Set `JWT_PASSPHRASE` in the Stack env (pick a strong value and do not change it casually).
2. On first start the `php` entrypoint creates `private.pem` / `public.pem` with OpenSSL.
3. Keys are stored in the Docker volume `jwt_keys`, so they survive Git redeploys (the `.pem` files are gitignored and would otherwise disappear).

Local helper (optional): `make jwt` — same OpenSSL flow inside the running `php` container.
