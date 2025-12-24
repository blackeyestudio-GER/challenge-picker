@echo off
REM Challenge Picker - Docker Setup (Windows)

echo ========================================
echo   Challenge Picker - Docker Setup
echo ========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo WARNING: Docker is not running. Please start Docker Desktop and try again.
    exit /b 1
)

echo [OK] Docker is running
echo.

REM Start Docker Compose
echo Starting Docker containers...
docker-compose up -d

if errorlevel 1 (
    echo WARNING: Failed to start containers. Check the error messages above.
    exit /b 1
)

echo.
echo [OK] Containers started successfully
echo.

REM Check if vendor directory exists in backend
if not exist "backend\vendor" (
    echo Installing Symfony dependencies (first time setup)...
    docker-compose exec -T php composer install
    echo [OK] Symfony dependencies installed
    echo.
)

REM Wait for MySQL to be ready
echo Waiting for MySQL to be ready...
timeout /t 5 /nobreak >nul

REM Run migrations
echo Running database migrations...
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

if errorlevel 1 (
    echo WARNING: Migration failed or no migrations to run
) else (
    echo [OK] Migrations completed
)
echo.

REM Display access information
echo ========================================
echo Your development environment is ready!
echo ========================================
echo.

echo Frontend (Nuxt):     http://localhost:3000
echo Backend API (Symfony): http://localhost:8090
echo phpMyAdmin:          http://localhost:8080
echo   - Username: root / Password: rootpassword
echo   - Or use: user / password
echo.

echo To view logs: docker-compose logs -f
echo To stop:      docker-compose down
echo.

echo Happy coding!
echo.

