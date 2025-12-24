.PHONY: help env start backend stop restart logs shell migrate fixtures setup clean install dev jwt cs cs-fix phpstan qa

# Colors for pretty output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)========================================$(NC)"
	@echo "$(GREEN)  Challenge Picker - Make Commands$(NC)"
	@echo "$(BLUE)========================================$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)Quick Start:$(NC)"
	@echo "  1. $(GREEN)make setup$(NC)    - Complete first-time setup"
	@echo "  2. $(GREEN)make start$(NC)    - Start all services (backend + frontend)"
	@echo ""

env: ## Create .env from .env.dist if it doesn't exist
	@if [ ! -f backend/.env ]; then \
		echo "$(BLUE)Creating backend/.env from .env.dist...$(NC)"; \
		cp backend/.env.dist backend/.env; \
		echo "$(GREEN)✓ Created backend/.env$(NC)"; \
		echo ""; \
		echo "$(RED)⚠️  IMPORTANT: Configure the following in backend/.env:$(NC)"; \
		echo ""; \
		echo "$(YELLOW)1. JWT Keys:$(NC)"; \
		echo "   Run: $(GREEN)make jwt$(NC)"; \
		echo ""; \
		echo "$(YELLOW)2. Discord OAuth (optional):$(NC)"; \
		echo "   Get credentials: $(BLUE)https://discord.com/developers/applications$(NC)"; \
		echo "   - DISCORD_CLIENT_ID"; \
		echo "   - DISCORD_CLIENT_SECRET"; \
		echo ""; \
		echo "$(YELLOW)3. Twitch OAuth (optional):$(NC)"; \
		echo "   Get credentials: $(BLUE)https://dev.twitch.tv/console/apps$(NC)"; \
		echo "   - TWITCH_CLIENT_ID"; \
		echo "   - TWITCH_CLIENT_SECRET"; \
		echo ""; \
		echo "$(YELLOW)4. Steam API (optional):$(NC)"; \
		echo "   Get key: $(BLUE)https://steamcommunity.com/dev/apikey$(NC)"; \
		echo "   - STEAM_API_KEY"; \
		echo ""; \
	else \
		echo "$(GREEN)✓ backend/.env already exists$(NC)"; \
	fi

jwt: ## Generate JWT encryption keys
	@echo "$(BLUE)Generating JWT keys...$(NC)"
	@docker-compose up -d mysql php
	@sleep 2
	@docker-compose exec php php bin/console lexik:jwt:generate-keypair --skip-if-exists
	@echo "$(GREEN)✓ JWT keys generated$(NC)"

start: ## Start all services (backend + frontend)
	@echo "$(BLUE)Starting all services...$(NC)"
	@docker-compose up -d
	@echo "$(GREEN)✓ All services started!$(NC)"
	@echo ""
	@echo "$(YELLOW)Frontend:$(NC)       http://localhost:3000"
	@echo "$(YELLOW)Backend API:$(NC)    http://localhost:8090"
	@echo "$(YELLOW)phpMyAdmin:$(NC)     http://localhost:8080"
	@echo ""

backend: ## Start only backend services (MySQL, PHP, Nginx) - excludes frontend
	@echo "$(BLUE)Starting backend services...$(NC)"
	@docker-compose up -d php nginx mysql phpmyadmin
	@echo "$(GREEN)✓ Backend services started!$(NC)"
	@echo ""
	@echo "$(YELLOW)Backend API:$(NC)    http://localhost:8090"
	@echo "$(YELLOW)phpMyAdmin:$(NC)     http://localhost:8080"
	@echo ""

stop: ## Stop all services
	@echo "$(BLUE)Stopping all services...$(NC)"
	@docker-compose down
	@echo "$(GREEN)✓ All services stopped$(NC)"

restart: ## Restart backend services
	@echo "$(BLUE)Restarting backend services...$(NC)"
	@docker-compose restart
	@echo "$(GREEN)✓ Backend services restarted$(NC)"

logs: ## View backend logs (Ctrl+C to exit)
	@docker-compose logs -f

shell: ## Open a shell in the PHP container
	@docker-compose exec php bash

migrate: ## Run database migrations
	@echo "$(BLUE)Running database migrations...$(NC)"
	@docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
	@echo "$(GREEN)✓ Migrations completed$(NC)"

fixtures: ## Load database fixtures (categories, games, rules, images)
	@echo "$(BLUE)Loading database fixtures...$(NC)"
	@docker-compose exec -T php php bin/console doctrine:fixtures:load --no-interaction
	@echo "$(GREEN)✓ Fixtures loaded (including game images)$(NC)"

