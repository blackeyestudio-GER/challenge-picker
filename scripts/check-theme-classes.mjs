import fs from 'node:fs'
import path from 'node:path'

const repoRoot = process.cwd()
const baselinePath = path.join(repoRoot, 'scripts/theme-guard-baseline.json')
const roots = ['pages', 'components']
const pattern = /\b(?:text|bg|border|from|to|via)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)(?:-[0-9]{2,3})?(?:\/[0-9]{2,3})?\b/g

const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'))
const allowedFiles = new Set(baseline.allowedFiles)
const violations = []

function walk(dir) {
  if (!fs.existsSync(dir)) {
    return
  }

  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name)
    if (entry.isDirectory()) {
      walk(fullPath)
      continue
    }

    if (!/\.(vue|ts|js|mjs)$/.test(entry.name)) {
      continue
    }

    const relativePath = path.relative(repoRoot, fullPath).replaceAll(path.sep, '/')
    const source = fs.readFileSync(fullPath, 'utf8')
    pattern.lastIndex = 0
    if (pattern.test(source) && !allowedFiles.has(relativePath)) {
      violations.push(relativePath)
    }
  }
}

for (const root of roots) {
  walk(path.join(repoRoot, root))
}

if (violations.length > 0) {
  console.error('Theme guard failed. New files with raw palette classes were found:')
  for (const violation of violations) {
    console.error(`- ${violation}`)
  }
  console.error('Use theme variables/classes instead, or explicitly baseline the file if intentional.')
  process.exit(1)
}
