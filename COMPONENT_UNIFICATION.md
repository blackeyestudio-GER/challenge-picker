# Component Unification Opportunities

This document identifies UI elements across the application that share similar styling and use cases, and could be unified into reusable components.

## Summary

After analyzing pages and components, we've identified **8 major component categories** that could be unified, affecting **30+ instances** across the codebase.

---

## 1. Info/Help Boxes

**Current State:** Multiple implementations with similar styling but different class names.

**Locations Found:**
- `pages/obs-sources.vue` - `obs-sources-page__info-box` (cyan, info style)
- `pages/shop/success.vue` - `bg-cyan/10 border border-cyan/30` (cyan, info style)
- `pages/admin/features.vue` - `bg-blue-600/20 border border-blue-500` (blue, info style)
- `pages/admin/shop.vue` - `bg-yellow-500/10 border border-yellow-500/30` (yellow, warning style)
- `pages/playthrough/[uuid]/setup.vue` - `border-cyan bg-cyan/10` (cyan, info style)
- `pages/creator-tools.vue` - Multiple info boxes (blue, warning, etc.)

**Proposed Component:** `InfoBox.vue`

**Props:**
- `type`: `'info' | 'warning' | 'success' | 'error'` (default: `'info'`)
- `title`: `string` (optional)
- `icon`: `string` (optional, defaults based on type)
- `variant`: `'default' | 'compact'` (default: `'default'`)

**Slots:**
- `default` - Main content
- `title` - Custom title (overrides prop)
- `icon` - Custom icon (overrides prop)

**CSS Class:** `info-box`, `info-box--info`, `info-box--warning`, `info-box--success`, `info-box--error`

**Benefits:**
- Consistent styling across all info boxes
- Easy to update styling globally
- Better accessibility (semantic HTML)
- Reduces code duplication (~200+ lines)

---

## 2. Loading States

**Current State:** Nearly identical spinner + text implementations across 20+ pages.

**Locations Found:**
- `pages/playthrough/new.vue` - `playthrough-new-page__loading`
- `pages/playthrough/game/[gameId]/rulesets.vue` - `playthrough-rulesets-page__loading`
- `pages/shop.vue` - `shop-page__loading`
- `pages/my-runs.vue` - `runs-page__loading`
- `pages/browse-runs.vue` - `runs-page__loading`
- `pages/admin/features.vue` - Inline spinner
- `pages/shop/purchases.vue` - Inline spinner
- `pages/playthrough/[uuid]/setup.vue` - Inline spinner
- And 15+ more pages...

**Proposed Component:** `LoadingState.vue`

**Props:**
- `message`: `string` (default: `'Loading...'`)
- `size`: `'sm' | 'md' | 'lg'` (default: `'md'`)
- `fullPage`: `boolean` (default: `false`)

**CSS Class:** `loading-state`, `loading-state--sm`, `loading-state--md`, `loading-state--lg`

**Benefits:**
- Single source of truth for loading UI
- Consistent spinner animation
- Easy to update loading style globally
- Reduces code duplication (~300+ lines)

---

## 3. Empty States

**Current State:** Similar structure but different styling across pages.

**Locations Found:**
- `components/admin/AdminEmptyState.vue` - Already exists! (admin only)
- `pages/my-runs.vue` - `runs-page__empty` (trophy icon, message, button)
- `pages/browse-runs.vue` - `runs-page__empty` (film icon, message)
- `pages/playthrough/game/[gameId]/rulesets.vue` - `playthrough-rulesets-page__empty` (warning icon, message, button)
- `pages/shop/purchases.vue` - Inline empty state (shopping bag icon, message, button)
- `pages/admin/games.vue` - Uses `AdminEmptyState`
- `pages/admin/categories.vue` - Uses `AdminEmptyState`
- And 10+ more pages...

**Proposed Component:** `EmptyState.vue` (enhance existing `AdminEmptyState`)

**Props:**
- `icon`: `string` (default: `'heroicons:magnifying-glass'`)
- `title`: `string` (optional)
- `message`: `string` (required)
- `actionLabel`: `string` (optional)
- `actionRoute`: `string` (optional)
- `actionHandler`: `function` (optional)
- `variant`: `'default' | 'compact'` (default: `'default'`)

