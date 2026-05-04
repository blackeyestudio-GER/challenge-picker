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
