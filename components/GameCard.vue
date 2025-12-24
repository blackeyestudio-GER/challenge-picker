<script setup lang="ts">
import type { Game } from '~/composables/usePlaythrough'
import type { Category } from '~/composables/useCategories'

interface GameCategory {
  id: number
  name: string
  voteCount: number
  userVoted: boolean
  userVoteType: number | null // 1 for upvote, -1 for downvote, null if not voted
}

interface Props {
  game: Game
  categories: GameCategory[]
  selectedCategoryIds?: Set<number>
  allCategories: readonly Category[]
  isAuthenticated: boolean
}

interface Emits {
  (e: 'select', gameId: number): void
  (e: 'vote', payload: { gameId: number; categoryId: number; voteType: 1 | -1; currentVoteType: number | null }): void
  (e: 'toggleFavorite', gameId: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const handleVote = (event: Event, categoryId: number, voteType: 1 | -1, currentVoteType: number | null) => {
  event.stopPropagation()
  
  if (!props.isAuthenticated) {
    alert('Please log in to vote')
    return
  }
  
  emit('vote', {
    gameId: props.game.id,
    categoryId,
    voteType,
    currentVoteType
  })
}

const handleFavoriteClick = (event: Event) => {
  event.stopPropagation()
  
  if (!props.isAuthenticated) {
    alert('Please log in to favorite games')
    return
  }
  
  emit('toggleFavorite', props.game.id)
}

const handleSelect = () => {
  emit('select', props.game.id)
}
</script>

<template>
  <div class="relative h-full flex flex-col">
    <!-- Category Representative Badge (Top-Left) -->
    <div 
      v-if="game.isCategoryRepresentative" 
      class="absolute top-2 left-2 z-10 px-3 py-1 bg-amber-500/90 text-white text-xs font-bold rounded-lg flex items-center gap-1 shadow-lg"
      title="Category-wide game: Rulesets for this category work with ANY game in this category"
    >
      <Icon name="heroicons:tag" class="w-3 h-3" />
      <span>CATEGORY</span>
    </div>

    <!-- Favorite Star Button (Top-Right) -->
    <button
      v-if="isAuthenticated && !game.isCategoryRepresentative"
      @click="handleFavoriteClick"
      class="absolute top-2 right-2 z-10 p-2 rounded-lg bg-gray-900/90 hover:bg-gray-800 transition-all"
      :title="game.isFavorited ? 'Remove from favorites' : 'Add to favorites'"
    >
      <Icon 
        :name="game.isFavorited ? 'heroicons:star-solid' : 'heroicons:star'" 
        :class="[
          'w-6 h-6 transition-colors',
          game.isFavorited ? 'text-yellow-400' : 'text-gray-400 hover:text-yellow-400'
        ]"
      />
    </button>

    <button
      @click="handleSelect"
      :class="[
        'w-full h-full backdrop-blur-sm rounded-lg p-6 transition-all transform hover:-translate-y-1 text-left flex flex-col',
        game.isCategoryRepresentative
          ? 'bg-amber-900/20 border-2 border-amber-500/50 hover:border-amber-400 hover:shadow-xl hover:shadow-amber-500/20'
          : 'bg-gray-800/80 border border-gray-700 hover:border-cyan hover:shadow-xl hover:shadow-cyan/20'
      ]"
    >
      <!-- Game Image -->
      <div 
        v-if="game.image" 
        :class="[
          'mb-4 h-32 flex items-center justify-center rounded',
          game.isCategoryRepresentative ? 'bg-amber-950/30' : 'bg-gray-900'
        ]"
      >
        <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
      </div>
      <div 
        v-else 
        :class="[
          'mb-4 h-32 flex items-center justify-center rounded',
          game.isCategoryRepresentative ? 'bg-amber-950/30' : 'bg-gray-900'
        ]"
      >
        <Icon 
          v-if="game.isCategoryRepresentative" 
          name="heroicons:tag" 
          class="w-16 h-16 text-amber-500" 
        />
        <span v-else class="text-4xl">🎮</span>
      </div>

      <!-- Game Title -->
      <h3 
        :class="[
          'text-xl font-bold mb-2',
          game.isCategoryRepresentative ? 'text-amber-200' : 'text-white'
        ]"
      >
        {{ game.name }}
      </h3>

      <!-- Category Representative Description -->
      <p 
        v-if="game.isCategoryRepresentative" 
        class="text-amber-300/80 text-sm mb-3 italic"
      >
        Use for ANY {{ game.name.toLowerCase() }} game
      </p>
    
    <!-- Category Tags -->
    <div class="flex-grow mb-3">
      <!-- Category Representative: Simple category display (no voting) -->
      <div v-if="game.isCategoryRepresentative && categories.length > 0" class="flex flex-wrap gap-2">
        <div
          v-for="cat in categories"
          :key="cat.id"
          :class="[
            'px-3 py-1.5 rounded-lg text-sm font-semibold border-2',
            selectedCategoryIds?.has(cat.id)
              ? 'bg-amber-500/20 border-amber-400 text-amber-200'
              : 'bg-amber-900/30 border-amber-700/50 text-amber-300'
          ]"
        >
          {{ allCategories.find(c => c.id === cat.id)?.name }}
        </div>
      </div>
      
      <!-- Regular Games: Category tags with voting -->
      <div v-else-if="!game.isCategoryRepresentative && categories.length > 0" class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
        <div
          v-for="cat in categories"
          :key="cat.id"
          :class="[
            'flex items-center gap-1 px-2 py-1 rounded text-xs font-semibold border transition-all',
            selectedCategoryIds?.has(cat.id)
              ? 'bg-gradient-to-r from-cyan/20 to-magenta/20 border-cyan text-white shadow-lg'
              : 'bg-gray-700/80 border-gray-600 text-gray-300'
          ]"
        >
          <!-- Vote Up Button -->
          <button
            @click="handleVote($event, cat.id, 1, cat.userVoteType)"
            :class="[
              'hover:text-green-400 transition-colors px-1 font-bold',
              cat.userVoteType === 1 ? 'text-green-400' : 'text-gray-500'
            ]"
            title="Vote up (or remove upvote)"
          >
            +
          </button>
          
          <!-- Category Name and Vote Count -->
          <span class="px-1">
            {{ allCategories.find(c => c.id === cat.id)?.name }}
            <span :class="cat.voteCount >= 0 ? 'text-green-400' : 'text-red-400'">
              ({{ cat.voteCount >= 0 ? '+' : '' }}{{ cat.voteCount }})
            </span>
          </span>
          
          <!-- Vote Down Button -->
          <button
            @click="handleVote($event, cat.id, -1, cat.userVoteType)"
            :class="[
              'hover:text-red-400 transition-colors px-1 font-bold',
              cat.userVoteType === -1 ? 'text-red-400' : 'text-gray-500'
            ]"
            title="Vote down (or remove downvote)"
          >
            −
          </button>
        </div>
      </div>
      
      <!-- No Categories -->
      <div v-else class="text-gray-500 text-sm italic">No categories</div>
    </div>
    
    <!-- Footer Actions (Always at bottom) -->
    <div class="flex items-center justify-end gap-2 mt-auto pt-3 border-t border-gray-700">
      <div class="flex items-center text-cyan text-sm font-medium">
        <span>{{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}</span>
      </div>
    </div>
    </button>
  </div>
</template>

