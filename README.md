# Challenge Picker

A full-stack web application for managing game challenge sessions for streamers and content creators.

## 🎮 Tech Stack

- **Backend**: Symfony 7.4 + JWT authentication
- **Frontend**: Nuxt 3 + Vue 3 + Tailwind CSS
- **Database**: MySQL with Doctrine ORM
- **DevOps**: Docker Compose

## 🚀 Quick Start

### Prerequisites
- **Docker Desktop** - [Download here](https://www.docker.com/products/docker-desktop)

### Setup Steps

**1. Complete Setup (First Time):**

```bash
make setup    # Creates .env, generates JWT keys, starts backend, installs deps, runs migrations, loads fixtures
npm install   # Install frontend dependencies
```

The `make setup` command will:
- ✅ Create `backend/.env` from `backend/.env.dist` (if it doesn't exist)
- ✅ Generate JWT encryption keys
- ✅ Start backend services (MySQL, PHP, Nginx)
- ✅ Install Composer dependencies
- ✅ Run database migrations
- ✅ Load initial data (categories, games, rules, tarot cards)
- 💡 Show you what credentials need to be configured (Discord, Twitch, Steam)

**Optional:** Update game images:
```bash
make fetch-icons    # Downloads latest images from Twitch/Steam (takes 2-3 minutes)
```

**Note:** Game images **are** included in fixtures for smooth development. The `fetch-icons` command can be used to fetch/update images as needed.

**2. Configure OAuth (Optional but recommended):**

Edit `backend/.env` to add your credentials:
- **Discord**: https://discord.com/developers/applications
- **Twitch**: https://dev.twitch.tv/console/apps
- **Steam**: https://steamcommunity.com/dev/apikey (optional)

**3. Start Development:**

```bash
# Terminal 1: Backend (if not already running)
make start

# Terminal 2: Frontend
make dev      # or 'npm run dev'
```

### 📋 Available Make Commands

```bash
make help      # Show all available commands
make env       # Create .env from .env.dist
make jwt       # Generate JWT encryption keys
make start     # Start backend services
make stop      # Stop backend services
make logs        # View backend logs
make migrate     # Run database migrations
make fixtures    # Load database fixtures
make fetch-icons # Fetch game images from Twitch/Steam (optional)
make setup       # Complete first-time setup (does everything!)
make dev         # Start frontend dev server
make cs          # Check code style (PHP CS Fixer dry-run)
make cs-fix      # Fix code style automatically
make phpstan     # Run static analysis (PHPStan)
make qa          # Run all quality checks (cs + phpstan)
make clean       # Clean up (deletes database!)
```

For more details, see:
- [ROADMAP.md](./ROADMAP.md) - Project roadmap & progress to v1.0
- [QUICKSTART.md](./QUICKSTART.md) - Quick command reference
- [backend/.env.dist](./backend/.env.dist) - Environment variables template

## 🌐 Access URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| **Frontend** (local) | http://localhost:3000 | - |
| **API** (Docker) | http://localhost:8090 | - |
| **phpMyAdmin** (Docker) | http://localhost:8080 | root / rootpassword |

## 🎯 Code Quality

This project enforces **strict code quality standards**:
- **PHP CS Fixer** - Automated code style fixing (@Symfony + @PSR12 rules)
- **PHPStan Level Max (9)** - Strictest static analysis
  - No mixed types allowed
  - Full type safety enforcement
  - Null safety checks
  - Array type specifications required
- Run `make qa` before committing to ensure code quality

**Why Level Max?** Since this is a new project built from scratch, we enforce the highest standards from day one. No legacy code means no compromises on type safety!

## ✨ Features

### 🔐 Authentication
- Email/password registration & login
- **Discord OAuth** - Login & connect Discord account
- Twitch OAuth (connect only, login coming soon)
- JWT token authentication
- Profile management with avatar upload

### 🎮 Game Management
- Browse games with search & filtering
- **Categories system** - Organize games (Shooter, Horror, RPG, etc.)
- **Multiple categories per game**
- Category representative games (fallback for niche games)
- **Steam icon fetching** - Automatic game cover art
- Admin CRUD for games, categories, rulesets, rules

### 📜 Rules & Rulesets
- **Rule Variants System**:
  - **Basic Rules**: 10 difficulty levels (The Fool → The World)
  - **Court Rules**: 4 difficulty levels (Page, Knight, Queen, King)
  - **Legendary Rules**: 1 level (ultra-hard challenges)
- **Tarot card mapping** for consistent theming
- **Time-based rules** with auto-incrementing durations
- **Category inheritance** - Rules apply to all games in category
- Search & pagination for rules

### 🎬 Session Management
- Create playthrough sessions with shareable links
- Select game, ruleset, and configure rules
- Session controls: Start, Pause, Resume, End
- One active session per user
- Public viewer pages (no login required)

### 📺 OBS Streaming Overlays
- **Permanent URLs** - `/play/me/{timer|rules|status}`
  - Automatically shows your active playthrough
  - Set once in OBS, works forever
  - Updates every 2 seconds
- **UUID-based overlays** - `/play/{uuid}/*` for specific sessions
- **Multiple design variants**:
  - Timer: Numbers (HH:MM:SS)
  - Status: Word (LIVE), Symbols (▶️), Buttons (colored)
  - Rules: List with countdown timers
- Design preferences page with preview & copy buttons
- Minimal styling for chroma key compatibility

### 👑 Admin Panel
- Manage games, categories, rulesets, rules, design sets
- Add/remove games from categories
- Set category representative games
- Protected representative games (can't be removed)
- Search, pagination, and filtering
- Form validation and error handling

## 📁 Project Structure

```
challenge-picker/
├── backend/                  # Symfony 7.4 API
│   ├── src/
│   │   ├── Controller/       # One controller per endpoint
│   │   ├── DTO/              # Request/Response objects
│   │   ├── Entity/           # Doctrine entities
│   │   ├── Repository/       # Database queries
│   │   ├── Service/          # Business logic
│   │   └── Command/          # CLI commands
│   ├── migrations/           # Database migrations
│   └── composer.json         # PHP dependencies
├── components/               # Vue components
├── composables/              # Reusable Vue logic
├── pages/                    # File-based routing
├── middleware/               # Auth & admin guards
└── docker-compose.yml        # Docker services
```

## 🏗️ Architecture Principles

- **One Endpoint = One Controller** (SOLID)
- **Request/Response DTOs** for all API endpoints
- **Service layer** for business logic
- **Composition API** with `<script setup>`
- **TypeScript** for type safety
- **Tailwind CSS** for styling

## 📚 Documentation

- **[ROADMAP.md](./ROADMAP.md)** - Project roadmap & progress to v1.0
- **[QUICKSTART.md](./QUICKSTART.md)** - Quick command reference
- **[.cursorrules](./.cursorrules)** - Development guidelines & best practices

## 🛠️ Common Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Symfony commands
docker-compose exec php php bin/console <command>

# Create migration
docker-compose exec php php bin/console make:migration

# Run migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Clear cache
docker-compose exec php php bin/console cache:clear
```

## 🔑 First Steps

1. **Start the app** - Run `make setup` (first time) or `make start` (subsequent times)
2. **Start frontend** - Run `npm install` (first time) and `make dev`
3. **Create account** - Visit http://localhost:3000 and register
4. **Set admin role** - Run:
   ```bash
   docker-compose exec php php bin/console app:set-admin your@email.com
   ```
4. **Access admin panel** - Visit http://localhost:3000/admin
5. **Add games & rules** - Use the admin interface
6. **Create session** - Go to Dashboard → New Game Session

## ⚠️ Development Only

This setup is for **development only**. Production requires:
- Environment variable configuration
- HTTPS setup
- Secure JWT secrets
- Database backups
- Rate limiting

## 📊 Database Schema

### Core Tables
- `users` - User accounts with OAuth info
- `games` - Available games with Steam icons
- `categories` - Game categories (Shooter, Horror, etc.)
- `game_categories` - Many-to-many relationship
- `rulesets` - Rule collections
- `rules` - Challenge rules with variants
- `rule_difficulty_levels` - Difficulty variants per rule
- `playthroughs` - Game sessions with UUID
- `playthrough_rules` - Active/inactive rules in session

### Design System
- `design_sets` - OBS overlay design collections
- `designs` - Individual overlay designs (timer, status, rules)

## 🤝 Contributing

This project follows strict architectural patterns. Before contributing:
1. Read [.cursorrules](./.cursorrules) for coding standards
2. Check [ROADMAP.md](./ROADMAP.md) for planned features & priorities
3. Follow the one-controller-per-endpoint pattern
4. Always use DTOs for API communication

## 📝 License

Proprietary - All rights reserved
