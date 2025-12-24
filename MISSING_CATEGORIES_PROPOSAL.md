# Missing Categories Proposal

**Date:** December 24, 2025  
**Status:** 🔴 Action Required

---

## Problem

With 113 real games, only 10 categories is insufficient. Many games are under-categorized:
- Resident Evil: Only "Horror" (missing Action, Survival, TPS)
- Dark Souls: Only "RPG" (missing Action, Souls-like)
- Zelda: Only "RPG" (should be Action-Adventure!)
- GTA V: Only "Shooter" (missing Action, Open World)

---

## Proposed New Categories

### 🔴 Critical (Needed for Current Games)

1. **Action**
   - **Description:** Fast-paced combat and action-focused gameplay
   - **Games:** Resident Evil, Dark Souls, Zelda, GTA, RDR2, Sekiro, Bloodborne
   - **Count:** ~40+ games

2. **Adventure**
   - **Description:** Exploration and story-driven experiences
   - **Games:** Zelda series, Dark Souls, Resident Evil, Uncharted
   - **Count:** ~25+ games

3. **Action-Adventure**
   - **Description:** Mix of action combat and exploration/story
   - **Games:** All Zelda games, Resident Evil 4+, Uncharted, Tomb Raider
   - **Count:** ~20+ games

4. **Souls-like**
   - **Description:** Challenging action RPGs with stamina-based combat
   - **Games:** Dark Souls 1-3, Demon's Souls, Bloodborne, Sekiro, Elden Ring
   - **Count:** 6 games (currently only in RPG)

5. **Metroidvania**
   - **Description:** Non-linear platformers with exploration and backtracking
   - **Games:** Hollow Knight, Ori, Metroid series, Dead Cells
   - **Count:** ~8 games

6. **Open World**
   - **Description:** Large explorable worlds with freedom of movement
   - **Games:** GTA V, RDR2, Elden Ring, BotW, TotK, Minecraft, Skyrim
   - **Count:** ~15+ games

### 🟡 High Priority (For Future Games)

7. **Third-Person Shooter (TPS)**
   - **Description:** Shooter games with third-person camera
   - **Games:** RE4+, GTA, RDR2, Fortnite
   - **Count:** ~10+ games

8. **Stealth**
   - **Description:** Games with sneaking and stealth mechanics
   - **Games:** Metal Gear Solid (future), Hitman (future), Dishonored (future)
   - **Count:** 0 current, many future

9. **Strategy**
   - **Description:** Real-time or turn-based strategy games
   - **Games:** StarCraft (future), Civilization (future), XCOM (future)
   - **Count:** 0 current, many in OPENGAMELIST.md

10. **Puzzle**
    - **Description:** Puzzle-solving focused games
    - **Games:** Portal (future), The Witness (future), Tetris Effect (future)
    - **Count:** 0 current, many future

### 🟢 Lower Priority (Future Expansion)

11. **Simulation**
    - **Description:** Life/city/vehicle simulation games
    - **Games:** The Sims (future), Cities: Skylines (future)

12. **Racing**
    - **Description:** Car/vehicle racing games
    - **Games:** Mario Kart (future), Forza (future)

13. **Sports**
    - **Description:** Sports simulation games
    - **Games:** FIFA (future), NBA 2K (future)

14. **Card Game**
    - **Description:** Digital card games
    - **Games:** Slay the Spire (already added but needs category!)

15. **Tower Defense**
    - **Description:** Defensive strategy games
    - **Games:** Bloons (future)

---

## Recommended Immediate Additions

### Phase 1: Core Genre Categories (6 new categories)

Add these **now** for current games:

1. ✅ **Action** - ~40 games need this
2. ✅ **Adventure** - ~25 games need this
3. ✅ **Action-Adventure** - ~20 games need this
4. ✅ **Souls-like** - 6 games need this
5. ✅ **Metroidvania** - ~8 games need this
6. ✅ **Open World** - ~15 games need this

**New Total:** 16 categories (currently 10)

---

## Example Re-Categorization

### Resident Evil Series
**Current:** Horror  
**Proposed:** Horror, Action, Survival, Action-Adventure

### Dark Souls Series
**Current:** RPG  
**Proposed:** RPG, Action, Souls-like, Adventure

### Zelda: Breath of the Wild
**Current:** RPG  
**Proposed:** Action-Adventure, Adventure, Open World, (RPG optional)

### Hollow Knight
**Current:** Platformer  
**Proposed:** Platformer, Metroidvania, Action

### Elden Ring
**Current:** RPG  
**Proposed:** RPG, Action, Souls-like, Adventure, Open World

### GTA V
**Current:** Shooter  
**Proposed:** Action, Adventure, Shooter, Open World, Third-Person Shooter

---

## Implementation Steps

1. **Add 6 new categories to CategoryFixtures.php:**
   ```php
   public const CATEGORY_ACTION = 'category_action';
   public const CATEGORY_ADVENTURE = 'category_adventure';
   public const CATEGORY_ACTION_ADVENTURE = 'category_action_adventure';
   public const CATEGORY_SOULSLIKE = 'category_soulslike';
   public const CATEGORY_METROIDVANIA = 'category_metroidvania';
   public const CATEGORY_OPEN_WORLD = 'category_open_world';
   ```

2. **Add 6 representative games to CategoryRepresentativeGameFixtures.php**

3. **Update GameFixtures.php** - Add additional categories to ~60 games

4. **Run fixtures** - Reload database with new categories

5. **Verify** - Ensure all categories have representative games

---

## Impact Analysis

### Before (10 categories):
```
Shooter:        35 games
RPG:            29 games
Platformer:     20 games
Horror:         19 games
Retro Shooter:   7 games
Roguelike:       7 games
Battle Royale:   5 games
Survival:        5 games
Fighting:        4 games
MOBA:            3 games
```

### After (16 categories estimated):
```
Shooter:        35 games (unchanged)
Action:         40+ games (NEW - most action games)
RPG:            29 games (unchanged)
Adventure:      25+ games (NEW - exploration games)
Action-Adventure: 20+ games (NEW - Zelda, RE, etc.)
Platformer:     20 games (unchanged)
Horror:         19 games (unchanged)
Open World:     15+ games (NEW - large explorable worlds)
Metroidvania:   8+ games (NEW - backtracking platformers)
Retro Shooter:  7 games (unchanged)
Roguelike:      7 games (unchanged)
Souls-like:     6 games (NEW - challenging action RPGs)
Battle Royale:  5 games (unchanged)
Survival:       5 games (unchanged)
Fighting:       4 games (unchanged)
MOBA:           3 games (unchanged)
```

---

## Recommendation

✅ **Add the 6 critical categories NOW**  
This will properly categorize our existing 113 games and provide better filtering/discovery for users.

---

**Last Updated:** December 24, 2025


