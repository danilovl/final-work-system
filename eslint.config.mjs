import pluginVue from 'eslint-plugin-vue'
import tsParser from '@typescript-eslint/parser'
import tsPlugin from '@typescript-eslint/eslint-plugin'

export default [
    {
        ignores: ['node_modules/**', 'public/**', 'vendor/**', 'var/**']
    },
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['assets/vue/**/*.ts', 'assets/vue/**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tsParser,
                ecmaVersion: 'latest',
                sourceType: 'module'
            }
        },
        plugins: {
            '@typescript-eslint': tsPlugin
        },
        rules: {
            ...tsPlugin.configs.recommended.rules
        }
    },
    {
        files: ['assets/vue/**/*.ts'],
        languageOptions: {
            parser: tsParser
        }
    }
]
