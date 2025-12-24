# Category Representative Games

## Overview
Each of the 10 categories has a dedicated **representative game** that serves as a placeholder for category-level rulesets. These games are abstract (not real games) and enable ruleset inheritance.

## Purpose
- **Ruleset Inheritance**: Rulesets attached to category representative games are automatically available to all games in that category
- **No Steam Links**: Representative games have NO external store links (Steam, Epic, GOG) - they're purely for organization
- **One Per Category**: Each category has exactly one representative game
- **Same Name**: Representative game is named exactly the same as the category

## The 10 Representative Games

| ID | Category | Representative Game | Description |
|----|----------|---------------------|-------------|
| 1 | Shooter | **Shooter** | Generic shooter game for category ruleset inheritance |
| 2 | Horror | **Horror** | Generic horror game for category ruleset inheritance |
| 3 | RPG | **RPG** | Generic RPG game for category ruleset inheritance |
| 4 | Roguelike | **Roguelike** | Generic roguelike game for category ruleset inheritance |
| 5 | MOBA | **MOBA** | Generic MOBA game for category ruleset inheritance |
| 6 | Platformer | **Platformer** | Generic platformer game for category ruleset inheritance |
| 7 | Retro Shooter | **Retro Shooter** | Generic retro shooter game for category ruleset inheritance |
| 8 | Survival | **Survival** | Generic survival game for category ruleset inheritance |
| 9 | Fighting | **Fighting** | Generic fighting game for category ruleset inheritance |
| 10 | Battle Royale | **Battle Royale** | Generic battle royale game for category ruleset inheritance |

## Database Structure

### Unique Constraint
The `categories` table has a **unique constraint** on `representative_game_id`:
- Each category can have **at most ONE** representative game
- Each representative game can be linked to **at most ONE** category
- Database-level enforcement prevents data corruption

```sql
-- Constraint: UNIQ_3AF34668A1C01850
-- Ensures: Only one game can be representative per category

-- Category Representative Games Query
SELECT 
    c.id,
    c.name as category,
    c.representative_game_id,
    g.name as representative_game
FROM categories c 
LEFT JOIN games g ON c.representative_game_id = g.id
ORDER BY c.name;
```

### Testing the Constraint
```sql
-- This will FAIL with "Duplicate entry" error:
UPDATE categories SET representative_game_id = 1 WHERE id = 2;
-- Error: SQLSTATE[23000]: Integrity constraint violation: 1062
-- Message: Duplicate entry '1' for key 'categories.UNIQ_3AF34668A1C01850'
```

## Fixture Files

### `CategoryRepresentativeGameFixtures.php`
- Creates the 10 category representative games
- Loads AFTER `CategoryFixtures`
- Each game:
  - Has `is_category_representative = true`
  - Is linked to exactly one category
  - Has no Steam/Epic/GOG links
  - Has a simple descriptive name matching the category

### `GameFixtures.php`
- Creates 113 real games (Call of Duty, Resident Evil, Zelda, Mario, etc.)
- Loads AFTER `CategoryFixtures`
- All games have `is_category_representative = false`
- Many games have Steam links, Twitch categories, etc.

## Database Verification

**Total Games**: 123
- **Real Games**: 113 (with Steam links, Twitch categories, etc.)
- **Representative Games**: 10 (one per category, no external links)

**All Real Games with Steam Links**: 72 (none are marked as representatives ✅)

## How It Works

1. Admin creates a ruleset for the "Horror" representative game
2. Any game in the Horror category (Resident Evil, Silent Hill, etc.) inherits these rulesets
3. Users can select inherited rulesets when creating playthroughs
4. Inherited rulesets are marked as such in the UI

## Benefits

✅ **Cleaner Data**: Real games aren't polluted with `is_representative` flag
✅ **Scalability**: Add hundreds of games per category without confusion
✅ **Flexibility**: Category-wide rules vs game-specific rules
✅ **Clear Separation**: Abstract placeholder games vs real playable games
✅ **Data Integrity**: Database-level unique constraint prevents duplicate representatives
✅ **Cannot Break**: Impossible to assign the same game as representative for 2 categories

---

**Last Updated:** December 24, 2025
