import fs from "node:fs";
import path from "node:path";

import { writeLogInventory } from "../scripts/generate-log-inventory.mjs";

const FEATURES_ROOT = path.resolve(
  import.meta.dirname,
  "../../zmsautomation/src/test/resources/features"
);
const CUCUMBER_DOC_BASENAME =
  "testing-and-automation/zmsautomation-cucumber-current.md";
const CUCUMBER_DOC_TARGETS = [
  // [absolute path, locale code "en" | "de"]
  // The root copy (docs/testing-and-automation/zmsautomation-cucumber-current.md)
  // is mirrored from docs/en/ by mirrorEnToRoot() — no separate target here.
  [path.resolve(import.meta.dirname, `../en/${CUCUMBER_DOC_BASENAME}`), "en"],
  [path.resolve(import.meta.dirname, `../de/${CUCUMBER_DOC_BASENAME}`), "de"],
];
const FEATURE_SOURCE_BASE =
  "https://github.com/it-at-m/eappointment/blob/main/zmsautomation/src/test/resources/features";

const toPosix = (p) => p.split(path.sep).join("/");

const listFeatureFiles = (dir) => {
  const out = [];
  if (!fs.existsSync(dir)) {
    return out;
  }
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      out.push(...listFeatureFiles(full));
      continue;
    }
    if (entry.isFile() && entry.name.endsWith(".feature")) {
      out.push(full);
    }
  }
  return out.sort((a, b) => a.localeCompare(b));
};

const cucumberStrings = {
  en: {
    title: "Current Cucumber Tests in zmsautomation",
    intro: [
      "This page is generated automatically from `zmsautomation/src/test/resources/features`.",
      "When a `.feature` file is added, changed, or removed, this documentation is updated automatically.",
    ],
    patternHeading: "Recommended Feature Pattern",
    patternIntro:
      "Use ticket tags consistently. Always include a Jira tag like `@ZMSKVR-123` on new scenarios/features.",
    deprecated:
      "> Deprecated: These scenarios target the legacy buergeransicht frontend from `it-at-m/eappointment-buergeransicht` and are not used for `zmscitizenview`.",
    deprecatedSuffix: "(deprecated)",
    sourceLabel: "Source",
    noFiles: "No `.feature` files found.",
  },
  de: {
    title: "Aktuelle Cucumber-Tests in zmsautomation",
    intro: [
      "Diese Seite wird automatisch aus `zmsautomation/src/test/resources/features` generiert.",
      "Wenn eine `.feature`-Datei hinzugefügt, geändert oder entfernt wird, wird diese Dokumentation automatisch aktualisiert.",
    ],
    patternHeading: "Empfohlenes Feature-Muster",
    patternIntro:
      "Verwende Ticket-Tags konsistent. Füge immer einen Jira-Tag wie `@ZMSKVR-123` an neuen Szenarien/Features hinzu.",
    deprecated:
      "> Veraltet: Diese Szenarien adressieren das alte buergeransicht-Frontend aus `it-at-m/eappointment-buergeransicht` und werden für `zmscitizenview` nicht mehr verwendet.",
    deprecatedSuffix: "(veraltet)",
    sourceLabel: "Quelle",
    noFiles: "Keine `.feature`-Dateien gefunden.",
  },
};

