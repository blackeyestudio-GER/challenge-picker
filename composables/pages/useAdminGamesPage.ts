import { computed, ref } from 'vue'
import type { AdminGame, CreateGameRequest, GameName, GamePagination } from '~/composables/useAdmin'
import { useAdmin } from '~/composables/useAdmin'

export const useAdminGamesPage = () => {
  const { fetchAdminGames, fetchGameNames, createGame, updateGame, deactivateGame, loading } = useAdmin()
  const { success, notifyApiError } = useNotify()

  const games = ref<AdminGame[]>([])
  const gameNames = ref<GameName[]>([])
  const pagination = ref<GamePagination | null>(null)
  const currentPage = ref(1)
  const searchQuery = ref('')
  const selectedGame = ref<GameName | null>(null)
  const errorMessage = ref<string | null>(null)

  const showModal = ref(false)
  const editingGame = ref<AdminGame | null>(null)

  const filteredGameNames = computed(() => {
    if (!searchQuery.value.trim()) return gameNames.value

    const query = searchQuery.value.toLowerCase()
    return gameNames.value.filter((game) =>
      game.name.toLowerCase().includes(query)
    )
  })

  const displaySelectedGameName = (game: GameName | null) => game?.name || searchQuery.value

  const pageNumbers = computed(() => {
    if (!pagination.value) return []

    const { page, totalPages } = pagination.value
    const pages: (number | string)[] = []
    const maxVisible = 7

    if (totalPages <= maxVisible) {
      for (let i = 1; i <= totalPages; i++) pages.push(i)
    } else if (page <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages)
    } else if (page >= totalPages - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = totalPages - 4; i <= totalPages; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = page - 1; i <= page + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages)
    }

    return pages
  })

  const loadGames = async (page: number = 1, search: string = '') => {
    try {
      errorMessage.value = null
      const response = await fetchAdminGames(page, 24, search)
      games.value = response.games
      pagination.value = response.pagination
      currentPage.value = page
    } catch (err) {
      errorMessage.value = 'Failed to load games'
      notifyApiError(err, 'Failed to load games')
    }
  }

  const loadGameNames = async () => {
    try {
      gameNames.value = await fetchGameNames()
    } catch (err) {
      notifyApiError(err, 'Failed to load game names')
    }
  }

  const bootstrap = async () => {
    await Promise.all([
      loadGames(),
      loadGameNames()
    ])
  }

  const handleSearch = async () => {
    currentPage.value = 1
    await loadGames(1, searchQuery.value)
  }

  const handleGameSelect = async (game: GameName | null) => {
    if (!game) return
    selectedGame.value = game
    searchQuery.value = game.name
    await handleSearch()
  }

  const clearSearch = async () => {
    searchQuery.value = ''
    selectedGame.value = null
    currentPage.value = 1
    await loadGames(1, '')
  }

  const goToPage = async (page: number) => {
    await loadGames(page, searchQuery.value)
  }

  const openCreateModal = () => {
    editingGame.value = null
    showModal.value = true
  }

  const openEditModal = (game: AdminGame) => {
    editingGame.value = game
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    editingGame.value = null
  }

  const handleModalSubmit = async (data: CreateGameRequest & { id?: number }) => {
    try {
      if (editingGame.value) {
        await updateGame(editingGame.value.id, data)
      } else {
        await createGame(data)
      }
      await loadGames(currentPage.value, searchQuery.value)
      closeModal()
      success(editingGame.value ? 'Game updated' : 'Game created')
    } catch (err) {
      notifyApiError(err, 'Failed to save game')
    }
  }

  const handleDeactivate = async () => {
    if (!editingGame.value) return

    if (!confirm(`Are you sure you want to deactivate "${editingGame.value.name}"? This will hide it from the system but preserve its history.`)) {
      return
    }

    try {
      await deactivateGame(editingGame.value.id)
      closeModal()
      await loadGames(currentPage.value, searchQuery.value)
      success('Game deactivated')
    } catch (err) {
      notifyApiError(err, 'Failed to deactivate game')
    }
  }

  return {
    loading,
    games,
    pagination,
    gameNames,
    currentPage,
    searchQuery,
    selectedGame,
    errorMessage,
    showModal,
    editingGame,
    filteredGameNames,
    pageNumbers,
    displaySelectedGameName,
    bootstrap,
    handleSearch,
    handleGameSelect,
    clearSearch,
    goToPage,
    openCreateModal,
    openEditModal,
    closeModal,
    handleModalSubmit,
    handleDeactivate
  }
}
