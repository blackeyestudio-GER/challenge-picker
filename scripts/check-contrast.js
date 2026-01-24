/**
 * Contrast Ratio Checker
 * 
 * Checks WCAG AA compliance (4.5:1 for normal text, 3:1 for large text)
 * WCAG AAA requires 7:1 for normal text, 4.5:1 for large text
 */

// Helper function to convert hex to RGB
function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
}

// Helper function to convert RGB to relative luminance
function getLuminance(rgb) {
  const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(val => {
    val = val / 255;
    return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

// Calculate contrast ratio between two colors
function getContrastRatio(color1, color2) {
  const lum1 = getLuminance(color1);
  const lum2 = getLuminance(color2);
  const lighter = Math.max(lum1, lum2);
  const darker = Math.min(lum1, lum2);
  return (lighter + 0.05) / (darker + 0.05);
}

// Check if contrast meets WCAG standards
function checkContrast(textColor, bgColor, textSize = 'normal') {
  const textRgb = typeof textColor === 'string' ? hexToRgb(textColor) : textColor;
  const bgRgb = typeof bgColor === 'string' ? hexToRgb(bgColor) : bgColor;
  
  if (!textRgb || !bgRgb) {
    return { error: 'Invalid color format' };
  }
  
  const ratio = getContrastRatio(textRgb, bgRgb);
  const minRatio = textSize === 'large' ? 3 : 4.5;
  const minRatioAAA = textSize === 'large' ? 4.5 : 7;
  
  return {
    ratio: ratio.toFixed(2),
    passesAA: ratio >= minRatio,
    passesAAA: ratio >= minRatioAAA,
    textColor: typeof textColor === 'string' ? textColor : `rgb(${textColor.r}, ${textColor.g}, ${textColor.b})`,
    bgColor: typeof bgColor === 'string' ? bgColor : `rgb(${bgColor.r}, ${bgColor.g}, ${bgColor.b})`,
  };
}

// Light Theme Color Combinations
const lightThemeCombinations = [
  // Primary text on backgrounds
  { text: '#000000', bg: '#ffffff', name: 'Primary text on white', size: 'normal' },
  { text: '#4b5563', bg: '#ffffff', name: 'Secondary text on white', size: 'normal' },
  { text: '#6b7280', bg: '#ffffff', name: 'Tertiary text on white', size: 'normal' },
  { text: '#6b7280', bg: '#ffffff', name: 'Muted text on white', size: 'normal' },
  
  // Accent colors on white
  { text: '#155e75', bg: '#ffffff', name: 'Accent primary dark on white', size: 'normal' },
  { text: '#86198f', bg: '#ffffff', name: 'Accent secondary dark on white', size: 'normal' },
  
  // Button text on button backgrounds
  { text: '#000000', bg: '#a5f3fc', name: 'Button text on primary bg (cyan)', size: 'normal' },
  { text: '#000000', bg: '#f5d0fe', name: 'Button text on primary bg (magenta)', size: 'normal' },
  { text: '#000000', bg: '#f9fafb', name: 'Button text on secondary bg', size: 'normal' },
  { text: '#047857', bg: '#d1fae5', name: 'Success button text on bg', size: 'normal' },
  { text: '#991b1b', bg: '#fee2e2', name: 'Danger button text on bg', size: 'normal' },
  { text: '#92400e', bg: '#fef9c3', name: 'Warning button text on bg', size: 'normal' },
  
  // Status badges
  { text: '#047857', bg: '#d1fae5', name: 'Active status text on bg', size: 'normal' },
  { text: '#155e75', bg: '#cffafe', name: 'Completed status text on bg', size: 'normal' },
  { text: '#b91c1c', bg: '#fee2e2', name: 'Failed status text on bg', size: 'normal' },
  { text: '#a16207', bg: '#fef9c3', name: 'Pending status text on bg', size: 'normal' },
  { text: '#4b5563', bg: '#f3f4f6', name: 'Inactive status text on bg', size: 'normal' },
  
  // Rule type colors
  { text: '#a16207', bg: '#fef9c3', name: 'Legendary rule text on bg', size: 'normal' },
  { text: '#6b21a8', bg: '#ede9fe', name: 'Court rule text on bg', size: 'normal' },
  { text: '#1e40af', bg: '#dbeafe', name: 'Basic rule text on bg', size: 'normal' },
  
  // Card backgrounds
  { text: '#000000', bg: 'rgba(255, 255, 255, 0.95)', name: 'Text on card bg', size: 'normal' },
  { text: '#4b5563', bg: '#fafbfc', name: 'Secondary text on secondary bg', size: 'normal' },
];

// Dark Theme Color Combinations
const darkThemeCombinations = [
  // Primary text on backgrounds
  { text: '#ffffff', bg: '#111212', name: 'Primary text on dark bg', size: 'normal' },
  { text: '#e5e7eb', bg: '#111212', name: 'Secondary text on dark bg', size: 'normal' },
  { text: '#d1d5db', bg: '#111212', name: 'Tertiary text on dark bg', size: 'normal' },
  { text: '#9ca3af', bg: '#111212', name: 'Muted text on dark bg', size: 'normal' },
  
  // Accent colors on dark backgrounds
  { text: '#06b6d4', bg: '#111212', name: 'Accent primary on dark bg', size: 'normal' },
  { text: '#d946ef', bg: '#111212', name: 'Accent secondary on dark bg', size: 'normal' },
  
  // Button text on button backgrounds (with opacity)
  // Updated to use darker colors for WCAG AA contrast (4.5:1 minimum)
  { text: '#ffffff', bg: '#0e7490', name: 'Button text on primary bg (cyan)', size: 'normal' },
  { text: '#ffffff', bg: '#a21caf', name: 'Button text on secondary bg (magenta)', size: 'normal' },
  { text: '#ffffff', bg: '#0d6982', name: 'Button text on primary hover (cyan)', size: 'normal' },
  { text: '#ffffff', bg: '#8b1a96', name: 'Button text on secondary hover (magenta)', size: 'normal' },
  { text: '#ffffff', bg: '#c026d3', name: 'Button text on secondary hover (magenta)', size: 'normal' },
  { text: '#4ade80', bg: 'rgba(34, 197, 94, 0.2)', name: 'Success button text on bg', size: 'normal' },
  { text: '#f87171', bg: 'rgba(239, 68, 68, 0.2)', name: 'Danger button text on bg', size: 'normal' },
  { text: '#facc15', bg: 'rgba(234, 179, 8, 0.2)', name: 'Warning button text on bg', size: 'normal' },
  
  // Status badges
  { text: '#4ade80', bg: 'rgba(34, 197, 94, 0.15)', name: 'Active status text on bg', size: 'normal' },
  { text: '#67e8f9', bg: 'rgba(6, 182, 212, 0.15)', name: 'Completed status text on bg', size: 'normal' },
  { text: '#f87171', bg: 'rgba(239, 68, 68, 0.15)', name: 'Failed status text on bg', size: 'normal' },
  { text: '#facc15', bg: 'rgba(234, 179, 8, 0.15)', name: 'Pending status text on bg', size: 'normal' },
  { text: '#d1d5db', bg: 'rgba(107, 114, 128, 0.15)', name: 'Inactive status text on bg', size: 'normal' },
  
  // Rule type colors
  { text: '#facc15', bg: 'rgba(234, 179, 8, 0.15)', name: 'Legendary rule text on bg', size: 'normal' },
  { text: '#c084fc', bg: 'rgba(168, 85, 247, 0.15)', name: 'Court rule text on bg', size: 'normal' },
  { text: '#60a5fa', bg: 'rgba(59, 130, 246, 0.15)', name: 'Basic rule text on bg', size: 'normal' },
];

// Helper to blend rgba with background
function blendWithBackground(rgba, bgColor) {
  const bg = hexToRgb(bgColor);
  const alpha = parseFloat(rgba.match(/[\d.]+/g)[3]);
  const r = parseFloat(rgba.match(/[\d.]+/g)[0]);
  const g = parseFloat(rgba.match(/[\d.]+/g)[1]);
  const b = parseFloat(rgba.match(/[\d.]+/g)[2]);
  
  return {
    r: Math.round(r * alpha + bg.r * (1 - alpha)),
    g: Math.round(g * alpha + bg.g * (1 - alpha)),
    b: Math.round(b * alpha + bg.b * (1 - alpha))
  };
}

// Process combinations
function processCombinations(combinations, themeName) {
  console.log(`\n=== ${themeName} Theme Contrast Check ===\n`);
  
  const issues = [];
  
  combinations.forEach(combo => {
    let bgRgb;
    if (combo.bg.startsWith('rgba')) {
      // Blend rgba with background
      const bgColor = themeName === 'Light' ? '#ffffff' : '#111212';
      bgRgb = blendWithBackground(combo.bg, bgColor);
    } else {
      bgRgb = hexToRgb(combo.bg);
    }
    
    const textRgb = hexToRgb(combo.text);
    const result = checkContrast(textRgb, bgRgb, combo.size);
    
    if (result.error) {
      console.log(`❌ ${combo.name}: ${result.error}`);
      return;
    }
    
    const status = result.passesAA ? '✅' : '❌';
    const aaStatus = result.passesAA ? 'PASS' : 'FAIL';
    const aaaStatus = result.passesAAA ? 'PASS' : 'FAIL';
    
    console.log(`${status} ${combo.name}`);
    console.log(`   Ratio: ${result.ratio}:1 | AA: ${aaStatus} | AAA: ${aaaStatus}`);
    console.log(`   Text: ${result.textColor} | BG: ${result.bgColor}`);
    
    if (!result.passesAA) {
      issues.push({
        name: combo.name,
        ratio: result.ratio,
        textColor: result.textColor,
        bgColor: result.bgColor,
        required: combo.size === 'large' ? '3:1' : '4.5:1'
      });
    }
    
    console.log('');
  });
  
  if (issues.length > 0) {
    console.log(`\n⚠️  Found ${issues.length} contrast issues:\n`);
    issues.forEach(issue => {
      console.log(`- ${issue.name}: ${issue.ratio}:1 (requires ${issue.required})`);
      console.log(`  Text: ${issue.textColor} | BG: ${issue.bgColor}\n`);
    });
  } else {
    console.log('\n✅ All combinations pass WCAG AA standards!\n');
  }
  
  return issues;
}

// Run checks
const lightIssues = processCombinations(lightThemeCombinations, 'Light');
const darkIssues = processCombinations(darkThemeCombinations, 'Dark');

// Summary
console.log('\n=== Summary ===');
console.log(`Light theme issues: ${lightIssues.length}`);
console.log(`Dark theme issues: ${darkIssues.length}`);
console.log(`Total issues: ${lightIssues.length + darkIssues.length}`);