const renderCucumberDocFor = (locale) => {
  const t = cucumberStrings[locale];
  const featureFiles = listFeatureFiles(FEATURES_ROOT);
  const grouped = new Map();

  for (const file of featureFiles) {
    const rel = toPosix(path.relative(FEATURES_ROOT, file));
    const parts = rel.split("/");
    const testType = parts[0] ?? "other";
    const module = parts[1] ?? "misc";
    if (!grouped.has(testType)) {
      grouped.set(testType, new Map());
    }
    const moduleMap = grouped.get(testType);
    if (!moduleMap.has(module)) {
      moduleMap.set(module, []);
    }
    moduleMap.get(module).push({ abs: file, rel });
  }

  const lines = [
    "---",
    "outline:",
    "  level: [2, 3]",
    "---",
    "",
    `# ${t.title}`,
    "",
    ...t.intro,
    "",
    `## ${t.patternHeading}`,
    "",
    t.patternIntro,
    "",
    "```gherkin",
    "@rest @zmsapi @ZMSKVR-123 @smoke",
    "Feature: Example feature with required ticket tag",
    "  Scenario: Example scenario",
    "    Given the API is available",
    "    When I call the endpoint",
    "    Then the response status code should be 200",
    "```",
    "",
  ];

  if (!featureFiles.length) {
    lines.push(t.noFiles);
  } else {
    for (const [testType, modules] of grouped) {
      lines.push(`## ${testType.toUpperCase()}`);
      lines.push("");
      for (const [module, files] of modules) {
        const moduleTitle =
          testType === "ui" && module === "buergeransicht"
            ? `${module} ${t.deprecatedSuffix}`
            : module;
        lines.push(`### ${moduleTitle}`);
        lines.push("");
        if (testType === "ui" && module === "buergeransicht") {
          lines.push(t.deprecated);
          lines.push("");
        }
        for (const item of files) {
          const fileName = path.basename(item.rel);
          const sourceUrl = `${FEATURE_SOURCE_BASE}/${item.rel}`;
          const raw = fs
            .readFileSync(item.abs, "utf8")
            .replaceAll("```", "\\`\\`\\`")
            .trimEnd();
          lines.push(`#### \`${fileName}\``);
          lines.push("");
          lines.push(`${t.sourceLabel}: [${fileName}](${sourceUrl})`);
          lines.push("");
          lines.push("```gherkin");
          lines.push(raw);
          lines.push("```");
          lines.push("");
        }
      }
    }
  }

  return `${lines.join("\n").trimEnd()}\n`;
};

const renderCucumberDoc = () => {
  for (const [target, locale] of CUCUMBER_DOC_TARGETS) {
    const next = renderCucumberDocFor(locale);
    fs.mkdirSync(path.dirname(target), { recursive: true });
    const prev = fs.existsSync(target) ? fs.readFileSync(target, "utf8") : "";
    if (prev !== next) {
      fs.writeFileSync(target, next, "utf8");
    }
  }
};

renderCucumberDoc();
writeLogInventory();

// docs/en/ is the single on-disk source for English content. We use VitePress
// `rewrites` so every docs/en/<path>.md is rendered at URL /<path>.html (the
// root locale), avoiding any duplicate files at the root. The /en/<path>.html
// URL family does not exist on this site.
const DOCS_DIR = path.resolve(import.meta.dirname, "..");
const EN_DIR = path.join(DOCS_DIR, "en");

const listEnMarkdownFiles = (dir) => {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      out.push(...listEnMarkdownFiles(full));
    } else if (entry.isFile() && entry.name.endsWith(".md")) {
      out.push(full);
    }
  }
  return out;
};

const buildEnRewrites = () => {
  const rewrites = {};
  for (const abs of listEnMarkdownFiles(EN_DIR)) {
    const rel = toPosix(path.relative(EN_DIR, abs));
    rewrites[`en/${rel}`] = rel;
  }
  return rewrites;
};

const GH_REPO = "https://github.com/it-at-m/eappointment";

const navLabels = {
  en: {
    overview: "Overview",
    releases: "Releases",
    openSource: "Open Source",
    openSourceUrl:
      "https://opensource.muenchen.de/software/zeitmanagementsystem.html",
  },
  de: {
    overview: "Übersicht",
    releases: "Releases",
    openSource: "Open Source",
    openSourceUrl:
      "https://opensource.muenchen.de/de/software/zeitmanagementsystem.html",
  },
};

const buildNav = (prefix, lang) => {
  const t = navLabels[lang];
  return [
    { text: t.overview, link: `${prefix}/` },
    { text: t.releases, link: `${GH_REPO}/releases` },
    {
      text: t.openSource,
      link: t.openSourceUrl,
    },
  ];
};

