module.exports = {
    "parser": "babel-eslint",
    "parserOptions": {
        "ecmaVersion": 6,
        "sourceType": "module",
        "ecmaFeatures": {
            "impliedStrict": true,
            "jsx": true
        }
    },
    "plugins": ["react"],
    "globals": {
        "console": true,
        "setTimeout": true,
        "clearTimeout": true,
        "document": true,
        "Promise": true
    },
    "extends": ["eslint:recommended", "plugin:react/recommended"],
    "rules": {
        "complexity": [
            "error",
            11
        ],
        "no-console": [
            "off"
        ]
    }
}
