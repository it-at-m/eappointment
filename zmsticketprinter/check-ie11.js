#!/usr/bin/env node
/**
 * IE11 Compatibility Checker
 * Checks JavaScript files for ES6+ syntax that IE11 doesn't support
 */

const fs = require('fs');
const path = require('path');

const file = process.argv[2] || 'public/_js/index.js';

if (!fs.existsSync(file)) {
  console.error('File not found:', file);
  process.exit(1);
}

const code = fs.readFileSync(file, 'utf8');

const checks = [
  { pattern: /=>/g, name: 'Arrow functions (=>)' },
  { pattern: /\bconst\s+[a-zA-Z_]/g, name: 'const declarations' },
  { pattern: /\blet\s+[a-zA-Z_]/g, name: 'let declarations' },
  { pattern: /\bclass\s+[A-Z][a-zA-Z0-9_]*\s*(extends|\{)/g, name: 'ES6 classes' },
  { pattern: /`/g, name: 'Template literals' },
  { pattern: /\.\.\.[a-zA-Z_\[]/g, name: 'Spread operator' },
];

console.log('IE11 Compatibility Check: ' + path.basename(file));
console.log('='.repeat(40));

let allPassed = true;

checks.forEach(({ pattern, name }) => {
  const matches = code.match(pattern);
  const count = matches ? matches.length : 0;
  const status = count === 0 ? '✅' : '❌';
  console.log(`${status} ${name}: ${count}`);
  if (count > 0) allPassed = false;
});

console.log('='.repeat(40));
console.log(allPassed ? '\n✅ IE11 COMPATIBLE!' : '\n❌ NOT IE11 compatible');
process.exit(allPassed ? 0 : 1);

