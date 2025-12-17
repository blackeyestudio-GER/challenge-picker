/**
 * OBS Overlay Design Constants
 * 
 * These constants define the supported design variants for each overlay type.
 * When adding new designs, update these arrays and the corresponding backend validators.
 */

// Timer overlay designs
export const TIMER_DESIGNS = ['numbers'] as const
export type TimerDesign = typeof TIMER_DESIGNS[number]
export const DEFAULT_TIMER_DESIGN: TimerDesign = 'numbers'

// Status overlay designs
export const STATUS_DESIGNS = ['word', 'symbols', 'buttons'] as const
export type StatusDesign = typeof STATUS_DESIGNS[number]
export const DEFAULT_STATUS_DESIGN: StatusDesign = 'word'

// Rules overlay designs
export const RULES_DESIGNS = ['list'] as const
export type RulesDesign = typeof RULES_DESIGNS[number]
export const DEFAULT_RULES_DESIGN: RulesDesign = 'list'

// Helper functions
export const isValidTimerDesign = (design: string): design is TimerDesign => {
  return TIMER_DESIGNS.includes(design as TimerDesign)
}

export const isValidStatusDesign = (design: string): design is StatusDesign => {
  return STATUS_DESIGNS.includes(design as StatusDesign)
}

export const isValidRulesDesign = (design: string): design is RulesDesign => {
  return RULES_DESIGNS.includes(design as RulesDesign)
}

// Human-readable labels for designs
export const TIMER_DESIGN_LABELS: Record<TimerDesign, string> = {
  numbers: 'Numbers (HH:MM:SS or MM:SS)'
}

export const STATUS_DESIGN_LABELS: Record<StatusDesign, string> = {
  word: 'Word (LIVE, PAUSED, etc.)',
  symbols: 'Symbols (▶️ ⏸️ ⏹️)',
  buttons: 'Buttons (colored buttons with symbols)'
}

export const RULES_DESIGN_LABELS: Record<RulesDesign, string> = {
  list: 'List (full-screen text list with timers)'
}

