import DefaultTheme from "vitepress/theme";
import LhmThemeExtension from "./LhmThemeExtension.vue";
import "./style.css";

export default {
  ...DefaultTheme,
  Layout: LhmThemeExtension
};
