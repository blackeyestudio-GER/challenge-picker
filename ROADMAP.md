# Challenge Picker - Roadmap to v1.0

**Current Version:** 0.8.0  
**Target:** v1.0 - Full production-ready release

---

## 📊 Progress Overview

| Category | Status | Progress |
|----------|--------|----------|
| **Core Infrastructure** | ✅ Complete | 100% |
| **Authentication & Users** | 🚧 In Progress | 80% |
| **Game & Rule Management** | ✅ Complete | 100% |
| **Admin Panel** | ✅ Complete | 100% |
| **Playthrough System** | ✅ Complete | 100% |
| **OBS Integration** | ✅ Complete | 100% |
| **Polish & UX** | 🚧 In Progress | 60% |

---

## ✅ Completed Features

### Core Infrastructure
- ✅ Makefile-based universal build system
- ✅ Docker backend (PHP, MySQL, Nginx, phpMyAdmin)
- ✅ Frontend runs locally (Nuxt.js)
- ✅ Single `.env.dist` template pattern
- ✅ Consolidated root `.gitignore`
- ✅ Doctrine Fixtures for initial data
- ✅ Single consolidated database migration
- ✅ JWT authentication
- ✅ **UUIDv7** - Time-ordered UUIDs stored as BINARY(16) for optimal performance
- ✅ PHP CS Fixer (code style - @Symfony + @PSR12)
- ✅ PHPStan level max/9 (strictest static analysis - no mixed types)

### Authentication & Users
- ✅ Email/password registration & login
- ✅ Discord OAuth (login + account linking)
- ✅ Twitch OAuth (account linking)
- ✅ User profile management
- ✅ Avatar upload (base64, auto-resize)
- ✅ Password change

### Game & Category System
- ✅ Game CRUD operations
- ✅ Category system (10 categories)
- ✅ Games can belong to multiple categories
- ✅ Category representative games (fallback)
- ✅ Steam/Epic/GOG links
- ✅ Twitch category integration
- ✅ Game search & filtering
- ✅ 44 games seeded with fixtures

### Rule System
- ✅ Rule CRUD operations
- ✅ Rule variants system (Basic/Court/Legendary)
- ✅ Tarot card integration (78 cards)
- ✅ Rule difficulty levels (1-10 for Basic, 1-4 for Court)
- ✅ Time-based rules with duration
- ✅ Rules associated with categories
- ✅ Hybrid scoring for category rules
- ✅ 84+ rules seeded with fixtures (42 unique rules × 2 variants)
- ✅ Rule search & pagination

### Ruleset System
- ✅ Ruleset CRUD operations
- ✅ Associate rules with tarot cards
- ✅ Ruleset inheritance from category representative games
- ✅ Ruleset voting system
- ✅ Favorite rulesets

### Admin Panel
- ✅ Games management (CRUD)
- ✅ Categories management (CRUD)
- ✅ Rules management (CRUD with variants)
- ✅ Rulesets management (CRUD)
- ✅ Card designs management
- ✅ Add/remove games from categories
- ✅ Protected category representative games
- ✅ Local filtering for large lists
- ✅ Admin role command: `php bin/console app:set-admin`

### Playthrough System
- ✅ Create playthrough with game + ruleset
- ✅ Public UUID-based viewing
- ✅ Setup phase (configure rules)
- ✅ Active phase (play session)
- ✅ Start/pause/resume/end controls
- ✅ Max concurrent rules setting
- ✅ Toggle rules on/off
- ✅ Session timer
- ✅ Rule completion tracking
- ✅ Real-time polling (2s intervals)

### OBS Integration
- ✅ Permanent overlay URLs (`/play/me/timer|rules|status`)
- ✅ UUID-based overlays (`/play/{uuid}/timer|rules|status`)
- ✅ OBS preferences (visibility, design, position)
- ✅ Timer designs (numbers)
- ✅ Status designs (word, symbols, buttons)
- ✅ Rules designs (list)
- ✅ Chroma key support
- ✅ Query param design overrides
- ✅ OBS sources management page

---

## 🚧 In Progress

### Authentication (For v1.0)
| Feature | Status | Priority |
|---------|--------|----------|
| Twitch OAuth login | ⏳ Planned | High |
| Email verification | ⏳ Planned | Medium |
| Password reset | ⏳ Planned | High |
| Account deletion | ⏳ Planned | Medium |

### Code Quality (For v1.0)
| Feature | Status | Priority |
|---------|--------|----------|
| Fix PHPStan level max errors (445 total) | 🚧 In Progress | High |
| Add PHPDoc blocks for all methods | ⏳ Planned | High |
| Remove all mixed types | ⏳ Planned | High |
| Add array type specifications | ⏳ Planned | High |

