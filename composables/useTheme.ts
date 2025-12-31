/**
 * Simple Theme Helper - Returns CSS class names
 * 
 * Colors are defined in assets/css/main.css using CSS custom properties.
 * This is just a simple helper to get the right class name.
 */

export const useTheme = () => {
  /**
   * Get rule type badge CSS class
   */
  const getRuleTypeBadge = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'rule-type-badge-legendary'
    if (type === 'court') return 'rule-type-badge-court'
    if (type === 'basic') return 'rule-type-badge-basic'
    return 'rule-type-badge-basic' // default
  }

  /**
   * Get rule type background CSS class
   */
  const getRuleTypeBg = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'rule-type-bg-legendary'
    if (type === 'court') return 'rule-type-bg-court'
    if (type === 'basic') return 'rule-type-bg-basic'
    return 'rule-type-bg-basic' // default
  }

  /**
   * Get rule type border CSS class
   */
  const getRuleTypeBorder = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'rule-type-border-legendary'
    if (type === 'court') return 'rule-type-border-court'
    if (type === 'basic') return 'rule-type-border-basic'
    return 'rule-type-border-basic' // default
  }

  /**
   * Get rule type text CSS class
   */
  const getRuleTypeText = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'rule-type-text-legendary'
    if (type === 'court') return 'rule-type-text-court'
    if (type === 'basic') return 'rule-type-text-basic'
    return 'rule-type-text-basic' // default
  }

  /**
   * Get rule type icon CSS class
   */
  const getRuleTypeIcon = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'rule-type-icon-legendary'
    if (type === 'court') return 'rule-type-icon-court'
    if (type === 'basic') return 'rule-type-icon-basic'
    return 'rule-type-icon-basic' // default
  }

  /**
   * Get rule type display name
   */
  const getRuleTypeName = (ruleType: string): string => {
    const type = ruleType.toLowerCase()
    if (type === 'legendary') return 'Legendary'
    if (type === 'court') return 'Court'
    if (type === 'basic') return 'Basic'
    return 'Basic'
  }

  return {
    getRuleTypeBadge,
    getRuleTypeBg,
    getRuleTypeBorder,
    getRuleTypeText,
    getRuleTypeIcon,
    getRuleTypeName
  }
}

