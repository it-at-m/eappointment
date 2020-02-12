module.exports = {
    "parserOptions": {
        "ecmaVersion": 6,
        "sourceType": "module",
        "ecmaFeatures": {
            "impliedStrict": true,
            "jsx": true
        }
    },
    "plugins": [],
    "globals": {
        "window": true,
        "console": true,
        "setTimeout": true,
        "clearTimeout": true,
        "setInterval": true,
        "document": true,
        "Promise": true
    },
    "extends": ["eslint:recommended"],
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
