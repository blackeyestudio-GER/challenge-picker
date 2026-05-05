// This file is generated from backend/contracts/frontend-api.json.

// Do not edit it manually. Run `node scripts/generate-api-contracts.mjs`.



export interface DeleteAccountResponse {
  success: boolean
  message: string
}

export interface DeletePlaythroughResponseData {
  uuid: string
  message: string
}

export interface DeletePlaythroughResponse {
  success: boolean
  data: DeletePlaythroughResponseData
}

export interface OAuthProvidersResponseData {
  twitchAccountLinking: boolean
}

export interface OAuthProvidersResponse {
  success: boolean
  data: OAuthProvidersResponseData
}

export interface FeatureSettingItem {
  key: string
  name: string
  description: string
  enabled: boolean
}

export interface FeatureSettingsResponseData {
  features: FeatureSettingItem[]
}

export interface FeatureSettingsResponse {
  success: boolean
  data: FeatureSettingsResponseData
}

export interface UpdateFeatureSettingsFeatureData {
  key: string
  enabled: boolean
}

export interface UpdateFeatureSettingsResponseData {
  feature: UpdateFeatureSettingsFeatureData
}

export interface UpdateFeatureSettingsResponse {
  success: boolean
  data: UpdateFeatureSettingsResponseData
}

export interface ShopSettingsResponseData {
  shopEnabled: boolean
}

export interface ShopSettingsResponse {
  success: boolean
  data: ShopSettingsResponseData
}

export interface UpdateShopSettingsResponseData {
  message: string
  shopEnabled: boolean
}

export interface UpdateShopSettingsResponse {
  success: boolean
  data: UpdateShopSettingsResponseData
}

export interface PayoutRequestItem {
  id: number
  designerUuid: string | null
  designerUsername: string | null
  designerEmail: string | null
  amount: string
  currency: string | null
  status: string | null
  isAutomated: boolean
  requestedAt: string | null
}

export interface PayoutRequestsResponseData {
  payoutRequests: PayoutRequestItem[]
}

export interface PayoutRequestsResponse {
  success: boolean
  data: PayoutRequestsResponseData
}

export interface PayoutDecisionItem {
  id: number
  status: string
  processedAt: string | null
}

export interface PayoutDecisionResponseData {
  payoutRequest: PayoutDecisionItem
}

export interface PayoutDecisionResponse {
  success: boolean
  data: PayoutDecisionResponseData
}

export interface DesignNameItem {
  id: number
  name: string
  description: string | null
  createdAt: string
  designSetCount: number
}

export interface DesignNamesResponseData {
  designNames: DesignNameItem[]
}

export interface DesignNamesResponse {
  success: boolean
  data: DesignNamesResponseData
}

export interface DesignNameMutationResponseData {
  message: string
  designName: DesignNameItem
}

export interface DesignNameMutationResponse {
  success: boolean
  data: DesignNameMutationResponseData
}

export interface DesignSetListItem {
  id: number
  designNameId: number
  designName: string
  type: string
  isFree: boolean
  isPremium: boolean
  price: string | null
  theme: string | null
  description: string | null
  cardCount: number
  completedCards: number
  isComplete: boolean
  previewImage: string | null
  previewImages: string[]
  createdAt: string
  updatedAt: string
}

export interface DesignSetsResponseData {
  designSets: DesignSetListItem[]
}

export interface DesignSetsResponse {
  success: boolean
  data: DesignSetsResponseData
}

export interface DesignSetCardItem {
  id: number
  cardIdentifier: string
  displayName: string
  imageBase64: string | null
  hasImage: boolean
  isTemplate: boolean
  templateType: string | null
  requiresIconComposition: boolean
  rarity: string
  updatedAt: string
}

export interface DesignSetDetailItem {
  id: number
  designNameId: number
  designName: string
  type: string
  isPremium: boolean
  price: string | null
  theme: string | null
  description: string | null
  cardCount: number
  expectedCardCount: number
  completedCards: number
  isComplete: boolean
  cards: DesignSetCardItem[]
  createdAt: string
  updatedAt: string
}

