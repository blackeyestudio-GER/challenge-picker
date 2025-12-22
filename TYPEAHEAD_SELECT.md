# TypeaheadSelect Component

A reusable, accessible typeahead/autocomplete select component built with **@headlessui/vue** and styled with **Tailwind CSS**.

## Features

✅ **Accessible** - Built on HeadlessUI's Combobox (ARIA compliant)  
✅ **Searchable** - Filter options by typing  
✅ **Keyboard Navigation** - Full keyboard support (↑↓ arrows, Enter, Esc)  
✅ **Dark Theme** - Matches your app's aesthetic  
✅ **Customizable Display** - Control how options are displayed  
✅ **TypeScript Support** - Fully typed with generics  
✅ **Disabled State** - Can be disabled (useful for edit mode)

## Installation

Already installed! Package: `@headlessui/vue`

## Basic Usage

```vue
<script setup lang="ts">
import { ref } from 'vue'

interface Game {
  id: number
  name: string
}

const selectedGameId = ref<number | null>(null)
const games = ref<Game[]>([
  { id: 1, name: 'Super Mario' },
  { id: 2, name: 'Zelda' },
  { id: 3, name: 'Pokemon' }
])
</script>

<template>
  <TypeaheadSelect
    v-model="selectedGameId"
    :options="games"
    placeholder="Search for a game..."
  />
</template>
```

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `modelValue` | `number \| null` | ✅ Yes | - | The selected option's ID (v-model) |
| `options` | `T[]` | ✅ Yes | - | Array of options (must have `id` and `name`) |
| `placeholder` | `string` | ❌ No | `'Select an option...'` | Placeholder text |
| `disabled` | `boolean` | ❌ No | `false` | Disable the input |
| `label` | `string` | ❌ No | `''` | Label text (currently unused) |
| `required` | `boolean` | ❌ No | `false` | Mark as required field |
| `getDisplayValue` | `(option: T) => string` | ❌ No | `(o) => o.name` | Custom display function |
| `getSearchValue` | `(option: T) => string` | ❌ No | `(o) => o.name` | Custom search function |

## Advanced Usage

### Custom Display Format

```vue
<TypeaheadSelect
  v-model="formData.rulesetId"
  :options="rulesets"
  :get-display-value="(ruleset) => `${ruleset.gameName} - ${ruleset.name}`"
  :get-search-value="(ruleset) => `${ruleset.gameName} ${ruleset.name}`"
  placeholder="Search for a ruleset..."
/>
```

This will:
- Display: `"Super Mario - Speed Run"`
- Search by both game name AND ruleset name

### Disabled Mode (Edit Forms)

```vue
<TypeaheadSelect
  v-model="formData.gameId"
  :options="games"
  :disabled="!!editingRuleset"
  placeholder="Search for a game..."
/>
```

Useful when editing and you don't want to allow changing the parent entity.

## Current Usage in App

### 1. Admin Rulesets Page (`/admin/rulesets`)
- Select which **Game** to create a ruleset for
- Searchable list of all games
- Disabled when editing (can't change game after creation)

### 2. Admin Rules Page (`/admin/rules`)
- Select which **Ruleset** to create a rule for
- Shows "Game Name - Ruleset Name" format
- Searches both game and ruleset names
- Disabled when editing (can't change ruleset after creation)

## TypeScript Generic

The component uses a generic type constraint:

```typescript
generic="T extends { id: number; name: string }"
```

This means any object with `id` (number) and `name` (string) will work!

### Extending for Custom Objects

If your object has additional fields:

```typescript
interface CustomOption {
  id: number
  name: string
  description?: string
  category?: string
}

const options = ref<CustomOption[]>([...])
```

Then use custom display/search functions:

```vue
<TypeaheadSelect
  v-model="selected"
  :options="options"
  :get-display-value="(opt) => `${opt.name} (${opt.category})`"
  :get-search-value="(opt) => `${opt.name} ${opt.category} ${opt.description}`"
/>
```

## Styling

The component uses your existing Tailwind config:
- Background: `bg-gray-900`
- Border: `border-gray-600`
- Focus ring: `focus:ring-cyan`
- Active item: `bg-cyan/20`
- Selected item: `text-cyan` with checkmark icon

All colors match your existing admin UI!

## Keyboard Shortcuts

- **↓ Arrow Down** - Next option
- **↑ Arrow Up** - Previous option
- **Enter** - Select highlighted option
- **Escape** - Close dropdown
- **Type** - Filter options

## Accessibility

Built on HeadlessUI's Combobox, which provides:
- Proper ARIA attributes
- Screen reader support
- Focus management
- Keyboard navigation

## Future Enhancements

Possible additions:
- [ ] Multi-select mode (select multiple options)
- [ ] Option to show descriptions below names
- [ ] Custom option templates (with images, badges, etc.)
- [ ] Loading state for async options
- [ ] Create new option on the fly

## Example: Full Form Integration

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { useAdmin, type AdminGame } from '~/composables/useAdmin'

const { fetchAdminGames } = useAdmin()
const games = ref<AdminGame[]>([])
const formData = ref({
  gameId: null as number | null,
  name: '',
  description: ''
})

onMounted(async () => {
  games.value = await fetchAdminGames()
})

const handleSubmit = () => {
  console.log('Selected game:', formData.value.gameId)
  // ... save logic
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-300 mb-2">
        Game *
      </label>
      <TypeaheadSelect
        v-model="formData.gameId"
        :options="games"
        placeholder="Search for a game..."
        required
      />
    </div>
    
    <button type="submit">Save</button>
  </form>
</template>
```

## Notes

- Component is fully reactive (updates when options change)
- Uses `v-model` for two-way binding
- Stores/emits only the `id` (number), not the full object
- Query resets after selection
- Dropdown closes on selection or ESC key

---

**Enjoy your new searchable select! 🎉**

