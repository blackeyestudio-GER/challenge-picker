#!/bin/bash

# Color output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Challenge Picker - Docker Setup${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Docker is not running. Please start Docker Desktop and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}\n"

# Start Docker Compose
echo -e "${BLUE}Starting Docker containers...${NC}"
docker-compose up -d

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Failed to start containers. Check the error messages above.${NC}"
    exit 1
fi

echo -e "\n${GREEN}✓ Containers started successfully${NC}\n"

# Check if vendor directory exists in backend
if [ ! -d "backend/vendor" ]; then
    echo -e "${BLUE}Installing Symfony dependencies (first time setup)...${NC}"
    docker-compose exec -T php composer install
    echo -e "${GREEN}✓ Symfony dependencies installed${NC}\n"
fi

# Wait for MySQL to be ready
echo -e "${BLUE}Waiting for MySQL to be ready...${NC}"
sleep 5

# Run migrations
echo -e "${BLUE}Running database migrations...${NC}"
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Migration failed or no migrations to run${NC}"
else
    echo -e "${GREEN}✓ Migrations completed${NC}\n"
fi

# Display access information
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}🚀 Your development environment is ready!${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${GREEN}Frontend (Nuxt):${NC}     http://localhost:3000"
echo -e "${GREEN}Backend API (Symfony):${NC} http://localhost:8090"
echo -e "${GREEN}phpMyAdmin:${NC}          http://localhost:8080"
echo -e "  └─ Username: ${YELLOW}root${NC} / Password: ${YELLOW}rootpassword${NC}"
echo -e "  └─ Or use: ${YELLOW}user${NC} / ${YELLOW}password${NC}\n"

echo -e "${BLUE}To view logs:${NC} docker-compose logs -f"
echo -e "${BLUE}To stop:${NC}      docker-compose down\n"

echo -e "${GREEN}Happy coding! 🎉${NC}\n"