export interface DesignSetResponseData {
  designSet: DesignSetDetailItem
}

export interface DesignSetResponse {
  success: boolean
  data: DesignSetResponseData
}

export interface DesignSetMutationResponseData {
  message: string
  designSet: DesignSetListItem
}

export interface DesignSetMutationResponse {
  success: boolean
  data: DesignSetMutationResponseData
}

export interface RuleIconItem {
  id: number
  identifier: string
  category: string
  displayName: string
  svgContent: string
  tags: string[] | null
  color: string | null
  license: string | null
  source: string | null
  createdAt: string
  updatedAt: string
}

export interface RuleIconsResponseData {
  icons: RuleIconItem[]
}

export interface RuleIconsResponse {
  success: boolean
  data: RuleIconsResponseData
}

export interface ChallengeUserData {
  uuid: string
  username: string
  displayName: string
}

export interface ChallengeGameData {
  id: number | null
  name: string | null
  imageBase64: string | null
}

export interface ChallengeRulesetData {
  id: number
  name: string
  game: ChallengeGameData | null
}

export interface ChallengePlaythroughData {
  uuid: string
  ruleset: ChallengeRulesetData
  maxConcurrentRules: number
}

export interface ChallengeItem {
  uuid: string
  challenger: ChallengeUserData
  playthrough: ChallengePlaythroughData
  createdAt: string
  expiresAt: string
}

export interface ChallengeListResponseData {
  challenges: ChallengeItem[]
  count: number
}

export interface ChallengeListResponse {
  success: boolean
  data: ChallengeListResponseData
}

export interface SendChallengeResponseData {
  challengeUuid: string
  message: string
}

export interface SendChallengeResponse {
  success: boolean
  data: SendChallengeResponseData
}

export interface RespondToChallengeResponseData {
  message: string
  playthroughUuid: string | null
}

export interface RespondToChallengeResponse {
  success: boolean
  data: RespondToChallengeResponseData
}

export interface SentChallengeUserData {
  uuid: string
  username: string
}

export interface SentChallengeItem {
  uuid: string
  challengedUser: SentChallengeUserData
  status: string
  createdAt: string
  respondedAt: string | null
  expiresAt: string
  resultingPlaythroughUuid: string | null
}

export interface SentChallengeGameData {
  id: number | null
  name: string
  imageBase64: string | null
}

export interface SentChallengeRulesetData {
  id: number | null
  name: string
}

export interface SentChallengeGroup {
  playthroughUuid: string
  game: SentChallengeGameData
  ruleset: SentChallengeRulesetData
  createdAt: string
  challenges: SentChallengeItem[]
}

export interface SentChallengesResponse {
  success: boolean
  data: SentChallengeGroup[]
}

export interface BrowseAvailabilityResponseData {
  available: boolean
  count: number
}

export interface BrowseAvailabilityResponse {
  success: boolean
  data: BrowseAvailabilityResponseData
}

export interface UserStatsResponseData {
  totalVotes: number
  completedPlaythroughs: number
  rulesPlayed: number
  totalActiveRules: number
}

export interface UserStatsResponse {
  success: boolean
  data: UserStatsResponseData
}

export interface AdminStatsResponseData {
  categories: number
  games: number
  rulesets: number
  rules: number
}

export interface AdminStatsResponse {
  success: boolean
  data: AdminStatsResponseData
}

export interface ChallengeDetailsGameData {
  id: number
  name: string
  imageBase64: string | null
}

export interface ChallengeDetailsRulesetData {
  id: number
  name: string
  description: string | null
  difficulty: string | null
}

export interface ChallengeDetailsResponseData {
  playthroughUuid: string
  hostUsername: string
  game: ChallengeDetailsGameData
  ruleset: ChallengeDetailsRulesetData
  maxConcurrentRules: number
  requireAuth: boolean
  allowViewerPicks: boolean
}

export interface ChallengeDetailsResponse {
  success: boolean
  data: ChallengeDetailsResponseData
}

