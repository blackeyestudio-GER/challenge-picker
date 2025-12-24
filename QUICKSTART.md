# Quick Start Guide

## 🚀 Easiest Way: Use Make

### First Time Setup:
```bash
make setup      # Does EVERYTHING (env, JWT, backend, DB, fixtures)
npm install     # Install frontend dependencies
```

The `make setup` command will:
- ✅ Create `backend/.env` from `backend/.env.dist`
- ✅ Generate JWT encryption keys
- ✅ Start backend services
- ✅ Install dependencies
- ✅ Run migrations
- ✅ Load initial data
- 💡 Show what credentials to configure

Then optionally edit `backend/.env` to add OAuth credentials (Discord, Twitch, Steam).

### Daily Development:
```bash
# Terminal 1: Start backend
make start

# Terminal 2: Start frontend
make dev        # or 'npm run dev'
```

### All Available Commands:
```bash
make help        # Show all commands
make env         # Create .env from .env.dist
make jwt         # Generate JWT keys
make start       # Start backend
make stop        # Stop backend
make restart     # Restart backend
make logs        # View logs
make shell       # Open PHP shell
make migrate     # Run migrations
make fixtures    # Load fixtures
make fetch-icons # Fetch game images (Twitch/Steam)
make setup       # Complete setup (first time)
make dev         # Start frontend
make cs          # Check code style (dry-run)
make cs-fix      # Fix code style
make phpstan     # Run static analysis
make qa          # Run all quality checks
make clean       # Clean up (deletes DB!)
```

### Optional: Update Game Images

Game images are included in fixtures! To update them:
```bash
make fetch-icons    # Downloads latest from Twitch/Steam (2-3 minutes)
```

---

## 🔧 Alternative: Manual Docker Commands

If you prefer to run commands manually instead of using Make:

### Step 1: Setup Environment
```bash
# Create .env from template
cp backend/.env.dist backend/.env

# Start services and generate JWT keys
docker-compose up -d mysql php
docker-compose exec php php bin/console lexik:jwt:generate-keypair

# Install dependencies and setup database
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

### Step 2: Start Frontend
```bash
npm install     # First time only
npm run dev
```

## Access URLs

- **Frontend (Nuxt, runs locally):** http://localhost:3000
- **Backend API (Symfony, in Docker):** http://localhost:8090
- **phpMyAdmin (in Docker):** http://localhost:8080
  - Username: `root` / Password: `rootpassword`
  - Or: `user` / `password`

## Useful Commands

### View logs:
```bash
docker-compose logs -f
```

### Stop containers:
```bash
docker-compose down
```

### Restart containers:
```bash
docker-compose restart
```

### Run Symfony console commands:
```bash
docker-compose exec php php bin/console [command]
```

### Run database migrations:
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### Install Composer dependencies:
```bash
docker-compose exec php composer install
```

### Install npm dependencies (frontend):
```bash
npm install
```

## Troubleshooting

### Docker not running:
- Make sure Docker Desktop is started
- Check Docker status: `docker info`

### Port conflicts:
If ports are already in use, stop other services or modify ports in `docker-compose.yml`

### Database issues:
```bash
# Reset database
docker-compose down -v
docker-compose up -d
```

### Permission issues (Linux):
```bash
# Fix file permissions
sudo chown -R $USER:$USER .
```

