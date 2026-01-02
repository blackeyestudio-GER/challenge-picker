# 🃏 Tarot Card System - Challenge Picker

## Structure

### 78-Card Tarot Deck

**Minor Arcana (56 cards)** - Temporary viewer-voted challenges:
- **Numbered 1-10** (40 cards) → **Basic Rules**: 10 difficulty levels, 1-10 minutes
- **Court Cards** (16 cards) → **Court Rules**: 4 difficulty levels, 10/15/20/25 minutes

**Major Arcana (22 cards)** - Permanent challenges:
- → **Legendary Rules**: No duration, entire playthrough

---

## Database Mapping

```
rule_type = 'basic'     → Minor Arcana (1-10), Duration: 1-10 min
rule_type = 'court'     → Minor Arcana (Court), Duration: 10/15/20/25 min
rule_type = 'legendary' → Major Arcana (22), Duration: Permanent
```

---

## Rarity Colors & Ranking

**Visual Theme Colors** (defined in `assets/css/themes/default.css`):

### Legendary (Major Arcana) - Gold/Yellow
- **Color**: `rgb(234 179 8)`
- **Rarity**: Highest
- **Usage**: Permanent rules, entire playthrough
- **Cards**: 22 Major Arcana cards

### Court (Face Cards) - Purple
- **Color**: `rgb(168 85 247)`
- **Rarity**: High
- **Usage**: Long-duration rules (10/15/20/25 min)
- **Cards**: 16 Court cards (Page, Knight, Queen, King × 4 suits)

### Basic - Split by Difficulty
#### Common Basic (Difficulty 1-5) - Grey
- **Color**: `rgb(107 114 128)`
- **Rarity**: Common
- **Usage**: Short-duration rules (1-5 min)
- **Cards**: 20 numbered cards (Ace-5 × 4 suits)

#### Magical Basic (Difficulty 6-10) - Blue
- **Color**: `rgb(59 130 246)`
- **Rarity**: Uncommon
- **Usage**: Medium-duration rules (6-10 min)
- **Cards**: 20 numbered cards (6-10 × 4 suits)

**Ranking** (Lowest to Highest):
1. Common Basic (Grey) - Difficulty 1-5
2. Magical Basic (Blue) - Difficulty 6-10
3. Court (Purple) - 10/15/20/25 min
4. Legendary (Gold/Yellow) - Permanent

---

## Ruleset Limits

**Per Ruleset:**
- **Default Rules**: 0-22 legendary (permanent, auto-start)
- **Optional Rules**: Up to 8 (viewer-voted, basic or court)
- **Total Maximum**: 30 rules (22 + 8)

**During Gameplay:**
- **Concurrent Active**: 1-10 rules simultaneously (player chooses)

---

## Best Practices

**DO:**
- ✅ Keep optional rules to 6-8 maximum
- ✅ Mix basic (1-10 min) and court (10-25 min)
- ✅ Use legendary for permanent challenge identity
- ✅ Short timers = constant viewer engagement

**DON'T:**
- ❌ Exceed 8 optional rules (breaks tarot suit concept)
- ❌ Add redundant rules (Walking Only = No Sprint)
- ❌ Use counters in obvious scenarios (pistol counter in pistol-only)
- ❌ Make everything legendary (reduces viewer interaction)

---

## Creating Rules

**Basic**: Always 10 difficulty levels (1-10 minutes)
**Court**: Always 4 difficulty levels (10, 15, 20, 25 minutes)
**Legendary**: No duration, permanent effect

**Duration Formula:**
- Basic: `difficulty_level` minutes
- Court: `10 + ((difficulty_level - 1) × 5)` minutes

---

## Recommended Ruleset Structure

```
1-3 default legendary rules (challenge identity)
+ 6-8 optional viewer rules (viewer engagement)
= Balanced chaos
```

---

**See Also:** `.cursorrules` for implementation details