**Slots:**
- `default` - Custom content
- `action` - Custom action button

**CSS Class:** `empty-state`, `empty-state--compact`

**Benefits:**
- Unify admin and user-facing empty states
- Consistent empty state UX
- Easy to add new empty states
- Reduces code duplication (~250+ lines)

---

## 4. Error States

**Current State:** Similar error boxes with different styling.

**Locations Found:**
- `pages/playthrough/new.vue` - `playthrough-new-page__error`
- `pages/playthrough/game/[gameId]/rulesets.vue` - `playthrough-rulesets-page__error`
- `pages/obs-sources.vue` - `obs-sources-page__error`
- `pages/shop.vue` - Error handling
- `pages/admin/features.vue` - `bg-red-600/20 border border-red-500`
- `pages/playthrough/[uuid]/setup.vue` - `bg-red-500/20 border border-red-300`
- And 10+ more pages...

**Proposed Component:** `ErrorState.vue`

**Props:**
- `message`: `string` (required)
- `title`: `string` (optional, default: `'Error'`)
- `showRetry`: `boolean` (default: `false`)
- `retryLabel`: `string` (default: `'Retry'`)
- `showBackButton`: `boolean` (default: `false`)
- `backRoute`: `string` (optional)
- `backLabel`: `string` (default: `'Go Back'`)

**Events:**
- `retry` - Emitted when retry button clicked
- `back` - Emitted when back button clicked

**CSS Class:** `error-state`

**Benefits:**
- Consistent error handling UX
- Better error messaging
- Reduces code duplication (~200+ lines)

---

## 5. Success States

**Current State:** Success messages scattered with different styles.

**Locations Found:**
- `pages/register.vue` - `auth-page__message--success` (green, account created)
- `pages/shop/success.vue` - Full success page (green check icon, message, info box)
- `pages/profile.vue` - Success messages (green, inline)
- Various form submissions...

**Proposed Component:** `SuccessState.vue` or `SuccessMessage.vue`

**Props:**
- `message`: `string` (required)
- `title`: `string` (optional)
- `icon`: `string` (default: `'heroicons:check-circle'`)
- `variant`: `'inline' | 'full'` (default: `'inline'`)
- `showActions`: `boolean` (default: `false`)

**Slots:**
- `default` - Main message
- `actions` - Action buttons

**CSS Class:** `success-state`, `success-state--inline`, `success-state--full`

**Benefits:**
- Consistent success messaging
- Better user feedback
- Reduces code duplication (~100+ lines)

---

## 6. Page Headers

**Current State:** Similar header structure with gradient title + description.

**Locations Found:**
- `pages/dashboard.vue` - `dashboard-page__welcome` (gradient title, description)
- `pages/playthrough/new.vue` - `playthrough-new-page__header` (gradient title, description)
- `pages/playthrough/game/[gameId]/rulesets.vue` - Header with back button
- `pages/my-runs.vue` - `runs-page__header` (gradient title, description, legend)
- `pages/browse-runs.vue` - `runs-page__header` (gradient title, description, legend)
- `pages/shop.vue` - `shop-page__header` (gradient title, description, action button)
- `pages/admin/features.vue` - Header with back button, gradient title, description
- `pages/shop/purchases.vue` - Header with back button, gradient title, description
- And 15+ more pages...

**Proposed Component:** `PageHeader.vue`

**Props:**
- `title`: `string` (required)
- `description`: `string` (optional)
- `showBackButton`: `boolean` (default: `false`)
- `backRoute`: `string` (optional)
- `backLabel`: `string` (default: `'Back'`)
- `gradient`: `boolean` (default: `true`)
- `centered`: `boolean` (default: `false`)

**Slots:**
- `default` - Additional content (e.g., legend, stats)
- `actions` - Action buttons (right side)

**CSS Class:** `page-header`, `page-header--centered`

**Benefits:**
- Consistent page headers
- Easy to add back buttons
- Consistent gradient styling
- Reduces code duplication (~400+ lines)

---

## 7. Status Badges

**Current State:** Status badges with different colors but similar structure.

