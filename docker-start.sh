#!/usr/bin/env bash
# Challenge Picker - Docker Setup (Unix/Linux/macOS/WSL)

# Detect if terminal supports colors
if [ -t 1 ] && command -v tput > /dev/null 2>&1 && [ "$(tput colors)" -ge 8 ]; then
    GREEN='\033[0;32m'
    BLUE='\033[0;34m'
    YELLOW='\033[1;33m'
    NC='\033[0m'
else
    GREEN=''
    BLUE=''
    YELLOW=''
    NC=''
fi

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Challenge Picker - Docker Setup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${YELLOW}WARNING: Docker is not running. Please start Docker Desktop and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}[OK] Docker is running${NC}"
echo ""

# Start Docker Compose
echo -e "${BLUE}Starting Docker containers...${NC}"
docker-compose up -d

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}WARNING: Failed to start containers. Check the error messages above.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}[OK] Containers started successfully${NC}"
echo ""

# Check if vendor directory exists in backend
if [ ! -d "backend/vendor" ]; then
    echo -e "${BLUE}Installing Symfony dependencies (first time setup)...${NC}"
    docker-compose exec -T php composer install
    echo -e "${GREEN}[OK] Symfony dependencies installed${NC}"
    echo ""
fi

# Wait for MySQL to be ready
echo -e "${BLUE}Waiting for MySQL to be ready...${NC}"
sleep 5

# Run migrations
echo -e "${BLUE}Running database migrations...${NC}"
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}WARNING: Migration failed or no migrations to run${NC}"
else
    echo -e "${GREEN}[OK] Migrations completed${NC}"
fi
echo ""

# Display access information
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}Your development environment is ready!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

echo -e "${GREEN}Frontend (Nuxt):${NC}     http://localhost:3000"
echo -e "${GREEN}Backend API (Symfony):${NC} http://localhost:8090"
echo -e "${GREEN}phpMyAdmin:${NC}          http://localhost:8080"
echo -e "  - Username: ${YELLOW}root${NC} / Password: ${YELLOW}rootpassword${NC}"
echo -e "  - Or use: ${YELLOW}user${NC} / ${YELLOW}password${NC}"
echo ""

echo -e "${BLUE}To view logs:${NC} docker-compose logs -f"
echo -e "${BLUE}To stop:${NC}      docker-compose down"
echo ""

echo -e "${GREEN}Happy coding!${NC}"
echo ""

