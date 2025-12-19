# Icon Migration Guide

## ✅ Nuxt Icon Module Installed

We've switched from inline SVG icons to **nuxt-icon** - a Nuxt 3 compatible icon module that supports 100,000+ icons from multiple sets.

**Note**: Using `nuxt-icon` (v1) instead of `@nuxt/icon` (v2) because we're on Nuxt 3.x. The API and usage are identical.

### Benefits
- ✅ **No maintenance** - Icons maintained by icon libraries (Heroicons, Material Design Icons, etc.)
- ✅ **Tree-shakeable** - Only loads icons you actually use
- ✅ **Consistent** - Icons look professional and work reliably
- ✅ **Easy to use** - Simple component syntax
- ✅ **Multiple sets** - Access Heroicons, Material Design Icons, Font Awesome, etc.

## 📦 Available Icon Sets

### Heroicons (Recommended - matches Tailwind)
- **Outline**: `heroicons:home`, `heroicons:user`, `heroicons:cog`
- **Solid**: `heroicons:home-solid`, `heroicons:user-solid`, `heroicons:cog-solid`
- Browse: https://heroicons.com/

### Material Design Icons
- **Filled**: `mdi:home`, `mdi:account`, `mdi:settings`
- **Outline**: `mdi:home-outline`, `mdi:account-outline`
- Browse: https://icon-sets.iconify.design/mdi/

### Other Sets
- **Font Awesome**: `fa6-solid:house`, `fa6-regular:user`
- **Tabler Icons**: `tabler:home`, `tabler:user`
- **Lucide**: `lucide:home`, `lucide:user`
- Browse all: https://icones.js.org/

## 🎯 How to Use

### Basic Usage
```vue
<Icon name="heroicons:home" />
<Icon name="heroicons:user-circle" />
<Icon name="mdi:settings" />
```

### With Size and Color
```vue
<Icon name="heroicons:play-circle" class="w-8 h-8" />
<Icon name="heroicons:user" class="w-6 h-6 text-gray-500" />
<Icon name="mdi:home" class="w-12 h-12 text-cyan" />
```

### In Components
```vue
<button class="flex items-center gap-2">
  <Icon name="heroicons:arrow-right" class="w-5 h-5" />
  Continue
</button>
```

## 🔄 Migration Examples

### ❌ Before (Inline SVG)
```vue
<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>
```

### ✅ After (Icon Component)
```vue
<Icon name="heroicons:play-circle" class="w-8 h-8" />
```

---

### ❌ Before (User Icon)
```vue
<svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
</svg>
```

### ✅ After
```vue
<Icon name="heroicons:user-circle-solid" class="w-5 h-5 text-gray-500" />
```

---

### ❌ Before (Arrow)
```vue
<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
</svg>
```

### ✅ After
```vue
<Icon name="heroicons:arrow-left" class="w-6 h-6" />
```

## 📋 Files to Migrate

### ✅ Completed
- `pages/dashboard.vue` - Updated navigation icons and action cards
- `nuxt.config.ts` - Added @nuxt/icon module

### 🔄 Remaining Files with SVG Icons
- `pages/profile.vue` - Upload icon, back arrow
- `pages/index.vue` - Feature icons (lock, play, video)
- `pages/login.vue` - Email/password icons (if any)
- `pages/register.vue` - Form icons (if any)
- `pages/obs-sources.vue` - Copy/preview button icons
- `pages/playthrough/new.vue` - Game selection icons
- `pages/playthrough/[uuid]/setup.vue` - Control panel icons
- `pages/play/[uuid].vue` - Playback control icons
- Any other pages with inline SVG

## 🔍 How to Find and Replace

1. **Search for SVG tags**:
   ```bash
   grep -r "<svg" pages/
   ```

2. **Identify the icon** by looking at the path data

3. **Find matching icon** at https://icones.js.org/

4. **Replace** with `<Icon name="..." class="..." />`

## 💡 Tips

- Use **Heroicons** for consistency (we're using Tailwind CSS)
- Keep class names for sizing: `w-6 h-6`, `w-8 h-8`, etc.
- Color applies via `text-*` classes: `text-gray-500`, `text-white`, etc.
- For filled icons, use `-solid` suffix: `heroicons:user-circle-solid`
- For outlined icons, use regular name: `heroicons:user-circle`

## 🎨 Common Icon Mappings

| Purpose | Heroicon Name |
|---------|--------------|
| Home | `heroicons:home` |
| User/Profile | `heroicons:user-circle` |
| Settings | `heroicons:cog` |
| Play | `heroicons:play-circle` |
| Pause | `heroicons:pause-circle` |
| Stop | `heroicons:stop-circle` |
| Edit | `heroicons:pencil` |
| Delete | `heroicons:trash` |
| Back | `heroicons:arrow-left` |
| Forward | `heroicons:arrow-right` |
| Check | `heroicons:check-circle` |
| Error | `heroicons:x-circle` |
| Upload | `heroicons:arrow-up-tray` |
| Download | `heroicons:arrow-down-tray` |
| Copy | `heroicons:clipboard-document` |
| View | `heroicons:eye` |
| Camera/Video | `heroicons:video-camera` |
| Lock | `heroicons:lock-closed` |
| Unlock | `heroicons:lock-open` |

## 🚀 Next Steps

1. Gradually replace inline SVGs with Icon components
2. Test each page after migration
3. Remove this guide once migration is complete
4. Enjoy maintenance-free icons! 🎉

