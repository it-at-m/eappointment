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
    "plugins": [],
    "globals": {
        "console": true,
        "setTimeout": true,
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