### Polish & UX (For v1.0)
| Feature | Status | Priority |
|---------|--------|----------|
| Loading states | 🚧 Partial | High |
| Error messages | 🚧 Partial | High |
| Success notifications | 🚧 Partial | Medium |
| Tablet-friendly polish (optional) | ⏳ Backlog | Low |
| Accessibility improvements | ⏳ Planned | Medium |
| Dark mode refinements | ⏳ Planned | Low |

---

## ⏳ Planned for v1.0

### Critical for Launch
| Feature | Description | Priority |
|---------|-------------|----------|
| **Twitch OAuth Login** | Complete login flow (not just account linking) | 🔴 Critical |
| **Password Reset** | Email-based password recovery | 🔴 Critical |
| **Desktop-first UI** | Primary layout targets desktop (OBS/streaming); phone layouts not required for v1.0 | ✅ By design |
| **Error Handling** | Comprehensive error messages & recovery | 🔴 Critical |
| **Production Config** | HTTPS, security headers, rate limiting | 🔴 Critical |

### Important but Not Blocking
| Feature | Description | Priority |
|---------|-------------|----------|
| Email Verification | Verify email on registration | 🟡 High |
| User Statistics | Playthroughs completed, rules played | 🟡 High |
| Ruleset Templates | Pre-made popular rulesets | 🟡 High |
| Game Icons | Auto-fetch from Steam API | 🟡 High |

### Nice to Have
| Feature | Description | Priority |
|---------|-------------|----------|
| Account Deletion | Self-service account deletion | 🟢 Medium |
| Export Playthrough | Download session data | 🟢 Low |
| Share to Social | Tweet/Discord share buttons | 🟢 Low |
| Ruleset Comments | Community feedback on rulesets | 🟢 Low |

---

## 🎯 v1.0 Requirements Checklist

### Backend
- [ ] Twitch OAuth login endpoint
- [ ] Password reset flow (token generation, email, validation)
- [ ] Email verification system
- [ ] Rate limiting (login, API)
- [ ] HTTPS configuration
- [ ] Security headers (CORS, CSP, etc.)
- [ ] Database backups strategy
- [ ] Monitoring & logging

### Frontend
- [x] Desktop-first layout (mobile-wide responsiveness explicitly out of scope for v1.0; optional tablet pass later)
- [ ] Loading states (all API calls)
- [ ] Error messages (all forms, API failures)
- [ ] Success notifications (CRUD operations)
- [ ] 404 / error pages
- [ ] Accessibility audit (WCAG 2.1 AA)

### Documentation
- [ ] API documentation
- [ ] Deployment guide
- [ ] User guide
- [ ] Admin guide
- [ ] Contributing guide

### Testing
- [ ] Critical path testing (register → login → create playthrough → play)
- [ ] OAuth flows tested
- [ ] Admin panel tested
- [ ] OBS overlays tested
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)

### Deployment
- [ ] Production environment setup
- [ ] CI/CD pipeline
- [ ] Database migration strategy
- [ ] SSL certificates
- [ ] CDN for assets
- [ ] Monitoring (uptime, errors)

---

## 📅 Version History

### v0.8.0 (Current)
- ✅ Complete infrastructure overhaul
- ✅ Makefile-based workflow
- ✅ Consolidated environment & git config
- ✅ Doctrine Fixtures for all initial data
- ✅ Discord OAuth complete
- ✅ Rule variants system
- ✅ Admin panel complete

### v0.7.0
- ✅ OBS integration complete
- ✅ Playthrough system working
- ✅ Overlay designs & preferences

### v0.6.0
- ✅ Basic authentication
- ✅ Game & ruleset management
- ✅ Initial admin panel

---

## 🚀 Estimated Timeline to v1.0

| Milestone | Target | Status |
|-----------|--------|--------|
| Infrastructure & Cleanup | ✅ Complete | Done |
| Core Features | ✅ Complete | Done |
| Twitch OAuth & Auth Polish | Week 1 | Next |
| Optional tablet polish | Backlog | If needed |
| Error Handling & UX Polish | Week 3 | Planned |
| Testing & Bug Fixes | Week 4 | Planned |
| Production Deployment | Week 5 | Planned |
| **v1.0 Launch** | **~5 weeks** | 🎯 |

---

## 📝 Notes

- This roadmap is updated regularly as features are completed
- Priority levels: 🔴 Critical | 🟡 High | 🟢 Medium/Low
- Status: ✅ Complete | 🚧 In Progress | ⏳ Planned
- For detailed feature specifications, see individual components and API docs
- For bug tracking and feature requests, use GitHub Issues (when available)

---

**Last Updated:** December 24, 2024  
**Next Review:** Start of each week until v1.0

