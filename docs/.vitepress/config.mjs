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
      { text: "Testing", link: "/testing" },
      { text: "GitHub Repository", link: "https://github.com/it-at-m/eappointment/" },
      { text: "Open Source", link: "https://opensource.muenchen.de/software/zeitmanagementsystem.html" }
    ],
    sidebar: [
      { text: "Overview", items: [{ text: "Introduction", link: "/" }, { text: "Project History", link: "/project-history" }] },
      { text: "Setup and Development", items: [{ text: "Getting Started", link: "/getting-started" }, { text: "Development", link: "/development" }] },
      { text: "Operations", items: [{ text: "Testing", link: "/testing" }, { text: "API reference", link: "/api-reference" }, { text: "Operations", link: "/operations" }] },
      { text: "Reference", items: [{ text: "Module READMEs", link: "/module-readmes" }] }
    ]
  }
};
