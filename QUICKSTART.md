# Quick Start Guide

## Starting the Development Environment

### On Windows:
```bash
docker-start.bat
```

### On Linux/macOS/WSL:
```bash
chmod +x docker-start.sh  # Only needed once
./docker-start.sh
```

### Or use Docker Compose directly (all platforms):
```bash
docker-compose up -d
```

## Access URLs

- **Frontend (Nuxt):** http://localhost:3000
- **Backend API (Symfony):** http://localhost:8090
- **phpMyAdmin:** http://localhost:8080
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
docker-compose exec frontend npm install
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

