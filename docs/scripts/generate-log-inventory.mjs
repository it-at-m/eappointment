/**
 * Scans the monorepo for App::$log usage and writes docs/.vitepress/data/log-inventory.json.
 * Run: cd docs && npm run docs:log-inventory
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const REPO_ROOT = path.resolve(__dirname, "../..");
const OUTPUT = path.resolve(__dirname, "../.vitepress/data/log-inventory.json");

const MODULE_PREFIXES = [
  "zmsslim",
  "zmsbackend",
  "zmsadmin",
  "zmsdldb",
  "zmsentities",
  "zmsclient",
  "zmscitizenapi",
  "zmsmessaging",
  "zmsstatistic",
  "zmsticketprinter",
  "zmscalldisplay",
  "mellon",
];

const SKIP_DIR_NAMES = new Set([
  "vendor",
  "node_modules",
  ".git",
  ".phpunit.cache",
  "coverage",
  "fixtures",
  ".vitepress",
]);

const LOG_METHOD =
  /(?:\\)?App::\$log->(debug|info|notice|warning|warn|error|critical|alert|emergency)\s*\(/gi;

const LOG_DYNAMIC = /(?:\\)?App::\$log->\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/g;

const STRING_LITERAL = /^['"]([^'"]*)['"]/;

const normalizeLevel = (level) => {
  const lower = level.toLowerCase();
  if (lower === "warn") {
    return "warning";
  }
  return lower;
};

const detectModule = (relativePath) => {
  const top = relativePath.split("/")[0];
  if (MODULE_PREFIXES.includes(top)) {
    return top;
  }
  return "other";
};

const isTestPath = (relativePath) =>
  /(?:^|\/)(tests?|Tests?)(?:\/|$)/.test(relativePath) ||
  relativePath.includes("/tests/") ||
  /Test\.php$/.test(relativePath);

const shouldScanFile = (relativePath) => {
  if (!relativePath.endsWith(".php")) {
    return false;
  }
  if (isTestPath(relativePath)) {
    return false;
  }
  const top = relativePath.split("/")[0];
  if (MODULE_PREFIXES.includes(top)) {
    return true;
  }
  return false;
};

const walkPhpFiles = (dir, base = dir, out = []) => {
  if (!fs.existsSync(dir)) {
    return out;
  }
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (SKIP_DIR_NAMES.has(entry.name)) {
      continue;
    }
    const full = path.join(dir, entry.name);
    const rel = path.relative(base, full);
    if (entry.isDirectory()) {
      walkPhpFiles(full, base, out);
      continue;
    }
    if (entry.isFile() && shouldScanFile(rel)) {
      out.push({ abs: full, rel: rel.split(path.sep).join("/") });
    }
  }
  return out;
};

const extractMessage = (line, openParenIndex) => {
  const after = line.slice(openParenIndex + 1).trimStart();
  const stringMatch = after.match(STRING_LITERAL);
  if (stringMatch) {
    return stringMatch[1];
  }
  if (after.startsWith("$") || after.startsWith("(")) {
    return "(expression)";
  }
  return "(dynamic)";
};

const scanFile = (file) => {
  const content = fs.readFileSync(file.abs, "utf8");
  const lines = content.split("\n");
  const entries = [];
  const module = detectModule(file.rel);

  lines.forEach((line, index) => {
    const lineNo = index + 1;

    for (const match of line.matchAll(LOG_METHOD)) {
      const level = normalizeLevel(match[1]);
      const openParen = match.index + match[0].length - 1;
      entries.push({
        module,
        level,
        message: extractMessage(line, openParen),
        file: file.rel,
        line: lineNo,
        kind: "static",
      });
    }

    for (const match of line.matchAll(LOG_DYNAMIC)) {
      entries.push({
        module,
        level: "dynamic",
        message: `(via $${match[1]})`,
        file: file.rel,
        line: lineNo,
        kind: "variable",
        variable: match[1],
      });
    }
  });

  return entries;
};

export const generateLogInventory = () => {
  const files = MODULE_PREFIXES.flatMap((name) => {
    const moduleRoot = path.join(REPO_ROOT, name);
    return fs.existsSync(moduleRoot) ? walkPhpFiles(moduleRoot, REPO_ROOT) : [];
  });

  const entries = files.flatMap(scanFile).sort((a, b) => {
    const moduleCmp = a.module.localeCompare(b.module);
    if (moduleCmp !== 0) {
      return moduleCmp;
    }
    const levelCmp = a.level.localeCompare(b.level);
    if (levelCmp !== 0) {
      return levelCmp;
    }
    const fileCmp = a.file.localeCompare(b.file);
    if (fileCmp !== 0) {
      return fileCmp;
    }
    return a.line - b.line;
  });

  const byModule = {};
  const byLevel = {};
  for (const entry of entries) {
    byModule[entry.module] = (byModule[entry.module] ?? 0) + 1;
    byLevel[entry.level] = (byLevel[entry.level] ?? 0) + 1;
  }

  return {
    generatedAt: new Date().toISOString(),
    repoRoot: "eappointment",
    scanNote:
      "Production PHP only (module src/cron/bin). Excludes vendor, tests, and test_mysql-style dev scripts under Importer/.",
    entries,
    totals: {
      entries: entries.length,
      byModule,
      byLevel,
    },
    modules: [...new Set(entries.map((e) => e.module))].sort(),
    levels: [...new Set(entries.map((e) => e.level))].sort(),
  };
};

export const writeLogInventory = () => {
  const data = generateLogInventory();
  fs.mkdirSync(path.dirname(OUTPUT), { recursive: true });
  fs.writeFileSync(OUTPUT, `${JSON.stringify(data, null, 2)}\n`, "utf8");
  return { output: OUTPUT, totals: data.totals };
};

const isMain =
  process.argv[1] &&
  path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isMain) {
  const { output, totals } = writeLogInventory();
  console.log(`Wrote ${totals.entries} log call(s) to ${output}`);
}