export interface ChallengeComparisonRule {
  ruleId: number | null
  ruleName: string | null
  ruleType: string | null
  difficultyLevel: number | null
  isActive: boolean | null
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
}

export interface ChallengeComparisonParticipant {
  username: string
  playthroughUuid: string
  duration: number | null
  activeRules: ChallengeComparisonRule[]
  status: string
}

export interface ChallengeComparisonData {
  sourcePlaythroughUuid: string
  sourceUsername: string
  gameName: string
  rulesetName: string
  sourceDuration: number | null
  sourceActiveRules: ChallengeComparisonRule[]
  participants: ChallengeComparisonParticipant[]
}

export interface ChallengeComparisonResponse {
  success: boolean
  data: ChallengeComparisonData
}

export interface DesignSetShopItem {
  id: number
  name: string
  type: string
  is_premium: boolean
  price: string | null
  theme: string | null
  description: string | null
  owned: boolean
  preview_image: string | null
  preview_images: string[]
}

export interface ShopDesignSetsResponseData {
  design_sets: DesignSetShopItem[]
}

export interface ShopDesignSetsResponse {
  success: boolean
  data: ShopDesignSetsResponseData
}

export interface CheckoutSessionData {
  session_id: string
  checkout_url: string | null
}

export interface CheckoutSessionResponse {
  success: boolean
  data: CheckoutSessionData
}

export interface PurchaseDesignSet {
  id: number | null
  name: string
  type: string
  theme: string | null
  description: string | null
  preview_image: string | null
  preview_images: string[]
}

export interface PurchaseItem {
  id: number | null
  designSet: PurchaseDesignSet
  purchasedAt: string
  pricePaid: string | null
  currency: string | null
}

export interface PurchasesResponseData {
  purchases: PurchaseItem[]
}

export interface PurchasesResponse {
  success: boolean
  data: PurchasesResponseData
}

export interface TransactionLineItem {
  design_set_id: number
  name: string
  price: number | string
}

export interface TransactionItem {
  id: number | null
  stripeSessionId: string | null
  status: string | null
  amount: string | null
  currency: string | null
  items: TransactionLineItem[]
  createdAt: string
  completedAt: string | null
}

export interface TransactionsResponseData {
  transactions: TransactionItem[]
}

export interface TransactionsResponse {
  success: boolean
  data: TransactionsResponseData
}

export interface RetryTransactionResponseData {
  checkoutUrl: string | null
}

export interface RetryTransactionResponse {
  success: boolean
  data: RetryTransactionResponseData
}

export interface ShopStatusResponseData {
  enabled: boolean
  message: string
}

export interface ShopStatusResponse {
  success: boolean
  data: ShopStatusResponseData
}

export interface AuthUser {
  uuid: string
  email: string
  username: string
  avatar: string | null
  oauthProvider: string | null
  isAdmin: boolean
  isArtist: boolean | undefined
  discordId: string | null
  discordUsername: string | null
  discordAvatar: string | null
  twitchId: string | null
  twitchUsername: string | null
  twitchAvatar: string | null
  theme: string | null
  emailVerified: boolean
}

export interface AuthLoginData {
  token: string
  user: AuthUser
  expiresIn: number
}

export interface AuthLoginResponse {
  success: boolean
  data: AuthLoginData
  message: string
}

export interface PlaythroughGamesResponse {
  success: boolean
  data: PlaythroughGame[]
}

export interface PlaythroughRulesetsResponse {
  success: boolean
  data: PlaythroughRuleset[]
}

export interface CardDesignData {
  id: number | null
  cardIdentifier: string | null
  imageBase64: string | null
  isTemplate: boolean
  templateType: 'basic' | 'court' | 'legendary' | null
}

export interface CardDesignsResponseData {
  designSetId: number | null
  designSetName: string
  cardDesigns: Record<string, CardDesignData | null>
}

export interface CardDesignsResponse {
  success: boolean
  data: CardDesignsResponseData
}

