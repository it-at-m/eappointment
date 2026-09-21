const { defineConfig } = require("eslint/config");
const js = require("@eslint/js");
const globals = require("globals");

module.exports = defineConfig([
    js.configs.recommended,
    {
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            parserOptions: {
                ecmaFeatures: {
                    impliedStrict: true,
                    jsx: true
                }
            },
            globals: {
                ...globals.browser
            }
        },
        rules: {
            complexity: [
                "error",
                12
            ],
            "no-console": [
                "off"
            ],
            "no-unused-vars": [
                "error",
                {
                    varsIgnorePattern: "^React$"
                }
            ]
        }
    }
]);