const sidebarLabels = {
  en: {
    overview: "Overview",
    introduction: "Introduction",
    projectHistory: "Project History",
    changelog: "Changelog",
    setupAndDevelopment: "Setup and Development",
    developmentRules: "Development Rules",
    dependencyGraph: "Dependency Graph",
    branchingStrategy: "Branching Strategy",
    commitMessageConvention: "Commit Message Convention",
    monologLogging: "Monolog logging",
    statusMonitoring: "Monitoring and status",
    codeOfConduct: "Code of Conduct",
    contributing: "Contributing",
    security: "Security",
    license: "License (EUPL)",
    gettingStarted: "Getting Started",
    ddevAndDevcontainer: "DDEV and Devcontainer",
    quickReset: "Quick reset of the local environment",
    githubCodespaces: "Getting Started with GitHub Codespaces",
    docsGettingStarted: "Getting Started with docs",
    cronjobsLocally: "Running Cronjobs Locally",
    macosLocalConfig: "macOS local configuration",
    podmanDevContainers: "Podman and Dev Containers (6.x)",
    podmanDevContainersLegacy: "Podman and Dev Containers (5.8, legacy)",
    localHttpsDdev: "Local HTTPS SSL (DDEV)",
    keycloakLocal: "Local Keycloak Setup",
    codeFormatting: "Code Formatting",
    gitHooks: "Git Hooks (Husky)",
    localDbCache: "Local Database and Cache Operations",
    dependencyUpgrade: "Dependency Upgrade Check",
    phpBaseImages: "PHP Base Images",
    testingAndAutomation: "Testing and Automation",
    unitTesting: "Unit Testing in ZMS",
    unitCoverage: "Unit Test Coverage",
    zmsautomation: "zmsautomation Documentation",
    cucumberCurrent: "Current Cucumber Tests",
    operations: "Operations",
    apiReference: "API reference",
    dldb: "DLDB Interface Documentation",
    reference: "Reference",
    moduleReadmes: "Module READMEs",
    onTheFuture: "On the Future",
    databaseRefactor: "Database Refactor",
    standardizeDb: "Standardize Database Table and Field Naming",
    refarchRoadmap: "RefArch Roadmap",
    modernizeArch: "Modernize ZMS Architecture (3-5 Year Plan)",
    backendMergeRefarch: "Refactoring ZMS Backends into Spring RefArch",
    dynamicCache: "Dynamic Cache Layer and Bulk Queries",
  },
  de: {
    overview: "Übersicht",
    introduction: "Einführung",
    projectHistory: "Projektgeschichte",
    changelog: "Änderungsprotokoll",
    setupAndDevelopment: "Einrichtung und Entwicklung",
    developmentRules: "Entwicklungsregeln",
    dependencyGraph: "Abhängigkeitsgraph",
    branchingStrategy: "Branching-Strategie",
    commitMessageConvention: "Commit-Message-Konvention",
    monologLogging: "Monolog-Logging",
    statusMonitoring: "Monitoring und Status",
    codeOfConduct: "Verhaltenskodex",
    contributing: "Mitwirken",
    security: "Sicherheit",
    license: "Lizenz (EUPL)",
    gettingStarted: "Erste Schritte",
    ddevAndDevcontainer: "DDEV und Devcontainer",
    quickReset: "Schnelles Zurücksetzen der lokalen Umgebung",
    githubCodespaces: "Erste Schritte mit GitHub Codespaces",
    docsGettingStarted: "Erste Schritte mit der Dokumentation",
    cronjobsLocally: "Cronjobs lokal ausführen",
    macosLocalConfig: "macOS lokale Konfiguration",
    podmanDevContainers: "Podman und Dev Containers (6.x)",
    podmanDevContainersLegacy: "Podman und Dev Containers (5.8, Legacy)",
    localHttpsDdev: "Lokales HTTPS-SSL (DDEV)",
    keycloakLocal: "Lokale Keycloak-Einrichtung",
    codeFormatting: "Code-Formatierung",
    gitHooks: "Git-Hooks (Husky)",
    localDbCache: "Lokale Datenbank- und Cache-Operationen",
    dependencyUpgrade: "Abhängigkeits-Aktualisierungsprüfung",
    phpBaseImages: "PHP-Basis-Images",
    testingAndAutomation: "Tests und Automatisierung",
    unitTesting: "Unit-Tests in ZMS",
    unitCoverage: "Unit-Test-Abdeckung",
    zmsautomation: "zmsautomation-Dokumentation",
    cucumberCurrent: "Aktuelle Cucumber-Tests",
    operations: "Betrieb",
    apiReference: "API-Referenz",
    dldb: "DLDB-Schnittstellendokumentation",
    reference: "Referenz",
    moduleReadmes: "Modul-READMEs",
    onTheFuture: "Ausblick",
    databaseRefactor: "Datenbank-Refactoring",
    standardizeDb: "Datenbanktabellen- und Feldbenennung standardisieren",
    refarchRoadmap: "RefArch-Roadmap",
    modernizeArch: "ZMS-Architektur modernisieren (3-5-Jahresplan)",
    backendMergeRefarch: "Refactoring ZMS Backends in Spring RefArch",
    dynamicCache: "Dynamische Cache-Schicht und Bulk-Queries",
  },
};