**Locations Found:**
- `pages/shop/purchases.vue` - Transaction status badges (green/yellow/red/gray)
- `pages/admin/features.vue` - Feature status badges (ENABLED/DISABLED)
- `pages/playthrough/[uuid]/setup.vue` - Setup phase badge (yellow)
- `pages/runs/[uuid].vue` - Status indicators
- Various admin pages - Status badges

**Proposed Component:** `StatusBadge.vue`

**Props:**
- `status`: `string` (required)
- `variant`: `'default' | 'success' | 'warning' | 'error' | 'info' | 'muted'` (optional, auto-detected from status)
- `size`: `'sm' | 'md' | 'lg'` (default: `'md'`)
- `uppercase`: `boolean` (default: `true`)

**CSS Class:** `status-badge`, `status-badge--success`, `status-badge--warning`, etc.

**Benefits:**
- Consistent status display
- Easy to add new status types
- Better accessibility
- Reduces code duplication (~150+ lines)

---

## 8. Card Components

**Current State:** Similar card structures with different class names.

**Locations Found:**
- `pages/admin/categories.vue` - Category cards
- `pages/admin/games.vue` - Game cards
- `pages/admin/rulesets.vue` - Ruleset cards
- `pages/playthrough/game/[gameId]/rulesets.vue` - Ruleset cards
- `pages/shop/purchases.vue` - Purchase cards
- `pages/shop.vue` - Design set cards (via `DesignSetCard` component)
- `pages/my-runs.vue` - Run cards
- `pages/browse-runs.vue` - Run cards
- And 10+ more pages...

**Proposed Component:** `Card.vue` (base component)

**Props:**
- `clickable`: `boolean` (default: `false`)
- `selected`: `boolean` (default: `false`)
- `hover`: `boolean` (default: `true`)
- `padding`: `'sm' | 'md' | 'lg'` (default: `'md'`)

**Slots:**
- `default` - Card content
- `header` - Card header
- `footer` - Card footer
- `actions` - Action buttons

**CSS Class:** `card`, `card--clickable`, `card--selected`, `card--hover`

**Benefits:**
- Consistent card styling
- Easy to create new card types
- Better hover/selected states
- Reduces code duplication (~500+ lines)

---

## Implementation Priority

### High Priority (Immediate Impact)
1. **LoadingState** - Used on 20+ pages, easy to implement
2. **EmptyState** - Used on 15+ pages, already have `AdminEmptyState` as base
3. **ErrorState** - Used on 10+ pages, critical for error handling
4. **PageHeader** - Used on 20+ pages, high visibility

### Medium Priority (Good ROI)
5. **InfoBox** - Used on 8+ pages, improves consistency
6. **StatusBadge** - Used on 5+ pages, easy to implement
7. **SuccessState** - Used on 3+ pages, improves UX

### Low Priority (Nice to Have)
8. **Card** - More complex, but would unify many card implementations

---

## Estimated Impact

| Component | Instances | Lines Saved | Implementation Time |
|-----------|-----------|-------------|---------------------|
| LoadingState | 20+ | ~300 | 1-2 hours |
| EmptyState | 15+ | ~250 | 2-3 hours |
| ErrorState | 10+ | ~200 | 1-2 hours |
| PageHeader | 20+ | ~400 | 2-3 hours |
| InfoBox | 8+ | ~200 | 2-3 hours |
| StatusBadge | 5+ | ~150 | 1 hour |
| SuccessState | 3+ | ~100 | 1 hour |
| Card | 10+ | ~500 | 4-5 hours |
| **Total** | **90+** | **~2,100** | **14-20 hours** |

---

## Migration Strategy

1. **Phase 1:** Create base components (`LoadingState`, `EmptyState`, `ErrorState`)
2. **Phase 2:** Migrate high-traffic pages first (dashboard, shop, playthrough)
3. **Phase 3:** Migrate admin pages (already have some components)
4. **Phase 4:** Migrate remaining pages
5. **Phase 5:** Create advanced components (`Card`, `PageHeader`)

---

## Notes

- All components should use CSS variables for theming
- Components should follow BEM methodology
- Props should be flexible enough for edge cases
- Slots should be provided for customization
- Components should be accessible (ARIA labels, keyboard navigation)

