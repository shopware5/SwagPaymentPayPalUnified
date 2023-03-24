module.exports = {
    root: true,
    parserOptions: {
        "ecmaVersion": "latest",
        "sourceType": "module"
    },

    rules: {
        'no-var': 2,
        'node/no-callback-literal': 'off',
        'multiline-ternary': 'off',
        'arrow-parens': 2,
        'space-before-function-paren': 0,
        'keyword-spacing': [
            'warn'
        ],
        'space-in-parens': [
            'warn'
        ],
        'generator-star-spacing': 1,
        'no-shadow-restricted-names': 0,
        eqeqeq: 0,
        'no-debugger': 0,
        'one-var': 0,
        semi: [
            'error',
            'always'
        ],
        indent: [
            'error',
            4,
            { SwitchCase: 1 }
        ],
    }
};
