const { defineConfig } = require("eslint/config");
const js = require("@eslint/js");
const globals = require("globals");

const jsxUsesVars = {
    meta: {
        name: "jsx-uses-vars",
        version: "1.0.0"
    },
    rules: {
        "jsx-uses-vars": {
            create(context) {
                const sourceCode = context.sourceCode;

                function mark(node) {
                    if (node.type === "JSXIdentifier") {
                        sourceCode.markVariableAsUsed(node.name);
                    } else if (node.type === "JSXMemberExpression") {
                        mark(node.object);
                    }
                }

                return {
                    JSXOpeningElement(node) {
                        mark(node.name);
                    }
                };
            }
        }
    }
};

module.exports = defineConfig([
    js.configs.recommended,
    {
        plugins: {
            "jsx-uses-vars": jsxUsesVars
        },
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
            "jsx-uses-vars/jsx-uses-vars": "error",
            "no-unused-vars": [
                "error",
                {
                    varsIgnorePattern: "^React$"
                }
            ]
        }
    }
]);
