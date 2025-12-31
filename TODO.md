# 🎯 Challenge Picker - Remaining Tasks

## ✅ What's Complete
- [x] Database schema (design sets, card designs, rule icons, shop)
- [x] Backend API endpoints (designs, icons, shop, stripe)
- [x] Admin UI (categories, games, rulesets, rules, designs, icons)
- [x] Icon download system (90 icons from game-icons.net GitHub)
- [x] Icon customization (color, brightness, opacity)
- [x] Template vs full artwork design system
- [x] Admin navigation component
- [x] 259 rulesets across all categories
- [x] 90+ rules with difficulty variants
- [x] **Stripe shop integration** (checkout, webhooks, purchases)
- [x] **Shop UI** (browse, buy, success pages)

---

## ✅ Phase 1: Icon Integration COMPLETE!

### 1.1 Icon Picker Modal ✅
**Goal:** Let admins assign icons to rules  
**Location:** `components/modal/IconPickerModal.vue`

**Features:**
- ✅ Search icons by name/category/tags
- ✅ Filter by category dropdown
- ✅ Visual grid with SVG preview
- ✅ Click to select → emit icon identifier
- ✅ Selected icon preview
- ✅ Responsive design
- ✅ **Color customization** (hex color picker)
- ✅ **Brightness control** (0.0-2.0 slider)
- ✅ **Opacity control** (0-100% slider)
- ✅ **Live preview** with CSS filters
- ✅ Reset to defaults button

**Database:**
- ✅ `rules.icon_color` VARCHAR(7)
- ✅ `rules.icon_brightness` DECIMAL(3,2)
- ✅ `rules.icon_opacity` DECIMAL(3,2)

**Next:** Integrate into rule create/edit forms

### 1.2 Assign Icons to Rules ✅ (57% Complete)
**Goal:** Link existing rules to appropriate icons  
**Status:** 134/236 rules have icons assigned

**Done:**
- ✅ Infrastructure in place (Rule.iconIdentifier field)
- ✅ RuleFixtures updated with icon support
- ✅ 134 rules mapped to icons
- ✅ Fixtures loading successfully

**Remaining:**
- 102 rules still need icons (mostly legendary & counter rules)
- Can be added iteratively as needed

### 1.3 "New Icon" Upload ⏳
**Goal:** Let admins upload custom SVG icons  
**Location:** Backend controller + modal

**Features:**
- SVG file upload validation
- Metadata form (name, category, tags, color)
- Preview before save
- Store in database

**Files to create:**
- `backend/src/Controller/Api/Admin/Icon/CreateRuleIconController.php`
- `components/modal/IconUploadModal.vue`

---

## 🎨 Phase 2: Card Display System (Priority: HIGH)

### 2.1 TarotCard Component
**Goal:** Display cards with icon composition  
**Location:** `components/TarotCard.vue`

**Features:**
- Props: rule, designSet, cardDesign
- Handle both full artwork and templates
- For templates: overlay icon on frame
- Add duration/amount text overlays
- Add rule name at bottom
- Responsive sizing

**Composition Logic:**
```vue
<template>
  <div class="tarot-card">
    <!-- Full artwork: just show card image -->
    <img v-if="!isTemplate" :src="cardDesign.imageUrl" />
    
    <!-- Template: compose layers -->
    <div v-else class="card-composition">
      <img class="frame" :src="cardDesign.imageUrl" />
      <div class="icon" v-html="ruleIcon.svgContent"></div>
      <div class="duration">{{ formatDuration }}</div>
      <div class="rule-name">{{ rule.name }}</div>
    </div>
  </div>
</template>
```

### 2.2 Card Design Service Frontend
**Goal:** Fetch appropriate design with fallback  
**Location:** `composables/useCardDesign.ts`

**Features:**
- Fetch exact card design (if full set)
- Fallback to template (basic/court/legendary)
- Fetch rule icon
- Combine for display

### 2.3 Integrate Cards into Player Pages
**Goal:** Show cards in gameplay  
**Location:** Various player-facing pages

**Pages to update:**
- `/playthrough/dashboard` - Show active rule cards
- `/play/[uuid]/rules` - Show rule deck
- `/games/[id]/rulesets` - Preview ruleset cards

---

## 💰 Phase 3: Monetization (Priority: MEDIUM)

### 3.1 Design Marketplace
**Goal:** Let users browse and purchase design sets  
**Location:** `pages/designs.vue` (user-facing)

**Features:**
- Grid of design sets with preview
- Filter: Free / Premium
- Show price, theme, card count
- "Unlock" button for premium sets
- Preview modal with sample cards

### 3.2 Payment Integration
**Goal:** Process payments for premium designs  
**Options:** Stripe, PayPal, Gumroad

**Features:**
- Payment gateway integration
- User purchase history
- Unlock tracking (user → design sets)
- Receipt/invoice generation

### 3.3 User Design Selection
**Goal:** Let users choose active design set  
**Location:** User profile/settings

**Features:**
- Display owned design sets
- Select active set
- Preview before switching
- Apply to all cards

---

## 🎮 Phase 4: Player Experience (Priority: MEDIUM)

### 4.1 Card Animations
**Goal:** Make card interactions feel premium

**Features:**
- Flip animation when rule activates
- Pulse on timer expiration
- Shuffle animation when drawing
- Hover effects

### 4.2 Rule Card Deck View
**Goal:** Show ruleset as physical card deck

**Features:**
- Fanned card layout
- Click to inspect
- Drag to reorder
- Count indicator

---

## 🔧 Phase 5: Polish (Priority: LOW)

### 5.1 Icon Management
- Edit icon metadata
- Delete unused icons
- Bulk upload
- Usage statistics

### 5.2 Design Set Preview
- Multi-card preview in admin
- Generate sample cards automatically
- Export design set as ZIP

### 5.3 Advanced Card Customization
- Per-rule color overrides
- Custom fonts
- Border styles
- Holographic effects (premium)

---

## 📊 Current Status

**Database:**
- 90 icons in `rule_icons`
- 5 design sets in `design_sets`
- 165 card designs in `card_designs`
- 259 rulesets ready to display
- Icons NOT YET linked to rules ❌

**Frontend:**
- Admin UI: 100% complete ✅
- Player UI: Card display missing ❌
- Card component: Not built yet ❌

**Backend:**
- All CRUD APIs: Complete ✅
- Icon upload API: Missing ❌
- Card composition logic: Missing ❌

---

## 🎯 Recommended Order

1. **Assign icons to rules** (quick, enables card display)
2. **Build TarotCard component** (core feature)
3. **Integrate cards into player pages** (user-facing value)
4. **Icon picker modal** (admin enhancement)
5. **Design marketplace** (monetization)
6. **Icon upload** (nice-to-have)

---

## 🚀 Next Immediate Steps

1. Update `RuleFixtures.php` to assign `iconIdentifier` to all rules
2. Build `components/TarotCard.vue` component
3. Create `composables/useCardDesign.ts` for fetching designs with fallback
4. Integrate cards into `/playthrough/dashboard`
5. Test card display with both full and template designs

**Estimated time to MVP card display:** 2-3 hours

