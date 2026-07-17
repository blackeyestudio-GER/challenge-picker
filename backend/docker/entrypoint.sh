#!/bin/sh
set -eu

cd /var/www/html

echo "[entrypoint] Preparing Symfony runtime..."

if [ ! -f .env.dist ]; then
  echo "[entrypoint] ERROR: .env.dist not found in /var/www/html"
  exit 1
fi

# Symfony Runtime expects a .env file to exist; secrets come from process env (Compose/Portainer).
if [ ! -f .env ]; then
  echo "[entrypoint] Creating .env from .env.dist"
  cp .env.dist .env
fi

# Bind mounts overwrite the image filesystem — ensure vendor is present
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] Installing Composer dependencies..."
  composer install --prefer-dist --no-progress --no-interaction
fi

mkdir -p config/jwt var/cache var/log
chmod -R ug+rwX var config/jwt 2>/dev/null || true

# JWT keys are gitignored. On Portainer they live in the jwt_keys volume and are
# created once on first start — no manual `make jwt` needed after deploy.
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
  echo "[entrypoint] Generating JWT keypair (openssl)..."
  if [ -z "${JWT_PASSPHRASE:-}" ]; then
    echo "[entrypoint] ERROR: JWT_PASSPHRASE is empty; set it in Portainer / root .env"
    exit 1
  fi
  openssl genpkey \
    -out config/jwt/private.pem \
    -aes256 \
    -algorithm rsa \
    -pkeyopt rsa_keygen_bits:4096 \
    -pass env:JWT_PASSPHRASE
  openssl pkey \
    -in config/jwt/private.pem \
    -passin env:JWT_PASSPHRASE \
    -pubout \
    -out config/jwt/public.pem
  chmod 644 config/jwt/public.pem
  chmod 600 config/jwt/private.pem 2>/dev/null || true
  echo "[entrypoint] JWT keypair ready"
fi

echo "[entrypoint] Starting: $*"
exec docker-php-entrypoint "$@"
