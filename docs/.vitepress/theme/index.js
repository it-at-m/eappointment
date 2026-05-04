import DefaultTheme from "vitepress/theme";
import ChangelogEmbed from "./ChangelogEmbed.vue";
import LhmThemeExtension from "./LhmThemeExtension.vue";
import "./style.css";

export default {
  ...DefaultTheme,
  Layout: LhmThemeExtension,
  enhanceApp(ctx) {
    if (typeof DefaultTheme.enhanceApp === "function") {
      DefaultTheme.enhanceApp(ctx);
    }
    ctx.app.component("ChangelogEmbed", ChangelogEmbed);
  }
};
