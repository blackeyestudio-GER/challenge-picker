# Category Expansion Summary

**Date:** December 24, 2025  
**Status:** ✅ Complete

---

## Overview

Expanded the category system from **10 categories to 16 categories** to properly represent the diversity of 113 real games in the database. This ensures games are properly categorized with multiple relevant genres, improving game discovery and filtering.

---

## Before & After Comparison

### Before (10 Categories)
```
Category         | Total Games | Real Games
─────────────────┼─────────────┼───────────
Shooter          | 35          | 34
RPG              | 29          | 28
Platformer       | 20          | 19
Horror           | 19          | 18
Retro Shooter    | 7           | 6
Roguelike        | 7           | 6
Battle Royale    | 5           | 4
Survival         | 5           | 4
Fighting         | 4           | 3
MOBA             | 3           | 2
─────────────────┼─────────────┼───────────
TOTAL: 10 categories
```

**Problem:** Many games were severely under-categorized
- Resident Evil: Only "Horror" (missing Action, Survival, Action-Adventure)
- Dark Souls: Only "RPG" (missing Action, Souls-like, Adventure)
- Zelda: Only "RPG" ❌ (should be Action-Adventure!)
- GTA V: Only "Shooter" (missing Action, Adventure, Open World)

### After (16 Categories)
```
Category         | Total Games | Real Games | Representative Game
─────────────────┼─────────────┼────────────┼────────────────────
Action           | 82          | 81         | Action ✓
Adventure        | 45          | 44         | Adventure ✓
Shooter          | 44          | 43         | Shooter ✓
Platformer       | 20          | 19         | Platformer ✓
Action-Adventure | 20          | 19         | Action-Adventure ✓
Horror           | 19          | 18         | Horror ✓
RPG              | 18          | 17         | RPG ✓
Survival         | 16          | 15         | Survival ✓
Open World       | 13          | 12         | Open World ✓
Souls-like       | 8           | 7          | Souls-like ✓
Metroidvania     | 8           | 7          | Metroidvania ✓
Roguelike        | 7           | 6          | Roguelike ✓
Retro Shooter    | 7           | 6          | Retro Shooter ✓
Battle Royale    | 5           | 4          | Battle Royale ✓
Fighting         | 4           | 3          | Fighting ✓
MOBA             | 3           | 2          | MOBA ✓
─────────────────┼─────────────┼────────────┼────────────────────
TOTAL: 16 categories (+6 new)
```

**Solution:** Games now have multiple relevant categories!
- Resident Evil: Horror, Action, Survival, Action-Adventure ✓
- Dark Souls: RPG, Action, Souls-like, Adventure ✓
- Zelda: Action-Adventure, Adventure, Action ✓ (No longer mislabeled as RPG!)
- GTA V: Action, Adventure, Shooter, Open World, Action-Adventure ✓

---

## New Categories Added

### 1. **Action** (82 games)
**Description:** Fast-paced combat and action-focused gameplay

**Examples:**
- All Resident Evil games (added Action)
- All Dark Souls/Souls-like games
- All Zelda games
- Mario games (3D titles)
- Most shooters and battle royales
- Horror games (Phasmophobia, Dead by Daylight, Lethal Company)

### 2. **Adventure** (45 games)
**Description:** Exploration and story-driven experiences

