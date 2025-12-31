# 🎨 Challenge Picker - Complete Theme System

## Overview

Challenge Picker uses a **comprehensive CSS-based theme system** with:
- ✅ **All colors themed**: Backgrounds, text, borders, buttons, cards, icons
- ✅ **Separate CSS files** per theme for easy management
- ✅ **One-click theme switching** for users
- ✅ **CSS custom properties** (variables) for consistency
- ✅ **Persistent theme selection** (saved to localStorage)

---

## Theme Structure

### Theme Files

Each theme is a **separate CSS file** in `assets/css/themes/`:

```
assets/css/themes/
├── default.css    # Dark theme (default)
└── light.css      # Light theme
```

### How It Works

1. **Default theme** (`default.css`) uses `:root` selector (applies automatically)
2. **Other themes** use class selectors (e.g., `.theme-light`)
3. **Theme switcher** adds/removes classes on `<html>` element
4. **All themes** are imported in `main.css` (no runtime loading needed)

---

## Complete Color System

### Base Colors

```css
--color-bg-primary          /* Main background */
--color-bg-secondary        /* Secondary background */
--color-bg-tertiary         /* Tertiary background */
--color-bg-card             /* Card background */
--color-bg-card-hover       /* Card hover background */
--color-bg-overlay          /* Overlay/modal background */
```

### Text Colors

```css
--color-text-primary        /* Main text */
--color-text-secondary      /* Secondary text */
--color-text-tertiary       /* Tertiary text */
--color-text-muted          /* Muted text */
--color-text-inverse        /* Text on light backgrounds */
```

### Border Colors

```css
--color-border-primary      /* Default border */
--color-border-secondary    /* Hover border */
--color-border-accent       /* Accent border (cyan) */
```

### Accent Colors

```css
--color-accent-primary      /* Cyan - primary accent */
--color-accent-primary-hover
--color-accent-primary-dark
--color-accent-secondary     /* Magenta - secondary accent */
--color-accent-secondary-hover
--color-accent-secondary-dark
```

### Button Colors

```css
--color-btn-primary-bg      /* Primary button background */
--color-btn-primary-text     /* Primary button text */
--color-btn-primary-hover    /* Primary button hover */

--color-btn-secondary-bg     /* Secondary button */
--color-btn-success-bg        /* Success button */
--color-btn-danger-bg         /* Danger button */
--color-btn-warning-bg        /* Warning button */
```

### Card Colors

```css
--color-card-bg              /* Card background */
--color-card-border          /* Card border */
--color-card-hover-border    /* Card hover border */
--color-card-shadow          /* Card shadow */

--color-card-category-bg     /* Category card background */
--color-card-category-border /* Category card border */
--color-card-category-text   /* Category card text */
```

### Icon Colors

```css
--color-icon-primary         /* Primary icon color */
--color-icon-secondary       /* Secondary icon color */
--color-icon-muted          /* Muted icon color */
--color-icon-success         /* Success icon color */
--color-icon-danger          /* Danger icon color */
--color-icon-warning         /* Warning icon color */
```

### Rule Type Colors (Tarot-Based)

```css
/* Legendary (Major Arcana) - Gold/Yellow */
--rule-legendary-bg
--rule-legendary-text
--rule-legendary-border
--rule-legendary-icon

/* Court (Face Cards) - Purple */
--rule-court-bg
--rule-court-text
--rule-court-border
--rule-court-icon

/* Basic (Number Cards) - Blue */
--rule-basic-bg
--rule-basic-text
--rule-basic-border
--rule-basic-icon
```

### Status Colors

```css
--status-active-bg/text/border
--status-completed-bg/text/border
--status-failed-bg/text/border
--status-pending-bg/text/border
--status-inactive-bg/text/border
```

---

## Usage

### Theme Switcher Component

Add the theme switcher anywhere in your app:

```vue
<template>
  <ThemeSwitcher />
</template>

<script setup>
import ThemeSwitcher from '~/components/ThemeSwitcher.vue'
</script>
```

### Programmatic Theme Switching

```vue
<script setup>
import { useThemeSwitcher } from '~/composables/useThemeSwitcher'

const { switchTheme, toggleTheme, currentTheme } = useThemeSwitcher()

// Switch to specific theme
switchTheme('light')

// Toggle between light/dark
toggleTheme()

// Get current theme
console.log(currentTheme.value) // 'default' or 'light'
</script>
```

### Using Theme Variables in CSS

```css
.my-component {
  background-color: var(--color-bg-card);
  color: var(--color-text-primary);
  border: 1px solid var(--color-border-primary);
}

.my-button {
  background: var(--color-btn-primary-bg);
  color: var(--color-btn-primary-text);
}
```

