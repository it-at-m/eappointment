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
        "console": true,
        "window": true,
        "setTimeout": true,
        "clearTimeout": true,
        "document": true
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