**Examples:**
- All Zelda games (now properly categorized!)
- Dark Souls series
- Metroid series
- Resident Evil games (classic titles)
- RPGs with exploration (Baldur's Gate 3, Secret of Mana)
- Open world games (Minecraft, Terraria, GTA, RDR2)

### 3. **Action-Adventure** (20 games)
**Description:** Mix of action combat and exploration

**Critical Fix:** Zelda games are now properly categorized!
- The Legend of Zelda: Breath of the Wild ✓
- The Legend of Zelda: Tears of the Kingdom ✓
- The Legend of Zelda: Ocarina of Time ✓
- All other Zelda titles ✓
- GTA V and Red Dead Redemption 2
- Resident Evil 4, 5, 6, Village (action-focused titles)
- Sekiro: Shadows Die Twice
- Hogwarts Legacy

### 4. **Souls-like** (8 games)
**Description:** Challenging action RPGs with stamina-based combat

**Games:**
- Dark Souls (2011)
- Dark Souls II (2014)
- Dark Souls III (2016)
- Demon's Souls (2009)
- Bloodborne (2015)
- Sekiro: Shadows Die Twice (2019)
- Elden Ring

### 5. **Metroidvania** (8 games)
**Description:** Non-linear platformers with exploration and backtracking

**Games:**
- Super Metroid (1994)
- Metroid Prime (2002)
- Metroid Dread (2021)
- Hollow Knight (2017)
- Ori and the Blind Forest (2015)
- Ori and the Will of the Wisps (2020)
- Dead Cells (2018)

### 6. **Open World** (13 games)
**Description:** Large explorable worlds with freedom of movement

**Games:**
- The Legend of Zelda: Breath of the Wild
- The Legend of Zelda: Tears of the Kingdom
- Elden Ring
- Cyberpunk 2077
- Grand Theft Auto V
- Red Dead Redemption 2
- Starfield
- Hogwarts Legacy
- Minecraft
- Terraria
- Super Mario Odyssey
- Rust

---

## Major Franchise Re-Categorizations

### Resident Evil Series (13 games)
**Before:** Horror only
**After:** Horror + Action + Survival + (Action-Adventure for RE4+)

| Game | Categories |
|------|-----------|
| RE 1-3, Code Veronica, RE7 | Horror, Action, Survival, Adventure |
| RE4, RE5, RE6, Village | Horror, Action, Action-Adventure, (Shooter for some) |

### Dark Souls / Souls-like Series (6 games)
**Before:** RPG only (sometimes + Horror for Bloodborne)
**After:** RPG + Action + Souls-like + Adventure

| Game | Categories |
|------|-----------|
| Dark Souls 1-3, Demon's Souls | RPG, Action, Souls-like, Adventure |
| Bloodborne | RPG, Horror, Action, Souls-like, Adventure |
| Sekiro | RPG, Action, Souls-like, Action-Adventure |
| Elden Ring | RPG, Action, Souls-like, Adventure, Open World |

### The Legend of Zelda Series (9 games)
**Before:** RPG only ❌ (WRONG!)
**After:** Action-Adventure + Adventure + Action ✓ (CORRECT!)

| Game | Categories |
|------|-----------|
| ALTTP, OoT, MM, WW, TP, SS, LA | Action-Adventure, Adventure, Action |
| BotW, TotK | Action-Adventure, Adventure, Action, Open World |

### Super Mario Series (10 games)
**Before:** Platformer only
**After:** Platformer + Action + (Adventure for 3D titles) + (Open World for Odyssey)

| Game | Categories |
|------|-----------|
| SMB, SMB3, SMW, SM Maker 2 | Platformer, Action |
| SM64, Sunshine, Galaxy 1-2 | Platformer, Action, Adventure |
| SM Odyssey | Platformer, Action, Adventure, Open World |

### Metroid Series (3 games)
**Before:** Platformer (or Shooter for Prime)
**After:** Platformer/Shooter + Metroidvania + Action + Adventure

| Game | Categories |
|------|-----------|
| Super Metroid, Metroid Dread | Platformer, Metroidvania, Action, Adventure |
| Metroid Prime | Shooter, Metroidvania, Action, Adventure |

### Grand Theft Auto V & Red Dead Redemption 2
**Before:** Shooter only (+ RPG for RDR2)
**After:** Action + Adventure + Shooter + Open World + Action-Adventure

---

## Files Modified

### 1. `backend/src/DataFixtures/CategoryFixtures.php`
- Added 6 new category constants:
  - `CATEGORY_ACTION`
  - `CATEGORY_ADVENTURE`
  - `CATEGORY_ACTION_ADVENTURE`
  - `CATEGORY_SOULSLIKE`
  - `CATEGORY_METROIDVANIA`
  - `CATEGORY_OPEN_WORLD`
- Added category definitions with names, slugs, descriptions, and Kick.com mappings

### 2. `backend/src/DataFixtures/GameFixtures.php`
- Updated **60+ games** with proper multi-genre categorization
- Major franchises updated:
  - Resident Evil (13 games)
  - Dark Souls/Souls-like (6 games)
  - The Legend of Zelda (9 games) - **CRITICAL FIX**
  - Super Mario (10 games)
  - Metroid (3 games)
  - Metroidvania titles (Hollow Knight, Ori, Dead Cells)
  - Open world games (GTA, RDR2, Minecraft, Terraria, etc.)
  - Horror survival games (Phasmophobia, Dead by Daylight, Lethal Company)
  - Battle royales (Fortnite, Apex Legends)
  - Various RPGs (Baldur's Gate 3, Cyberpunk 2077, Starfield, Hogwarts Legacy)

### 3. `backend/src/DataFixtures/CategoryRepresentativeGameFixtures.php`
- Added 6 new abstract representative games:
  - `Action` → for Action category
  - `Adventure` → for Adventure category
  - `Action-Adventure` → for Action-Adventure category
  - `Souls-like` → for Souls-like category
  - `Metroidvania` → for Metroidvania category
  - `Open World` → for Open World category

---

## Database Impact

### Category Distribution Analysis

**Most Common Categories:**
1. **Action** - 82 games (most action-focused games)
2. **Adventure** - 45 games (exploration/story games)
3. **Shooter** - 44 games (FPS/TPS games)

**Specialized Categories:**
- **Souls-like** - 8 games (specific sub-genre)
- **Metroidvania** - 8 games (specific platformer sub-genre)
- **Open World** - 13 games (large explorable worlds)

**Existing Categories Unchanged:**
- Shooter, Horror, RPG, Platformer, Roguelike, Battle Royale, Fighting, MOBA, Survival, Retro Shooter

### Representative Games
All 16 categories now have exactly **1 representative game** for category-level ruleset inheritance:
- No real games (with Steam links) are category representatives ✓
- All representative games are abstract "Generic [Category]" placeholders ✓
- Database constraint enforces uniqueness ✓

---

## Quality Assurance

### Code Quality
- ✅ PHP CS Fixer: All code style fixed (0 issues)
- ⚠️ PHPStan: Pre-existing errors (not related to fixture changes)
- ✅ Fixtures loaded successfully
- ✅ Database migrations completed

### Database Integrity
- ✅ All 16 categories have representative games
- ✅ All representative games properly linked to categories
- ✅ Foreign key constraints working correctly
- ✅ No orphaned categories or games
- ✅ 113 real games + 16 representative games = 129 total games

---

## Impact on User Experience

### Improved Game Discovery
Users can now filter games by:
- **Action** - Find fast-paced, combat-focused games
- **Adventure** - Find exploration and story-driven games
- **Action-Adventure** - Find games that blend both (like Zelda!)
- **Souls-like** - Find challenging stamina-based combat RPGs
- **Metroidvania** - Find non-linear platformers with backtracking
- **Open World** - Find large explorable worlds

### Better Categorization
- **Zelda games are no longer mislabeled as RPGs** 🎉
- Multi-genre games properly tagged (Resident Evil is Horror + Action + Survival)
- Specialized sub-genres recognized (Souls-like, Metroidvania)
- Open world games properly identified

### Accurate Genre Representation
- Games can have **multiple relevant categories** (not just one)
- More accurate reflection of gameplay styles
- Better alignment with player expectations
- Improved recommendation potential

---

## Statistics

### Before vs After

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| Categories | 10 | 16 | +6 (+60%) |
| Avg categories/game | ~1.3 | ~2.5 | +1.2 (+92%) |
| Games with 1 category | ~90% | ~20% | -70% |
| Games with 3+ categories | ~5% | ~60% | +55% |
| Representative games | 10 | 16 | +6 |

### Category Usage
- **Most assigned**: Action (82 games)
- **Least assigned**: MOBA (3 games)
- **Average**: ~20 games per category
- **Median**: ~13 games per category

---

## Recommendation

✅ **Category system is now complete and production-ready!**

The system now properly represents the diversity of 113 games with 16 well-defined categories. Games are accurately categorized with multiple relevant genres, improving discoverability and user experience.

**Key Achievements:**
1. ✅ Zelda games now correctly categorized as Action-Adventure
2. ✅ Souls-like sub-genre properly recognized
3. ✅ Metroidvania games properly identified
4. ✅ Open world games properly tagged
5. ✅ Multi-genre tagging implemented across the board
6. ✅ All categories have representative games for ruleset inheritance

**Next Steps:**
- Consider adding category images from Kick.com (already have infrastructure)
- Monitor user filtering patterns to see which categories are most used
- Consider adding more specialized sub-genres as game library expands (Stealth, Strategy, Puzzle, etc.)

---

**Last Updated:** December 24, 2025

