export interface ActiveRule {
  id: number
  name: string
  description: string
  ruleType: 'basic' | 'court' | 'legendary'
  difficultyLevel: number
  expiresAt?: string | null
  currentAmount?: number | null
  isActive: boolean
}

export interface PlaythroughStatus {
  id: number
  gameId: number
  gameName: string
  rulesetId: number
  rulesetName: string
  status: 'active' | 'completed' | 'failed' | 'abandoned'
  startedAt: string
  completedAt?: string | null
}

export interface ActiveRulesResponse {
  playthrough: PlaythroughStatus
  rules: ActiveRule[]
}