const buildSidebar = (prefix, lang) => {
  const t = sidebarLabels[lang];
  return [
    {
      text: t.overview,
      items: [
        { text: t.introduction, link: `${prefix}/` },
        { text: t.projectHistory, link: `${prefix}/overview/project-history` },
        { text: t.changelog, link: `${prefix}/overview/changelog` },
      ],
    },
    {
      text: t.setupAndDevelopment,
      items: [
        {
          text: t.developmentRules,
          collapsed: false,
          items: [
            {
              text: t.dependencyGraph,
              link: `${prefix}/setup-and-development/development-rules/dependency-graph`,
            },
            {
              text: t.branchingStrategy,
              link: `${prefix}/setup-and-development/development-rules/branching-strategy-and-convention`,
            },
            {
              text: t.commitMessageConvention,
              link: `${prefix}/setup-and-development/development-rules/commit-message-convention`,
            },
            {
              text: t.codeOfConduct,
              link: `${GH_REPO}/blob/main/.github/CODE_OF_CONDUCT.md`,
            },
            {
              text: t.contributing,
              link: `${GH_REPO}/blob/main/CONTRIBUTING.md`,
            },
            {
              text: t.security,
              link: `${GH_REPO}/blob/main/SECURITY.md`,
            },
            {
              text: t.license,
              link: `${GH_REPO}/blob/main/LICENSE`,
            },
          ],
        },
        {
          text: t.gettingStarted,
          collapsed: false,
          items: [
            {
              text: t.ddevAndDevcontainer,
              link: `${prefix}/setup-and-development/getting-started/ddev-and-devcontainer`,
            },
            {
              text: t.codeFormatting,
              link: `${prefix}/setup-and-development/code-formatting`,
            },
            {
              text: t.gitHooks,
              link: `${prefix}/setup-and-development/git-hooks`,
            },
            {
              text: t.quickReset,
              link: `${prefix}/setup-and-development/getting-started/quick-reset-local-environment`,
            },
            {
              text: t.githubCodespaces,
              link: `${prefix}/setup-and-development/getting-started/getting-started-with-github-codespaces`,
            },
            {
              text: t.docsGettingStarted,
              link: `${prefix}/setup-and-development/getting-started/getting-started-with-docs`,
            },
            {
              text: t.cronjobsLocally,
              link: `${prefix}/setup-and-development/getting-started/running-cronjobs-locally`,
            },
            {
              text: t.macosLocalConfig,
              collapsed: false,
              items: [
                {
                  text: t.podmanDevContainers,
                  link: `${prefix}/setup-and-development/getting-started/macos-local-configuration/podman-and-dev-containers`,
                },
                {
                  text: t.podmanDevContainersLegacy,
                  link: `${prefix}/setup-and-development/getting-started/macos-local-configuration/podman-and-dev-containers-legacy`,
                },
                {
                  text: t.localHttpsDdev,
                  link: `${prefix}/setup-and-development/getting-started/macos-local-configuration/local-https-ddev`,
                },
              ],
            },
          ],
        },
        {
          text: t.keycloakLocal,
          link: `${prefix}/setup-and-development/local-keycloak-setup`,
        },
        {
          text: t.localDbCache,
          link: `${prefix}/setup-and-development/local-database-and-cache-operations`,
        },
        {
          text: t.dependencyUpgrade,
          link: `${prefix}/setup-and-development/dependency-upgrade-check`,
        },
        {
          text: t.phpBaseImages,
          link: `${prefix}/setup-and-development/php-base-images`,
        },
      ],
    },
    {
      text: t.testingAndAutomation,
      items: [
        {
          text: t.unitTesting,
          link: `${prefix}/testing-and-automation/testing-unit`,
        },
        {
          text: t.unitCoverage,
          link: `${prefix}/testing-and-automation/testing-coverage`,
        },
        {
          text: t.zmsautomation,
          link: `${prefix}/testing-and-automation/zmsautomation`,
        },
        {
          text: t.cucumberCurrent,
          link: `${prefix}/testing-and-automation/zmsautomation-cucumber-current`,
        },
      ],
    },
    {
      text: t.operations,
      items: [
        {
          text: t.apiReference,
          link: `${prefix}/operations/api-reference`,
        },
        {
          text: t.dldb,
          link: `${prefix}/operations/dldb-interface-documentation`,
        },
        {
          text: t.monologLogging,
          link: `${prefix}/operations/monolog-logging`,
        },
        {
          text: t.statusMonitoring,
          link: `${prefix}/operations/monitoring-and-status`,
        },
      ],
    },
    {
      text: t.reference,
      items: [
        {
          text: t.moduleReadmes,
          link: `${prefix}/reference/module-readmes`,
        },
      ],
    },
    {
      text: t.onTheFuture,
      items: [
        {
          text: t.databaseRefactor,
          collapsed: false,
          items: [
            {
              text: t.standardizeDb,
              link: `${prefix}/on-the-future/database-refactor/standardize-database-table-and-field-naming`,
            },
          ],
        },
        {
          text: t.refarchRoadmap,
          collapsed: false,
          items: [
            {
              text: t.modernizeArch,
              link: `${prefix}/on-the-future/refarch-roadmap/product-oriented-refarch-roadmap`,
            },
            {
              text: t.backendMergeRefarch,
              link: `${prefix}/on-the-future/refarch-roadmap/backend-merge-spring-refarch-example`,
            },
          ],
        },
        {
          text: t.dynamicCache,
          link: `${prefix}/on-the-future/dynamic-cache-and-bulk-queries`,
        },
      ],
    },
  ];
};

