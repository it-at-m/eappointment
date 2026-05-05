import fs from "node:fs";
import path from "node:path";

const FEATURES_ROOT = path.resolve(
  import.meta.dirname,
  "../../zmsautomation/src/test/resources/features"
);
const CUCUMBER_DOC_PATH = path.resolve(
  import.meta.dirname,
  "../testing-and-automation/zmsautomation-cucumber-current.md"
);
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

const renderCucumberDoc = () => {
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
    "# Current Cucumber Tests in zmsautomation",
    "",
    "This page is generated automatically from `zmsautomation/src/test/resources/features`.",
    "When a `.feature` file is added, changed, or removed, this documentation is updated automatically.",
    "",
    "## Recommended Feature Pattern",
    "",
    "Use ticket tags consistently. Always include a Jira tag like `@ZMSKVR-123` on new scenarios/features.",
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
    lines.push("No `.feature` files found.");
  } else {
    for (const [testType, modules] of grouped) {
      lines.push(`## ${testType.toUpperCase()}`);
      lines.push("");
      for (const [module, files] of modules) {
        const moduleTitle =
          testType === "ui" && module === "buergeransicht"
            ? `${module} (deprecated)`
            : module;
        lines.push(`### ${moduleTitle}`);
        lines.push("");
        if (testType === "ui" && module === "buergeransicht") {
          lines.push(
            "> Deprecated: These scenarios target the legacy buergeransicht frontend from `it-at-m/eappointment-buergeransicht` and are not used for `zmscitizenview`."
          );
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
          lines.push(`Source: [${fileName}](${sourceUrl})`);
          lines.push("");
          lines.push("```gherkin");
          lines.push(raw);
          lines.push("```");
          lines.push("");
        }
      }
    }
  }

  const next = `${lines.join("\n").trimEnd()}\n`;
  const prev = fs.existsSync(CUCUMBER_DOC_PATH)
    ? fs.readFileSync(CUCUMBER_DOC_PATH, "utf8")
    : "";
  if (prev !== next) {
    fs.writeFileSync(CUCUMBER_DOC_PATH, next, "utf8");
  }
};

renderCucumberDoc();

export default {
  title: "eAppointment Docs",
  description: "Technical documentation for it-at-m/eappointment",
  base: "/eappointment/",
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
  ],
  themeConfig: {
    nav: [
      { text: "Overview", link: "/" },
      {
        text: "Releases",
        link: "https://github.com/it-at-m/eappointment/releases",
      },
      {
        text: "GitHub Repository",
        link: "https://github.com/it-at-m/eappointment/",
      },
      {
        text: "Open Source",
        link: "https://opensource.muenchen.de/software/zeitmanagementsystem.html",
      },
    ],
    sidebar: [
      {
        text: "Overview",
        items: [
          { text: "Introduction", link: "/" },
          {
            text: "Project History",
            link: "/overview/project-history",
          },
          { text: "Changelog", link: "/overview/changelog" },
        ],
      },
      {
        text: "Setup and Development",
        items: [
          {
            text: "Development Rules",
            collapsed: false,
            items: [
              {
                text: "Dependency Graph",
                link: "/setup-and-development/development-rules/dependency-graph",
              },
              {
                text: "Branching Strategy",
                link: "/setup-and-development/development-rules/branching-strategy-and-convention",
              },
              {
                text: "Commit Message Convention",
                link: "/setup-and-development/development-rules/commit-message-convention",
              },
              {
                text: "Code of Conduct",
                link: "https://github.com/it-at-m/eappointment/blob/main/CODE_OF_CONDUCT.md",
              },
              {
                text: "Contributing",
                link: "https://github.com/it-at-m/eappointment/blob/main/CONTRIBUTING.md",
              },
              {
                text: "Security",
                link: "https://github.com/it-at-m/eappointment/blob/main/SECURITY.md",
              },
              {
                text: "License (EUPL)",
                link: "https://github.com/it-at-m/eappointment/blob/main/LICENSE",
              },
            ],
          },
          {
            text: "Getting Started",
            collapsed: false,
            items: [
              {
                text: "DDEV and Devcontainer",
                link: "/setup-and-development/getting-started/ddev-and-devcontainer",
              },
              {
                text: "Quick reset of the local environment",
                link: "/setup-and-development/getting-started/quick-reset-local-environment",
              },
              {
                text: "Getting Started with GitHub Codespaces",
                link: "/setup-and-development/getting-started/getting-started-with-github-codespaces",
              },
              {
                text: "Getting Started with docs",
                link: "/setup-and-development/getting-started/getting-started-with-docs",
              },
              {
                text: "Running Cronjobs Locally",
                link: "/setup-and-development/getting-started/running-cronjobs-locally",
              },
              {
                text: "macOS local configuration",
                collapsed: false,
                items: [
                  {
                    text: "Podman and Dev Containers",
                    link: "/setup-and-development/getting-started/macos-local-configuration/podman-and-dev-containers",
                  },
                  {
                    text: "Local HTTPS SSL (DDEV)",
                    link: "/setup-and-development/getting-started/macos-local-configuration/local-https-ddev",
                  },
                ],
              },
            ],
          },
          {
            text: "Local Keycloak Setup",
            link: "/setup-and-development/local-keycloak-setup",
          },
          {
            text: "Code Formatting",
            link: "/setup-and-development/code-formatting",
          },
          {
            text: "Local Database and Cache Operations",
            link: "/setup-and-development/local-database-and-cache-operations",
          },
          {
            text: "Dependency Upgrade Check",
            link: "/setup-and-development/dependency-upgrade-check",
          },
          {
            text: "PHP Base Images",
            link: "/setup-and-development/php-base-images",
          },
        ],
      },
      {
        text: "Testing and Automation",
        items: [
          {
            text: "Unit Testing in ZMS",
            link: "/testing-and-automation/testing-unit",
          },
          {
            text: "Unit Test Coverage",
            link: "/testing-and-automation/testing-coverage",
          },
          {
            text: "zmsautomation Documentation",
            link: "/testing-and-automation/zmsautomation",
          },
          {
            text: "Current Cucumber Tests",
            link: "/testing-and-automation/zmsautomation-cucumber-current",
          },
        ],
      },
      {
        text: "Operations",
        items: [
          {
            text: "API reference",
            link: "/operations/api-reference",
          },
          {
            text: "DLDB Interface Documentation",
            link: "/operations/dldb-interface-documentation",
          },
        ],
      },
      {
        text: "Reference",
        items: [{ text: "Module READMEs", link: "/reference/module-readmes" }],
      },
      {
        text: "On the Future",
        items: [
          {
            text: "Database Refactor",
            collapsed: false,
            items: [
              {
                text: "Standardize Database Table and Field Naming",
                link: "/on-the-future/database-refactor/standardize-database-table-and-field-naming",
              },
            ],
          },
          {
            text: "Modernize ZMS Architecture (3-5 Year Plan)",
            link: "/on-the-future/product-oriented-refarch-roadmap",
          },
        ],
      },
    ],
  },
};
