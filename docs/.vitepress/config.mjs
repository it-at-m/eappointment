export default {
  title: "eAppointment Docs",
  description: "Technical documentation for it-at-m/eappointment",
  base: "/eappointment/",
  themeConfig: {
    nav: [
      { text: "Overview", link: "/" },
      { text: "API reference", link: "/api-reference" },
      { text: "Testing", link: "/testing" },
      { text: "GitHub Wiki", link: "https://github.com/it-at-m/eappointment/wiki" }
    ],
    sidebar: [
      { text: "Overview", items: [{ text: "Introduction", link: "/" }, { text: "Project Overview", link: "/overview" }] },
      { text: "Setup and Development", items: [{ text: "Getting Started", link: "/getting-started" }, { text: "Development", link: "/development" }] },
      { text: "Operations", items: [{ text: "Testing", link: "/testing" }, { text: "API reference", link: "/api-reference" }, { text: "Operations", link: "/operations" }] },
      { text: "Reference", items: [{ text: "Module READMEs", link: "/module-readmes" }, { text: "Wiki Sync", link: "/wiki-sync" }] }
    ]
  }
};
