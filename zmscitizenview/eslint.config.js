import js from "@eslint/js";
import vuePrettierEslintConfigSkipFormatting from "@vue/eslint-config-prettier/skip-formatting";
import vueTsEslintConfig from "@vue/eslint-config-typescript";
import { ESLint } from "eslint";
import vueEslintConfig from "eslint-plugin-vue";

export default [
  ...ESLint.defaultConfig,
  js.configs.recommended,
  ...vueEslintConfig.configs["flat/recommended"],
  ...vueTsEslintConfig({
    extends: ["strict", "stylistic"],
  }),
  vuePrettierEslintConfigSkipFormatting,
  {
    ignores: ["dist", "target", "node_modules", "env.d.ts"],
  },
  {
    rules: {
      "no-console": ["error", { allow: ["debug"] }],
      "vue/component-name-in-template-casing": [
        "error",
        "kebab-case",
        { registeredComponentsOnly: false },
      ],
      "@typescript-eslint/no-explicit-any": "off",
    },
  },
];
