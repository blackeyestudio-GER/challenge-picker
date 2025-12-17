# Workflows

Track implementation progress. Test each feature and mark ✅ when working correctly.

---

## User Registration & Authentication

Create user accounts and authenticate via email/password or OAuth (Twitch/Discord).

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| Register with email/password | `POST /api/users` | Creates user, returns user data, hashes password | ⏳ |
| Login with email/password | `POST /api/auth/login` | Returns JWT token + user data, validates credentials | ⏳ |
| Login with Twitch OAuth | `GET /api/auth/twitch` → callback | Redirects to Twitch, creates/logs in user, returns JWT | ⏳ |
| Login with Discord OAuth | `GET /api/auth/discord` → callback | Redirects to Discord, creates/logs in user, returns JWT | ⏳ |

---

## User Profile Management

Manage user profile information and credentials.

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| Update profile | `PUT /api/users/{id}` | Updates email, username, avatar (base64, resized to 200x200) | ⏳ |
| Update password | `PUT /api/users/{id}/password` | Changes password with current password verification | ⏳ |

---

---

## Playthrough Setup (Gamehost - Authenticated)

Gamehost (logged-in user) creates and configures a playsession.

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| List games | `GET /api/games` | Returns all available games | 🔨 |
| Search/filter games | Frontend only | Filters games by name (case-insensitive, partial match) | 🔨 |
| List rulesets for game | `GET /api/games/{gameId}/rulesets` | Returns all rulesets for selected game | 🔨 |
| Create playthrough | `POST /api/playthroughs` | Creates session with game, ruleset, max concurrent rules, generates UUID | 🔨 |
| Get playthrough config | `GET /api/playthroughs/{id}` | Returns playthrough details, all rules, active/inactive status | 🔨 |
| Toggle rule in session | `PUT /api/playthroughs/{id}/rules/{ruleId}/toggle` | Activates or deactivates specific rule for this session | 🔨 |
| Update max concurrent | `PUT /api/playthroughs/{id}/concurrent` | Sets max number of rules shown at once | 🔨 |

---

## Playthrough Control (Gamehost - Authenticated)

Gamehost starts, pauses, resumes, and ends the session.

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| Get my active playthrough | `GET /api/users/me/playthrough/active` | Returns gamehost's active session or null | 🔨 |
| Start playthrough | `PUT /api/playthroughs/{id}/start` | Sets status to "active", saves started_at timestamp | 🔨 |
| Pause playthrough | `PUT /api/playthroughs/{id}/pause` | Sets status to "paused" (only from active) | 🔨 |
| Resume playthrough | `PUT /api/playthroughs/{id}/resume` | Sets status to "active" (only from paused) | 🔨 |
| End playthrough | `PUT /api/playthroughs/{id}/end` | Sets status to "completed", saves ended_at, calculates total_duration | 🔨 |

---

## Play Screen (Public - No Auth Required)

Anyone with the URL can watch the playsession.

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| View play screen (setup phase) | `GET /api/play/{uuid}` | Returns session info, shows "waiting" state for viewers | 🔨 |
| Real-time polling | `GET /api/play/{uuid}` | Auto-refreshes every 2s, updates status/timer in real-time | 🔨 |
| Session timer | Frontend (play screen) | Shows elapsed time, only runs when status is "active" | 🔨 |
| View play screen (active phase) | `GET /api/play/{uuid}` | Returns active rules with timers (to be implemented) | ⏳ |
| Share session link | Frontend (setup page) | Gamehost can copy/share link for viewers to join | 🔨 |
| Complete rule (gamehost only) | `POST /api/playthroughs/{id}/next` | Marks rule as done, returns next rule (authenticated) | ⏳ |

---

## Overlay Pages (Public - OBS/Streaming)

Separate pages for embedding into streaming overlays.

| Feature | URL | Expected Behavior | Tested |
|---------|-----|-------------------|--------|
| Timer overlay | `/play/me/timer` | Shows session timer for user's active playthrough | 🔨 |
| Rules overlay | `/play/me/rules` | Shows active rules with countdown timers | 🔨 |
| Status overlay | `/play/me/status` | Shows status badge (LIVE, PAUSED, etc.) | 🔨 |
| UUID overlays | `/play/{uuid}/timer\|rules\|status` | Shows specific playthrough (for guests/co-streams) | 🔨 |

---

## OBS Preferences (Authenticated)

User preferences for controlling overlay visibility and behavior.

| Feature | Endpoint | Expected Behavior | Tested |
|---------|----------|-------------------|--------|
| Get OBS preferences | `GET /api/users/me/obs-preferences` | Returns user's overlay visibility settings and design variants | 🔨 |
| Update OBS preferences | `PUT /api/users/me/obs-preferences` | Updates visibility, timer position, design variants | 🔨 |
| OBS sources page | `/obs-sources` | Shows permanent URLs, copy/preview buttons, design selectors | 🔨 |
| Permanent overlay URLs | `/play/me/{timer\|rules\|status}` | User's PERMANENT URLs - always show active session | 🔨 |
| Get gamehost preferences | `GET /api/play/{uuid}/preferences` | Returns design prefs for overlays (public, no auth) | 🔨 |
| Design validation | Frontend + Backend constants | Validates designs, shows supported options if invalid | 🔨 |
| Timer designs | Query param `?design=numbers` OR user's saved pref | Numbers (HH:MM:SS) - query param overrides saved pref | 🔨 |
| Status designs | Query param `?design=word\|symbols\|buttons` OR saved | Word/Symbol/Button - query param overrides | 🔨 |
| Rules designs | Query param `?design=list` OR user's saved pref | List layout - query param overrides | 🔨 |

---

## Testing Notes

- ✅ = Feature implemented and tested successfully
- 🔨 = Implemented, not yet tested
- ⏳ = Not yet implemented  
- 🐛 = Implemented but has issues (add notes)

**For Behat tests later:** Each row = one test scenario

---

## Database Design Notes

**Tables needed:**
- `games` - id, name, description, image (TEXT - base64 encoded) (seeded/manually added)
- `rulesets` - id, game_id, name, description (seeded/manually added)
- `rules` - id, ruleset_id, text, duration_minutes (seeded/manually added)
- `playthroughs` - id, user_id (gamehost), game_id, ruleset_id, uuid, max_concurrent_rules, status (setup/active/paused/completed), started_at, ended_at, total_duration
- `playthrough_rules` - id, playthrough_id, rule_id, is_active (gamehost can disable), completed_at
- `user_obs_preferences` - id, user_id, show_timer_in_{setup|active|paused|completed}, show_rules_in_{setup|active|paused|completed}, show_status_in_{setup|active|paused|completed}, timer_position (none|on_card|below_card)

**Business Rules:**
- Gamehost (authenticated user) can only have ONE active playthrough at a time
- UUID for public viewing: `/play/{uuid}` - no auth required
- Rules can be toggled active/inactive during setup phase only
- Max concurrent rules determines how many show at once during play
- Gamehost controls start/pause/end
- Viewers watch live via public URL

**User Roles:**
- **Admin**: Not implemented yet (data seeded manually)
- **Gamehost**: Logged-in user who creates and controls playsession
- **Viewer**: Anyone (no login) who watches via `/play/{uuid}`