export interface PlaythroughGame {
  id: number
  name: string
  description: string | null
  image: string | null
  rulesetCount: number
  gameSpecificRulesetCount: number | undefined
  categoryBasedRulesetCount: number | undefined
  categoryId: number | null
  categoryName: string | null
  categorySlug: string | null
  isCategoryRepresentative: boolean
  isFavorited: boolean
  steamLink: string | null
  epicLink: string | null
  gogLink: string | null
  twitchCategory: string | null
}

export interface PlaythroughRuleset {
  id: number
  name: string
  description: string | null
  gameId: number | undefined
  gameName: string | undefined
  ruleCount: number
  isFavorited: boolean | undefined
  voteCount: number | undefined
  userVoteType: number | null | undefined
  isInherited: boolean | undefined
  inheritedFromCategory: string | null | undefined
  isGameSpecific: boolean | undefined
  categoryName: string | null | undefined
  categoryId: number | null | undefined
}

export interface CompletedRunRule {
  id: number
  name: string
  description: string | null
  type: string | null
  isDefault: boolean
  isEnabled: boolean
}

export interface CompletedRunHistoryEntry {
  id: number | null
  ruleId: number
  name: string
  description: string | null
  type: string | null
  isActive: boolean
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
  createdAt: string | null
}

export interface Playthrough {
  id: number
  uuid: string
  userId: number
  username: string
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  maxConcurrentRules: number
  status: 'setup' | 'active' | 'paused' | 'completed'
  startedAt: string | null
  endedAt: string | null
  pausedAt: string | null
  totalPausedDuration: number | null
  totalDuration: number | null
  videoUrl: string | null
  finishedRun: boolean | null
  recommended: number | null
  configuration: Record<string, unknown>
  usedRules: CompletedRunRule[]
  ruleHistory: CompletedRunHistoryEntry[]
  createdAt: string
}

export interface AddVideoUrlResponseData {
  message: string
  videoUrl: string | null
}

export interface AddVideoUrlResponse {
  success: boolean
  data: AddVideoUrlResponseData
}

export interface CreatePlaythroughResponse {
  success: boolean
  data: Playthrough
}

export interface PlaythroughMutationResponse {
  success: boolean
  data: Playthrough
}

export interface PickRuleResponseData {
  ruleId: number
  ruleName: string
  activated: boolean
  position: number | null
  eta: number | null
  message: string
}

export interface PickRuleResponse {
  success: boolean
  data: PickRuleResponseData
}

export interface DashboardActiveRule {
  id: number
  ruleId: number | null
  ruleName: string | null
  ruleType: string | null
  type: 'permanent' | 'time' | 'counter' | 'hybrid'
  currentAmount: number | null
  initialAmount: number | null
  durationSeconds: number | null
  expiresAt: string | null
  timeRemaining: number | null
  startedAt: string | null
}

export interface DashboardPickStatus {
  canPick: boolean
  rateLimitSeconds: number | null
  cooldownRuleIds: number[]
  availableRulesCount: number
  message: string
}

export interface DashboardQueuePendingRule {
  ruleId: number
  ruleName: string
  ruleType: string | null
  position: number
  eta: number
}

export interface DashboardQueueStatus {
  queueLength: number
  pendingRules: DashboardQueuePendingRule[]
}

export interface ActiveRuleData {
  id: number
  text: string
  durationMinutes: number
  startedAt: string | null
}

export interface PlayScreenData {
  id: number
  uuid: string
  userUuid: string
  gameName: string
  gameImage: string | null
  rulesetName: string
  gamehostUsername: string
  status: 'setup' | 'active' | 'paused' | 'completed'
  maxConcurrentRules: number
  requireAuth: boolean
  allowViewerPicks: boolean
  startedAt: string | null
  pausedAt: string | null
  totalPausedDuration: number | null
  totalDuration: number | null
  activeRules: ActiveRuleData[]
  totalRulesCount: number
  activeRulesCount: number
  completedRulesCount: number
  configuration: Record<string, unknown>
}

export interface PlayScreenResponse {
  success: boolean
  data: PlayScreenData
}

export interface DashboardResponseData {
  playthrough: PlayScreenData
  activeRules: DashboardActiveRule[]
  pickStatus: DashboardPickStatus | null
  queueStatus: DashboardQueueStatus
  isHost: boolean
}