fetch-icons: ## Fetch/update game images from Twitch/Steam (optional)
	@echo "$(BLUE)Fetching game images from Twitch/Steam...$(NC)"
	@echo "$(YELLOW)This may take a few minutes...$(NC)"
	@docker-compose exec -T php php bin/console app:fetch-game-icons
	@echo "$(GREEN)✓ Game images fetched$(NC)"

fetch-category-icons: ## Fetch category images from Kick.com (optional)
	@echo "$(BLUE)Fetching category images from Kick.com...$(NC)"
	@docker-compose exec -T php php bin/console app:fetch-category-icons
	@echo "$(GREEN)✓ Category images fetched$(NC)"

export-game-images: ## Export game images to fixtures file
	@echo "$(BLUE)Exporting game images...$(NC)"
	@docker-compose exec -T php php bin/console app:export-game-images 2>/dev/null | grep -A 99999 "^<?" > /tmp/game_images.php && mv /tmp/game_images.php backend/src/DataFixtures/GameImagesData.php
	@echo "$(GREEN)✓ Game images exported to GameImagesData.php$(NC)"

export-category-images: ## Export category images to fixtures file
	@echo "$(BLUE)Exporting category images...$(NC)"
	@docker-compose exec -T php php bin/console app:export-category-icons 2>/dev/null | grep -A 99999 "^<?" > /tmp/category_images.php && mv /tmp/category_images.php backend/src/DataFixtures/CategoryImagesData.php
	@echo "$(GREEN)✓ Category images exported to CategoryImagesData.php$(NC)"

install: ## Install backend dependencies (Composer)
	@echo "$(BLUE)Installing backend dependencies...$(NC)"
	@docker-compose exec -T php composer install
	@echo "$(GREEN)✓ Backend dependencies installed$(NC)"

setup: env jwt start install migrate fixtures ## Complete setup (env, jwt, start, install, migrate, fixtures)
	@echo ""
	@echo "$(BLUE)========================================$(NC)"
	@echo "$(GREEN)  Backend Setup Complete!$(NC)"
	@echo "$(BLUE)========================================$(NC)"
	@echo ""
	@echo "$(YELLOW)Backend API:$(NC)    http://localhost:8090"
	@echo "$(YELLOW)phpMyAdmin:$(NC)     http://localhost:8080"
	@echo "  - Username: $(YELLOW)root$(NC) / Password: $(YELLOW)rootpassword$(NC)"
	@echo ""
	@echo "$(BLUE)Next: Start the frontend:$(NC)"
	@echo "  $(GREEN)make start$(NC)   - Start all services (backend + frontend)"
	@echo "  $(GREEN)make dev$(NC)     - Start frontend only (in watch mode)"
	@echo ""
	@echo "$(YELLOW)Frontend:$(NC)       http://localhost:3000 (after starting)"
	@echo ""

dev: ## Start frontend in watch mode (rebuilds on code changes)
	@echo "$(BLUE)Starting frontend container...$(NC)"
	@docker-compose up frontend

cs: ## Check code style with PHP CS Fixer (dry-run)
	@echo "$(BLUE)Checking code style...$(NC)"
	@docker-compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose
	@echo "$(GREEN)✓ Code style check complete$(NC)"

cs-fix: ## Fix code style with PHP CS Fixer
	@echo "$(BLUE)Fixing code style...$(NC)"
	@docker-compose exec -T php vendor/bin/php-cs-fixer fix --verbose
	@echo "$(GREEN)✓ Code style fixed$(NC)"

phpstan: ## Run PHPStan static analysis
	@echo "$(BLUE)Running PHPStan analysis...$(NC)"
	@docker-compose exec -T php php -d memory_limit=512M vendor/bin/phpstan analyse
	@echo "$(GREEN)✓ PHPStan analysis complete$(NC)"

qa: cs phpstan ## Run all quality checks (code style + static analysis)
	@echo ""
	@echo "$(GREEN)========================================$(NC)"
	@echo "$(GREEN)  All Quality Checks Passed! ✓$(NC)"
	@echo "$(GREEN)========================================$(NC)"
	@echo ""

clean: ## Stop services and remove volumes (WARNING: deletes database)
	@echo "$(YELLOW)⚠️  This will delete all database data!$(NC)"
	@read -p "Are you sure? (y/N) " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		echo "$(BLUE)Cleaning up...$(NC)"; \
		docker-compose down -v; \
		echo "$(GREEN)✓ Cleaned up$(NC)"; \
	else \
		echo "$(YELLOW)Cancelled$(NC)"; \
	fi

db-reset: clean setup ## Reset database and reload fixtures
	@echo "$(GREEN)✓ Database reset complete$(NC)"