### Using Theme Utility Classes

```vue
<template>
  <!-- Backgrounds -->
  <div class="bg-theme-primary">Content</div>
  <div class="bg-theme-card">Card</div>

  <!-- Text -->
  <p class="text-theme-primary">Main text</p>
  <p class="text-theme-secondary">Secondary text</p>

  <!-- Buttons -->
  <button class="btn-primary">Primary</button>
  <button class="btn-secondary">Secondary</button>
  <button class="btn-success">Success</button>
  <button class="btn-danger">Danger</button>

  <!-- Cards -->
  <div class="card">Regular card</div>
  <div class="card-category">Category card</div>

  <!-- Icons -->
  <Icon class="icon-primary" name="star" />
  <Icon class="icon-success" name="check" />
</template>
```

---

## Creating New Themes

### Step 1: Create Theme File

Create `assets/css/themes/my-theme.css`:

```css
/**
 * My Custom Theme
 */

.theme-my-theme {
  /* Base Colors */
  --color-bg-primary: #ffffff;
  --color-bg-secondary: #f9fafb;
  --color-text-primary: #111827;
  
  /* ... all other variables ... */
  
  /* Rule Types */
  --rule-legendary-bg: rgb(234 179 8 / 0.1);
  --rule-legendary-text: rgb(250 204 21);
  /* ... etc ... */
}
```

### Step 2: Import in main.css

```css
@import './themes/default.css';
@import './themes/light.css';
@import './themes/my-theme.css';  /* Add your theme */
```

### Step 3: Add to Theme Switcher

Update `composables/useThemeSwitcher.ts`:

```typescript
export type ThemeName = 'default' | 'light' | 'my-theme'

const availableThemes: Theme[] = [
  // ... existing themes ...
  {
    name: 'my-theme',
    label: 'My Theme',
    description: 'My custom theme description'
  }
]
```

### Step 4: Use It

```typescript
switchTheme('my-theme')
```

---

## Theme Initialization

The theme is automatically initialized when the app loads (`app.vue`):

```vue
<script setup>
const { initTheme } = useThemeSwitcher()

onMounted(() => {
  initTheme() // Loads theme from localStorage or defaults to 'default'
})
</script>
```

---

## Best Practices

### ✅ DO

- Use CSS variables for all colors
- Use utility classes when available
- Create new themes by copying an existing theme file
- Test themes in both light and dark variants
- Keep rule type colors consistent across themes

### ❌ DON'T

- Hardcode colors in components
- Mix theme variables with hardcoded Tailwind colors
- Create themes without all required variables
- Forget to update all themes when adding new variables

---

## Current Themes

### Default (Dark)
- **File**: `assets/css/themes/default.css`
- **Class**: `:root` (default, no class needed)
- **Style**: Dark background with cyan/magenta accents
- **Best for**: Default experience, streaming, night use

### Light
- **File**: `assets/css/themes/light.css`
- **Class**: `.theme-light`
- **Style**: Light background with softer colors
- **Best for**: Daytime use, accessibility

---

## Migration from Old System

If you have hardcoded colors, replace them:

### Before

```vue
<div class="bg-gray-800 text-white border border-gray-700">
  Content
</div>
```

### After

```vue
<div class="bg-theme-card text-theme-primary border border-theme-primary">
  Content
</div>
```

Or use CSS variables directly:

```vue
<div style="background-color: var(--color-bg-card); color: var(--color-text-primary);">
  Content
</div>
```

---

## Technical Details

### How Theme Switching Works

1. User clicks theme switcher
2. `switchTheme()` is called
3. All theme classes removed from `<html>`
4. New theme class added (e.g., `theme-light`)
5. CSS variables update automatically
6. Theme saved to localStorage
7. Page updates instantly (no reload)

### Performance

- ✅ **No runtime CSS loading** - All themes loaded at build time
- ✅ **Instant switching** - Just class toggle, no network requests
- ✅ **Small bundle size** - CSS variables are efficient
- ✅ **No FOUC** - Theme applied before page render

---

## Future Enhancements

Potential additions:
- 🌙 **Auto theme** - Detect system preference
- 🎨 **Custom themes** - User-created themes
- 🎯 **Theme presets** - Pre-built color schemes
- 🔄 **Smooth transitions** - Animate theme changes
- 📱 **Per-device themes** - Different themes per device

---

**See Also:**
- `TAROT.md` - Rule type color meanings
- `.cursorrules` - Project conventions