export interface DashboardResponse {
  success: boolean
  data: DashboardResponseData
}

export interface PublicRunRule {
  id: number
  name: string
  description: string | null
  type: string | null
}

export interface PublicRunHistoryEntry {
  ruleId: number
  name: string
  description: string | null
  type: string | null
  isActive: boolean
  completed: boolean
  currentAmount: number | null
  startedAt: string | null
  completedAt: string | null
  createdAt: string | null
}

export interface PublicRunPlaythroughGame {
  id: number | null
  name: string | null
  imageUrl: string | null
}

export interface PublicRunPlaythroughRuleset {
  id: number | null
  name: string | null
  description: string | null
}

export interface PublicRunPlaythroughUser {
  username: string | null
  avatarUrl: string | null
}

export interface PublicRunPlaythrough {
  uuid: string
  status: string
  startedAt: string | null
  endedAt: string | null
  totalDuration: number | null
  videoUrl: string | null
  finishedRun: boolean | null
  recommended: number | null
  game: PublicRunPlaythroughGame
  ruleset: PublicRunPlaythroughRuleset
  user: PublicRunPlaythroughUser
  usedRules: PublicRunRule[]
  ruleHistory: PublicRunHistoryEntry[]
}

export interface PublicRunResponseData {
  playthrough: PublicRunPlaythrough
}

export interface PublicRunResponse {
  success: boolean
  data: PublicRunResponseData
}

export interface BrowseRun {
  id: number
  uuid: string
  userId: number
  username: string
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  maxConcurrentRules: number
  status: 'setup' | 'active' | 'paused' | 'completed'
  startedAt: string | null
  endedAt: string | null
  pausedAt: string | null
  totalPausedDuration: number | null
  totalDuration: number | null
  videoUrl: string | null
  finishedRun: boolean | null
  recommended: number | null
  configuration: Record<string, unknown>
  usedRules: CompletedRunRule[]
  ruleHistory: CompletedRunHistoryEntry[]
  createdAt: string
  isOwnRun: boolean
  hasPlayedGame: boolean
}

export interface BrowseRunsResponseData {
  playthroughs: BrowseRun[]
}

export interface BrowseRunsResponse {
  success: boolean
  data: BrowseRunsResponseData
}

export interface CompletedPlaythroughsResponseData {
  playthroughs: Playthrough[]
}

export interface CompletedPlaythroughsResponse {
  success: boolean
  data: CompletedPlaythroughsResponseData
}

export interface ActivePlaythroughResponse {
  success: boolean
  data: Playthrough | null
}

export interface ActiveRulesResponseData {
  playthroughId: number
  status: string
  activeRules: DashboardActiveRule[]
}

export interface ActiveRulesResponse {
  success: boolean
  data: ActiveRulesResponseData
}

export interface PlaythroughRule {
  id: number
  ruleId: number
  text: string
  durationMinutes: number
  isActive: boolean
  completed: boolean
}

export interface PlaythroughDetails {
  id: number
  uuid: string
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  maxConcurrentRules: number
  status: 'setup' | 'active' | 'paused' | 'completed'
  rules: PlaythroughRule[]
}

export interface PlaythroughDetailsResponse {
  success: boolean
  data: PlaythroughDetails
}

export interface ToggleRuleResponseData {
  id: number | null
  ruleId: number | null
  isActive: boolean
}

export interface ToggleRuleResponse {
  success: boolean
  data: ToggleRuleResponseData
}

export interface CounterMutationResponseData {
  id: number | null
  currentAmount: number | null
  isActive: boolean
  message: string
}

export interface CounterMutationResponse {
  success: boolean
  data: CounterMutationResponseData
}

export interface IndexedCounterMutationResponseData {
  ruleId: number
  ruleName: string
  previousAmount: number
  currentAmount: number
  completed: boolean
}

export interface IndexedCounterMutationResponse {
  success: boolean
  data: IndexedCounterMutationResponseData
}

