export interface ActiveRule {
  id: number
  /** Normalized for UI; API may send ruleName only */
  name?: string
  ruleName?: string
  description?: string | null
  ruleId?: number
  ruleType?: 'basic' | 'court' | 'legendary'
  difficultyLevel?: number
  type?: 'permanent' | 'time' | 'counter' | 'hybrid'
  expiresAt?: string | null
  currentAmount?: number | null
  initialAmount?: number | null
  durationSeconds?: number | null
  timeRemaining?: number | null
  startedAt?: string | null
  isActive?: boolean
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

