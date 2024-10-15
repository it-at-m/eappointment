module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/vue3-essential',
    'eslint:recommended',
    '@vue/eslint-config-typescript',
  ],
  rules: {
    'vue/multi-word-component-names': 'off',
  },
  parserOptions: {
    parser: "@typescript-eslint/parser",
    ecmaFeatures : {
      jsx : false
    }
  },
  ignorePatterns: ["/**/src/js/*.js"]
}