export interface EndPlaythroughResponse {
  success: boolean
  data: Playthrough | null
  deleted: boolean | undefined
  message: string | null | undefined
  uuid: string | null | undefined
}

export interface AdminGameCategory {
  id: number
  name: string
  slug: string
}

export interface AdminGame {
  id: number
  name: string
  description: string | null
  image: string | null
  rulesetCount: number
  categories: AdminGameCategory[]
  isCategoryRepresentative: boolean
  isActive: boolean
  steamLink: string | null
  epicLink: string | null
  gogLink: string | null
  twitchCategory: string | null
}

export interface AdminRulesetGame {
  id: number
  name: string
}

export interface AdminRulesetDefaultRule {
  id: number
  name: string
  ruleType: string
}

export interface AdminRuleset {
  id: number
  name: string
  description: string | null
  games: AdminRulesetGame[]
  defaultRules: AdminRulesetDefaultRule[]
  ruleCount: number
}

export interface RuleDifficultyLevel {
  difficultyLevel: number
  durationSeconds: number | null
  amount: number | null
  description: string | null
}

export interface AdminRule {
  id: number
  name: string
  description: string | null
  ruleType: 'basic' | 'court' | 'legendary'
  iconIdentifier: string | null | undefined
  difficultyLevels: RuleDifficultyLevel[]
}

export interface GamePagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface AdminGameListData {
  games: AdminGame[]
  pagination: GamePagination
}

export interface AdminGameListResponse {
  success: boolean
  data: AdminGameListData
}

export interface RulePagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface AdminRuleListData {
  rules: AdminRule[]
  pagination: RulePagination
}

export interface AdminRuleListResponse {
  success: boolean
  data: AdminRuleListData
}

export interface GameName {
  id: number
  name: string
}

export interface AdminGameNamesData {
  games: GameName[]
}

export interface AdminGameNamesResponse {
  success: boolean
  data: AdminGameNamesData
}

export interface AdminRulesetsResponseData {
  rulesets: AdminRuleset[]
}

export interface AdminRulesetsResponse {
  success: boolean
  data: AdminRulesetsResponseData
}

export interface AdminCategoryGame {
  id: number
  name: string
  image: string | null
  isActive: boolean
  isCategoryRepresentative: boolean
}

export interface AdminCategory {
  id: number
  name: string
  description: string | null
  slug: string
  gameCount: number
  games: AdminCategoryGame[]
}

export interface AdminCategoriesResponseData {
  categories: AdminCategory[]
}

export interface AdminCategoriesResponse {
  success: boolean
  data: AdminCategoriesResponseData
}

export interface GameMutationResponseData {
  message: string
  game: AdminGame
}

export interface GameMutationResponse {
  success: boolean
  data: GameMutationResponseData
}

export interface RulesetMutationResponseData {
  message: string
  ruleset: AdminRuleset
}

export interface RulesetMutationResponse {
  success: boolean
  data: RulesetMutationResponseData
}

export interface RuleMutationResponseData {
  message: string
  rule: AdminRule
}

export interface RuleMutationResponse {
  success: boolean
  data: RuleMutationResponseData
}

export interface CategoryMutationResponseData {
  message: string
  category: AdminCategory
}

export interface CategoryMutationResponse {
  success: boolean
  data: CategoryMutationResponseData
}

export interface RuleRulesetMutationResponseData {
  rule: AdminRule
}

export interface RuleRulesetMutationResponse {
  success: boolean
  data: RuleRulesetMutationResponseData
}

export interface ActiveDesignSetData {
  id: number
  name: string
  type: string
  isPremium: boolean
  theme: string | null
  displayIcon: boolean
  displayText: boolean
  iconColor: string | null
  iconBrightness: number | null
  iconOpacity: number | null
}

export interface ActiveDesignSetResponse {
  success: boolean
  data: ActiveDesignSetData
}

export interface UpdateActiveDesignSetResponseData {
  designSetId: number | null | undefined
  message: string
}

export interface UpdateActiveDesignSetResponse {
  success: boolean
  data: UpdateActiveDesignSetResponseData
}
