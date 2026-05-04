import fs from 'node:fs'
import path from 'node:path'

const repoRoot = process.cwd()
const sourcePath = path.join(repoRoot, 'backend/contracts/frontend-api.json')
const outputPath = path.join(repoRoot, 'generated/api-contracts.ts')
const checkOnly = process.argv.includes('--check')

const source = JSON.parse(fs.readFileSync(sourcePath, 'utf8'))

function emitType(name, shape) {
  const lines = [`export interface ${name} {`]
  for (const [key, value] of Object.entries(shape)) {
    lines.push(`  ${key}: ${value}`)
  }
  lines.push('}')
  return lines.join('\n')
}

const output = `${[
  '// This file is generated from backend/contracts/frontend-api.json.',
  '// Do not edit it manually. Run `node scripts/generate-api-contracts.mjs`.',
  '',
  ...Object.entries(source.types).map(([name, shape]) => emitType(name, shape))
].join('\n\n')}\n`

if (checkOnly) {
  const existing = fs.existsSync(outputPath) ? fs.readFileSync(outputPath, 'utf8') : ''
  if (existing !== output) {
    console.error('Generated API contracts are out of date. Run `node scripts/generate-api-contracts.mjs`.')
    process.exit(1)
  }
  process.exit(0)
}

fs.mkdirSync(path.dirname(outputPath), { recursive: true })
fs.writeFileSync(outputPath, output)
