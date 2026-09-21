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
                ...globals.browser,
                console: "readonly",
                document: "readonly",
                Promise: "readonly"
            }
        },
        rules: {
            complexity: [
                "error",
                11
            ],
            "no-console": [
                "off"
            ]
        }
    }
]);
