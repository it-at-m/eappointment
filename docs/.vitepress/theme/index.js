import DefaultTheme from "vitepress/theme";

import BerlinChangelogEmbed from "./BerlinChangelogEmbed.vue";
import ChangelogEmbed from "./ChangelogEmbed.vue";
import CucumberFeatureGroup from "./CucumberFeatureGroup.vue";
import CucumberFeatureRow from "./CucumberFeatureRow.vue";
import CucumberFeatureSearch from "./CucumberFeatureSearch.vue";
import DepartmentCodeExplorerTarget from "./DepartmentCodeExplorerTarget.vue";
import DepartmentCodeExplorerToday from "./DepartmentCodeExplorerToday.vue";
import LhmThemeExtension from "./LhmThemeExtension.vue";
import LogInventory from "./LogInventory.vue";
import ThinnedProcessCodeExplorerTarget from "./ThinnedProcessCodeExplorerTarget.vue";
import ThinnedProcessCodeExplorerToday from "./ThinnedProcessCodeExplorerToday.vue";

import "./style.css";

export default {
  ...DefaultTheme,
  Layout: LhmThemeExtension,
  enhanceApp(ctx) {
    if (typeof DefaultTheme.enhanceApp === "function") {
      DefaultTheme.enhanceApp(ctx);
    }
    ctx.app.component("BerlinChangelogEmbed", BerlinChangelogEmbed);
    ctx.app.component("ChangelogEmbed", ChangelogEmbed);
    ctx.app.component(
      "DepartmentCodeExplorerTarget",
      DepartmentCodeExplorerTarget
    );
    ctx.app.component(
      "DepartmentCodeExplorerToday",
      DepartmentCodeExplorerToday
    );
    ctx.app.component(
      "ThinnedProcessCodeExplorerTarget",
      ThinnedProcessCodeExplorerTarget
    );
    ctx.app.component(
      "ThinnedProcessCodeExplorerToday",
      ThinnedProcessCodeExplorerToday
    );
    ctx.app.component("LogInventory", LogInventory);
    ctx.app.component("CucumberFeatureGroup", CucumberFeatureGroup);
    ctx.app.component("CucumberFeatureRow", CucumberFeatureRow);
    ctx.app.component("CucumberFeatureSearch", CucumberFeatureSearch);
  },
};
