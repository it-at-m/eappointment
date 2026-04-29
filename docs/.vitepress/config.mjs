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
    }
  },
  head: [
    [
      "link",
      {
        rel: "icon",
        href: "https://assets.muenchen.de/logos/lhm/icon-lhm-muenchen-32.png"
      }
    ]
  ],
  themeConfig: {
    nav: [
      { text: "Overview", link: "/" },
      { text: "API reference", link: "/api-reference" },
      { text: "Testing", link: "/testing-unit" },
      { text: "GitHub Repository", link: "https://github.com/it-at-m/eappointment/" },
      { text: "Open Source", link: "https://opensource.muenchen.de/software/zeitmanagementsystem.html" }
    ],
    sidebar: [
      { text: "Overview", items: [{ text: "Introduction", link: "/" }, { text: "Project History", link: "/project-history" }] },
      { text: "Setup and Development", items: [{ text: "Dependency Graph", link: "/dependency-graph" }, { text: "Branching Strategy", link: "/branching-strategy-and-convention" }, { text: "Commit Message Convention", link: "/commit-message-convention" }, { text: "Getting Started", link: "/getting-started" }, { text: "Getting Started with GitHub Codespaces", link: "/getting-started-with-github-codespaces" }, { text: "Local Keycloak Setup", link: "/local-keycloak-setup" }, { text: "Running Cronjobs Locally", link: "/running-cronjobs-locally" }, { text: "Code Formatting", link: "/code-formatting" }, { text: "Local Database and Cache Operations", link: "/local-database-and-cache-operations" }, { text: "Dependency Upgrade Check", link: "/dependency-upgrade-check" }, { text: "PHP Base Images", link: "/php-base-images" }] },
      { text: "Testing and Automation", items: [{ text: "Unit Testing in ZMS", link: "/testing-unit" }, { text: "Unit Test Coverage", link: "/testing-coverage" }, { text: "zmsautomation Documentation", link: "/zmsautomation" }, { text: "Current Cucumber Tests", link: "/zmsautomation-cucumber-current" }] },
      { text: "Operations", items: [{ text: "API reference", link: "/api-reference" }, { text: "Operations", link: "/operations" }, { text: "DLDB Interface Documentation", link: "/dldb-interface-documentation" }] },
      { text: "Reference", items: [{ text: "Module READMEs", link: "/module-readmes" }] }
    ]
  }
};
