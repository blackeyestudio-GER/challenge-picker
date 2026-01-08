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
  <div class="game-card">
    <!-- Category Representative Badge (Top-Left) -->
    <div 
      v-if="game.isCategoryRepresentative" 
      class="game-card__category-badge"
      title="Category-wide game: Rulesets for this category work with ANY game in this category"
    >
      <Icon name="heroicons:tag" class="w-3 h-3" />
      <span>CATEGORY</span>
    </div>

    <!-- Favorite Star Button (Top-Right) -->
    <button
      v-if="isAuthenticated && !game.isCategoryRepresentative"
      @click="handleFavoriteClick"
      class="game-card__favorite-button"
      :title="game.isFavorited ? 'Remove from favorites' : 'Add to favorites'"
    >
      <Icon 
        :name="game.isFavorited ? 'heroicons:star-solid' : 'heroicons:star'" 
        :class="[
          'game-card__favorite-icon',
          game.isFavorited ? 'game-card__favorite-icon--active' : 'game-card__favorite-icon--inactive'
        ]"
      />
    </button>

    <button
      @click="handleSelect"
      :class="[
        'game-card__button',
        game.isCategoryRepresentative
          ? 'game-card__button--category-rep'
          : 'game-card__button--regular'
      ]"
    >
      <!-- Game Image -->
      <div 
        v-if="game.image" 
        :class="[
          'game-card__image-container',
          game.isCategoryRepresentative ? 'game-card__image-container--category-rep' : 'game-card__image-container--regular'
        ]"
      >
        <img :src="game.image" :alt="game.name" class="game-card__image" />
      </div>
      <div 
        v-else 
        :class="[
          'game-card__image-container',
          game.isCategoryRepresentative ? 'game-card__image-container--category-rep' : 'game-card__image-container--regular'
        ]"
      >
        <Icon 
          v-if="game.isCategoryRepresentative" 
          name="heroicons:tag" 
          class="game-card__image-placeholder text-amber-500" 
        />
        <span v-else class="text-4xl">🎮</span>
      </div>

      <!-- Game Title -->
      <h3 
        :class="[
          'game-card__title',
          game.isCategoryRepresentative ? 'game-card__title--category-rep' : ''
        ]"
      >
        {{ game.name }}
      </h3>

      <!-- Category Representative Description -->
      <p 
        v-if="game.isCategoryRepresentative" 
        class="game-card__description"
      >
        Use for ANY {{ game.name.toLowerCase() }} game
      </p>
    
    <!-- Category Tags -->
    <div class="game-card__categories">
      <!-- Category Representative: Simple category display (no voting) -->
      <div v-if="game.isCategoryRepresentative && categories.length > 0" class="flex flex-wrap gap-2">
        <div
          v-for="cat in categories"
          :key="cat.id"
          :class="[
            'game-card__category-tag game-card__category-tag--category-rep',
            selectedCategoryIds?.has(cat.id)
              ? 'game-card__category-tag--category-rep-selected'
              : 'game-card__category-tag--category-rep-unselected'
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
            'game-card__category-tag',
            selectedCategoryIds?.has(cat.id)
              ? 'game-card__category-tag--regular-selected'
              : 'game-card__category-tag--regular-unselected'
          ]"
        >
          <!-- Vote Up Button -->
          <button
            @click="handleVote($event, cat.id, 1, cat.userVoteType)"
            :class="[
              'game-card__category-vote-button',
              cat.userVoteType === 1 ? 'game-card__category-vote-button--upvoted' : ''
            ]"
            title="Vote up (or remove upvote)"
          >
            +
          </button>
          
          <!-- Category Name and Vote Count -->
          <span class="game-card__category-name">
            {{ allCategories.find(c => c.id === cat.id)?.name }}
            <span :class="cat.voteCount >= 0 ? 'game-card__category-count' : 'game-card__category-count--negative'">
              ({{ cat.voteCount >= 0 ? '+' : '' }}{{ cat.voteCount }})
            </span>
          </span>
          
          <!-- Vote Down Button -->
          <button
            @click="handleVote($event, cat.id, -1, cat.userVoteType)"
            :class="[
              'game-card__category-vote-button',
              cat.userVoteType === -1 ? 'game-card__category-vote-button--downvoted' : ''
            ]"
            title="Vote down (or remove downvote)"
          >
            −
          </button>
        </div>
      </div>
      
      <!-- No Categories -->
      <div v-else class="game-card__no-categories">No categories</div>
    </div>
    
    <!-- Footer Actions (Always at bottom) -->
    <div class="game-card__footer">
      <div class="game-card__ruleset-count">
        <span v-if="game.gameSpecificRulesetCount !== undefined && game.categoryBasedRulesetCount !== undefined">
          {{ game.gameSpecificRulesetCount }} / {{ game.rulesetCount }} rulesets
        </span>
        <span v-else>
          {{ game.rulesetCount }} ruleset{{ game.rulesetCount !== 1 ? 's' : '' }}
        </span>
      </div>
    </div>
    </button>
  </div>
</template>

