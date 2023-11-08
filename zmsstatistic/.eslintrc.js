module.exports = {
    "parserOptions": {
        "ecmaVersion": 6,
        "sourceType": "module",
        "ecmaFeatures": {
            "impliedStrict": true,
            "jsx": true
        }
    },
    "env": {
        "browser": true
    },
    "plugins": [],
    "globals": {
        "console": true,
        "document": true,
        "Promise": true
    },
    "extends": [
        "eslint:recommended",
        "plugin:import/warnings",
        "plugin:import/errors"
    ],
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
