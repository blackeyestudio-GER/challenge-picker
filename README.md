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

### Start the Application

**Windows:**
```bash
docker-start.bat
```

**Linux/macOS/WSL:**
```bash
chmod +x docker-start.sh  # Only needed once
./docker-start.sh
```

**Or manually:**
```bash
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:migrate
```

For more details, see [QUICKSTART.md](./QUICKSTART.md)

## 🌐 Access URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| **Frontend** | http://localhost:3000 | - |
| **API** | http://localhost:8090 | - |
| **phpMyAdmin** | http://localhost:8080 | root / rootpassword |

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

- **[QUICKSTART.md](./QUICKSTART.md)** - Detailed setup & commands
- **[workflow.md](./workflow.md)** - Feature workflows to implement
- **[.cursorrules](./.cursorrules)** - Development guidelines

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

1. **Start the app** - Run `docker-start.sh` or `docker-start.bat`
2. **Create account** - Visit http://localhost:3000 and register
3. **Set admin role** - Run:
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
2. Check [workflow.md](./workflow.md) for planned features
3. Follow the one-controller-per-endpoint pattern
4. Always use DTOs for API communication

## 📝 License

Proprietary - All rights reserved
