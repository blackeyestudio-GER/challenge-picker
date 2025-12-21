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
  <div class="relative">
    <!-- Favorite Star Button (Top-Right) -->
    <button
      v-if="isAuthenticated"
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
      class="w-full bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-lg p-6 hover:border-cyan hover:shadow-xl hover:shadow-cyan/20 transition-all transform hover:-translate-y-1 text-left"
    >
      <!-- Game Image -->
      <div v-if="game.image" class="mb-4 h-32 flex items-center justify-center bg-gray-900 rounded">
        <img :src="game.image" :alt="game.name" class="max-h-full max-w-full object-contain" />
      </div>
      <div v-else class="mb-4 h-32 flex items-center justify-center bg-gray-900 rounded">
        <span class="text-4xl">🎮</span>
      </div>

      <!-- Game Title -->
      <h3 class="text-xl font-bold text-white mb-2">{{ game.name }}</h3>
    
    <!-- Category Tags with Inline Voting -->
    <div v-if="categories.length > 0" class="flex flex-wrap gap-2 mb-3">
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
    
    <!-- Game Description -->
    <p v-if="game.description" class="text-gray-400 text-sm mb-3">{{ game.description }}</p>
    
    <!-- Footer Actions -->
    <div class="flex items-center justify-end gap-2">
      <div class="flex items-center text-cyan text-sm font-medium">
        <span>{{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}</span>
      </div>
    </div>
    </button>
  </div>
</template>

