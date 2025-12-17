# Challenge Picker

A full-stack web application with **Nuxt.js** frontend and **Symfony** backend for managing game challenge sessions.

## 🎮 Project Overview

A challenge picker system where gamehosts can create game sessions with dynamic rules for streaming/content creation.

**Tech Stack:**
- **Backend**: Symfony 7.4 + JWT authentication
- **Frontend**: Nuxt 3 with Tailwind CSS
- **Database**: MySQL with Doctrine ORM
- **DevOps**: Docker Compose for easy local development

## 🚀 Quick Start

### Prerequisites

- **Docker Desktop** - [Download here](https://www.docker.com/products/docker-desktop)

### Start Everything

```bash
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:migrate
```

## 🌐 Access URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| **Frontend** | http://localhost:8000 | - |
| **API** | http://localhost:8090 | - |
| **phpMyAdmin** | http://localhost:8080 | root / rootpassword |
| **MySQL** | localhost:3307 | user / password |

## ✅ Implemented Features

### User Management
- ✅ User registration with email/password
- ✅ JWT authentication
- ✅ Profile management (username, email, avatar)
- ✅ Password change
- ✅ Base64 avatar storage with client-side resize

### Game Session Creation (Gamehost)
- ✅ Browse available games
- ✅ Search/filter games by name
- ✅ Select ruleset for game
- ✅ Create playthrough session with UUID
- ✅ Configure rules (toggle on/off)
- ✅ Set max concurrent rules (1-10)
- ✅ One active session per user enforcement
- ✅ Share session link with viewers

### Viewer Experience
- ✅ Public play screen (`/play/{uuid}`) - no login required
- ✅ View session status (setup/waiting phase)
- ✅ See game, ruleset, and gamehost info
- ✅ Join before session starts
- ✅ Real-time updates via polling (every 2 seconds)
- ✅ Live session timer (only runs when active)

### Streaming/OBS Overlays
- ✅ **Permanent URLs** (`/play/me/{timer|rules|status}`) - Set once, works forever!
  - Automatically shows your currently active playthrough
  - No need to change URLs when starting new games
  - Updates every 2 seconds
- ✅ Timer overlay - clean number display with elapsed time
- ✅ Rules overlay - full-screen list with countdown timers
- ✅ Status overlay - game state indicator (LIVE, PAUSED, etc.)
- ✅ UUID-based overlays (`/play/{uuid}/*`) - for specific sessions (guests/co-streams)
- ✅ OBS preferences page - configure visibility, behavior, and design
- ✅ Copy/preview buttons for easy OBS setup
- ✅ Design variants with smart fallback:
  - **Timer**: Numbers (HH:MM:SS)
  - **Status**: Word (LIVE), Symbols (▶️), Buttons (colored)
  - **Rules**: List layout (text + countdown)
- ✅ Hybrid approach: Query param override OR user's saved preference
- ✅ Design validation with helpful error messages
- ✅ Centralized constants (frontend + backend in sync)
- ✅ Minimal styling - plain content for OBS chroma key
- ✅ Strategy pattern for extensible designs
- ✅ Future-proof for multiplayer/guest viewing

### Session Control (Gamehost)
- ✅ Start session (setup → active)
- ✅ Pause session (active → paused)
- ✅ Resume session (paused → active)
- ✅ End session (active/paused → completed)
- ✅ Duration tracking

### Coming Soon
- ⏳ Real-time rule display with timers during gameplay
- ⏳ Rule cycling logic (show N rules at a time)
- ⏳ Session summary screen
- ⏳ Twitch/Discord OAuth integration

## 📚 Documentation

**Core Documentation:**
- **[workflow.md](./workflow.md)** - Feature workflows and implementation status
- **[GAME_SYSTEM_FLOW.md](./GAME_SYSTEM_FLOW.md)** - Complete user flow diagrams
- **[.cursorrules](./.cursorrules)** - Development conventions for AI/developers

## 🧪 Test the Application

### 1. Add Sample Data

Open phpMyAdmin at http://localhost:8080 and run:

```sql
-- Insert sample games
INSERT INTO games (name, description, image, created_at) VALUES
('CS:GO', 'Counter-Strike: Global Offensive challenges', NULL, NOW()),
('Valorant', 'Tactical shooter with abilities', NULL, NOW());

-- Insert rulesets for CS:GO (game_id = 1)
INSERT INTO rulesets (game_id, name, description, created_at) VALUES
(1, 'Basic Rules', 'Easy challenges for beginners', NOW()),
(1, 'Hard Mode', 'Difficult challenges for pros', NOW());

-- Insert rules for "Basic Rules" (ruleset_id = 1)
INSERT INTO rules (ruleset_id, text, duration_minutes, created_at) VALUES
(1, 'Use only AWP', 5, NOW()),
(1, 'No jumping', 3, NOW()),
(1, 'Only headshots', 10, NOW()),
(1, 'Play with inverted mouse', 5, NOW()),
(1, 'No crouching', 4, NOW());
```

### 2. Create Account & Session

1. Open http://localhost:8000
2. Register an account
3. Login and go to dashboard
4. Click "New Game Session"
5. Search/select a game
6. Choose a ruleset
7. Configure rules and max concurrent
8. Copy the shareable link
9. Open the link in incognito/another browser to see viewer experience
10. Viewers see "Setting Up" waiting screen (start button not yet implemented)

## 🛠️ Common Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Symfony console commands
docker-compose exec php php bin/console <command>

# Create new migration
docker-compose exec php php bin/console make:migration

# Run migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Install frontend package
docker-compose exec frontend npm install <package>
```

## 🏗️ Architecture Principles

- **One endpoint = One controller** (SOLID principles)
- **Request/Response DTOs** for all endpoints
- **Service layer** for business logic
- **JWT authentication** for secure API access
- **Mobile-first responsive design**
- **Base64 image storage** (users & games)

## 📊 Database Schema

### Core Tables
- `users` - User accounts (gamehosts)
- `games` - Available games (CS:GO, Valorant, etc.)
- `rulesets` - Rule collections per game
- `rules` - Individual challenge rules
- `playthroughs` - Game sessions with UUID
- `playthrough_rules` - Session rules with active/inactive status

### Key Features
- UUID for public viewing (future)
- One active playthrough per user
- Base64 image storage for games

## 🎯 User Roles

- **Gamehost**: Logged-in user who creates and controls sessions
- **Viewer**: Anyone who watches via `/play/{uuid}` (future - no login required)
- **Admin**: Manual data seeding (no UI yet)

## ⚠️ Development Only

This setup is for **development only**. Don't use these credentials in production!

## 🧪 Future Testing

Behat tests will be implemented later for automated testing of workflows.