const sharedThemeConfig = {
  socialLinks: [
    {
      icon: "github",
      link: `${GH_REPO}/`,
      ariaLabel: "Munich GitHub Repository",
    },
    {
      icon: "gitlab",
      link: "https://gitlab.com/eappointment/eappointment",
      ariaLabel: "Berlin GitLab Repository",
    },
  ],
  search: {
    provider: "local",
    options: {
      locales: {
        de: {
          translations: {
            button: {
              buttonText: "Suchen",
              buttonAriaLabel: "Suchen",
            },
            modal: {
              displayDetails: "Details anzeigen",
              resetButtonTitle: "Suche zurücksetzen",
              backButtonTitle: "Suche schließen",
              noResultsText: "Keine Ergebnisse",
              footer: {
                selectText: "Auswählen",
                selectKeyAriaLabel: "Eingabe",
                navigateText: "Navigieren",
                navigateUpKeyAriaLabel: "Pfeil nach oben",
                navigateDownKeyAriaLabel: "Pfeil nach unten",
                closeText: "Schließen",
                closeKeyAriaLabel: "esc",
              },
            },
          },
        },
      },
    },
  },
};

const SITE_HOSTNAME = "https://it-at-m.github.io";
const SITE_BASE = "/eappointment/";

// Convert a source `relativePath` (e.g. "en/overview/foo.md", "de/index.md",
// "testing-and-automation/zmsautomation-cucumber-current.md") into the public
// URL path under SITE_BASE (e.g. "overview/foo.html", "de/", "testing-and-...html").
// The `en/` prefix is stripped because docs/en/* is rewritten to the root URL.
const toUrlPath = (relativePath) => {
  let p = (relativePath || "").replace(/\\/g, "/");
  p = p.replace(/^en\//, "");
  p = p.replace(/\.md$/, ".html");
  p = p.replace(/(^|\/)index\.html$/, "$1");
  return p;
};

const stripDeLocalePrefix = (urlPath) => urlPath.replace(/^de\//, "");

const buildAbsoluteUrl = (urlPath) => `${SITE_HOSTNAME}${SITE_BASE}${urlPath}`;

const localeOfUrl = (urlPath) => (urlPath.startsWith("de/") ? "de" : "en");

export default {
  title: "eAppointment Docs",
  description: "Technical documentation for it-at-m/eappointment",
  base: SITE_BASE,
  lang: "en-US",
  vite: {
    esbuild: {
      target: "es2022",
    },
    optimizeDeps: {
      esbuildOptions: {
        target: "es2022",
      },
    },
    build: {
      target: "es2022",
    },
  },
  rewrites: buildEnRewrites(),
  sitemap: {
    // hostname must include the base path so emitted <loc> URLs are absolute
    // and resolve to the actual GitHub Pages location.
    hostname: `${SITE_HOSTNAME}${SITE_BASE}`,
  },
  transformHead({ pageData, siteConfig }) {
    const tags = [];
    const urlPath = toUrlPath(pageData.relativePath);
    const fullUrl = buildAbsoluteUrl(urlPath);
    const stripped = stripDeLocalePrefix(urlPath);
    const enUrl = buildAbsoluteUrl(stripped);
    const deUrl = buildAbsoluteUrl(`de/${stripped}`);
    const locale = localeOfUrl(urlPath);
    const ogLocale = locale === "de" ? "de_DE" : "en_US";

    const fm = pageData.frontmatter || {};
    const siteTitle = siteConfig?.site?.title || "eAppointment Docs";
    const siteDescription =
      siteConfig?.site?.description ||
      "Technical documentation for it-at-m/eappointment";
    const pageTitle = fm.title || pageData.title || siteTitle;
    const pageDescription =
      fm.description || pageData.description || siteDescription;
    const ogImage = `${SITE_HOSTNAME}${SITE_BASE}img/logo.png`;

    tags.push(["link", { rel: "canonical", href: fullUrl }]);
    tags.push(["link", { rel: "alternate", hreflang: "en", href: enUrl }]);
    tags.push(["link", { rel: "alternate", hreflang: "de", href: deUrl }]);
    tags.push([
      "link",
      { rel: "alternate", hreflang: "x-default", href: enUrl },
    ]);

    tags.push(["meta", { property: "og:type", content: "article" }]);
    tags.push(["meta", { property: "og:site_name", content: siteTitle }]);
    tags.push(["meta", { property: "og:title", content: pageTitle }]);
    tags.push([
      "meta",
      { property: "og:description", content: pageDescription },
    ]);
    tags.push(["meta", { property: "og:url", content: fullUrl }]);
    tags.push(["meta", { property: "og:image", content: ogImage }]);
    tags.push(["meta", { property: "og:locale", content: ogLocale }]);
    tags.push(["meta", { name: "twitter:card", content: "summary" }]);
    tags.push(["meta", { name: "twitter:title", content: pageTitle }]);
    tags.push([
      "meta",
      { name: "twitter:description", content: pageDescription },
    ]);
    tags.push(["meta", { name: "twitter:image", content: ogImage }]);

    return tags;
  },
  markdown: {
    config(md) {
      const defaultFence = md.renderer.rules.fence;
      md.renderer.rules.fence = (tokens, idx, options, env, self) => {
        const token = tokens[idx];
        const info = md.utils.unescapeAll(token.info || "").trim();
        const langName = info.split(/\s+/g)[0];
        if (langName === "mermaid") {
          return `<pre class="mermaid" v-pre>${md.utils.escapeHtml(token.content)}</pre>`;
        }
        if (defaultFence) {
          return defaultFence(tokens, idx, options, env, self);
        }
        return `<pre><code>${md.utils.escapeHtml(token.content)}</code></pre>`;
      };
    },
  },
  head: [
    [
      "link",
      {
        rel: "icon",
        href: "https://assets.muenchen.de/logos/lhm/icon-lhm-muenchen-32.png",
      },
    ],
    ["meta", { name: "robots", content: "index,follow" }],
    ["meta", { name: "author", content: "it@M / Landeshauptstadt München" }],
    [
      "meta",
      {
        name: "keywords",
        content:
          "eAppointment, ZMS, Zeitmanagementsystem, Termin, Munich, München, it-at-m, open source, government, docker, php, vuejs, twig, keycloak, sso, city, municipalities, appointment scheduling, sso authentication, sso login, appointment booking, ddev, government app, appointments manager, eappointments, appointment management system, municipal software, county level",
      },
    ],
  ],
  themeConfig: {
    ...sharedThemeConfig,
  },
  locales: {
    root: {
      label: "English",
      lang: "en",
      themeConfig: {
        ...sharedThemeConfig,
        nav: buildNav("", "en"),
        sidebar: buildSidebar("", "en"),
      },
    },
    de: {
      label: "Deutsch",
      lang: "de",
      link: "/de/",
      title: "eAppointment-Doku",
      description: "Technische Dokumentation für it-at-m/eappointment",
      themeConfig: {
        ...sharedThemeConfig,
        nav: buildNav("/de", "de"),
        sidebar: buildSidebar("/de", "de"),
        outline: { label: "Auf dieser Seite", level: [2, 4] },
        darkModeSwitchLabel: "Darstellung",
        langMenuLabel: "Sprache wechseln",
        returnToTopLabel: "Zurück nach oben",
        sidebarMenuLabel: "Menü",
        docFooter: { prev: "Vorherige Seite", next: "Nächste Seite" },
        lastUpdatedText: "Zuletzt aktualisiert",
      },
    },
  },
};
