// eslint.config.mjs
import { dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { FlatCompat } from '@eslint/eslintrc';
import js from '@eslint/js';
import prettierConfig from 'eslint-config-prettier';
import globals from 'globals';
import tseslint from 'typescript-eslint';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const [
  importPlugin,
  simpleImportSort,
  boundariesPlugin,
  reactPlugin,
  reactHooksPlugin,
  jsxA11yPlugin,
  unicornPlugin,
  perfectionistPlugin,
  validateFilenamePlugin,
  nodePlugin,
] = await Promise.all([
  import('eslint-plugin-import'),
  import('eslint-plugin-simple-import-sort'),
  import('eslint-plugin-boundaries'),
  import('eslint-plugin-react'),
  import('eslint-plugin-react-hooks'),
  import('eslint-plugin-jsx-a11y'),
  import('eslint-plugin-unicorn'),
  import('eslint-plugin-perfectionist'),
  import('eslint-plugin-validate-filename'),
  import('eslint-plugin-n'),
]);

const compat = new FlatCompat({
  baseDirectory: __dirname,
});

const eslintConfig = [
  // ───── Base JS Configs ─────
  js.configs.recommended,
  ...compat.extends('next/core-web-vitals', 'next/typescript'),

  // ───── Global Ignores ─────
  {
    ignores: [
      '**/node_modules/**',
      '**/.next/**',
      '**/dist/**',
      'public/**',
      'scripts/**',
      'postcss.config.mjs',
      'tailwind.config.mjs',
      'next.config.mjs',
      'next-env.d.ts',
    ],
  },

  // ───── Non-TypeScript Files (no type checking) ─────
  {
    files: ['**/*.js', '**/*.mjs', '**/*.cjs'],
    languageOptions: {
      globals: { ...globals.node },
    },
    rules: {
      'no-console': ['error', { allow: ['warn', 'error'] }],
      'no-debugger': 'error',
      'prefer-const': 'error',
    },
  },

  // ───── TypeScript + TSX (with type checking) ─────
  {
    files: ['**/*.ts', '**/*.tsx'],
    languageOptions: {
      parser: tseslint.parser,
      parserOptions: {
        project: './tsconfig.json',
        tsconfigRootDir: __dirname,
      },
      globals: { ...globals.browser, ...globals.node },
    },
    plugins: {
      '@typescript-eslint': tseslint.plugin,
      import: importPlugin.default,
      'simple-import-sort': simpleImportSort.default,
      boundaries: boundariesPlugin.default,
      react: reactPlugin.default,
      'react-hooks': reactHooksPlugin.default,
      'jsx-a11y': jsxA11yPlugin.default,
      unicorn: unicornPlugin.default,
      perfectionist: perfectionistPlugin.default,
      'validate-filename': validateFilenamePlugin.default,
      n: nodePlugin.default,
    },

    settings: {
      'boundaries/aliases': {
        '@app': './src/app',
        '@components': './src/components',
        '@lib': './src/lib',
        '@modules': './src/modules',
        '@types': './src/types',
        '@hooks': './src/hooks',
      },
      'boundaries/elements': [
        { type: 'app', pattern: 'src/app/**/*' },
        { type: 'components', pattern: 'src/components/**/*' },
        { type: 'lib', pattern: 'src/lib/**/*' },
        { type: 'types', pattern: 'src/types/**/*' },
        { type: 'hooks', pattern: 'src/hooks/**/*' },
        { type: 'presentation', pattern: 'src/modules/*/presentation/**/*' },
        { type: 'application', pattern: 'src/modules/*/application/**/*' },
        { type: 'domain', pattern: 'src/modules/*/domain/**/*' },
        { type: 'infrastructure', pattern: 'src/modules/*/infrastructure/**/*' },
      ],
    },

    rules: {
      /* ───── TypeScript ───── */
      '@typescript-eslint/no-explicit-any': 'off',
      '@typescript-eslint/no-unsafe-assignment': 'off',
      '@typescript-eslint/no-unsafe-member-access': 'off',
      '@typescript-eslint/no-unsafe-return': 'off',
      '@typescript-eslint/no-unsafe-argument': 'off',
      '@typescript-eslint/restrict-template-expressions': 'off',
      '@typescript-eslint/no-floating-promises': 'off',
      '@typescript-eslint/no-unnecessary-condition': 'off',
      '@typescript-eslint/no-unused-expressions': 'off',
      '@typescript-eslint/no-misused-promises': 'off',
      '@typescript-eslint/no-unused-vars': 'warn',
      '@typescript-eslint/explicit-function-return-type': [
        'warn',
        { allowExpressions: true, allowTypedFunctionExpressions: true },
      ],

      /* ───── General JS ───── */
      'unicorn/prevent-abbreviations': 'off',
      'no-console': ['error', { allow: ['warn', 'error'] }],
      'no-debugger': 'error',
      'prefer-const': 'error',

      /* ───── React & Hooks ───── */
      'react/function-component-definition': [
        'error',
        { namedComponents: 'arrow-function', unnamedComponents: 'arrow-function' },
      ],
      'react/jsx-uses-react': 'off',
      'react/react-in-jsx-scope': 'off',
      'react-hooks/rules-of-hooks': 'error',
      'react-hooks/exhaustive-deps': 'warn',

      /* ───── Accessibility ───── */
      'jsx-a11y/alt-text': 'error',
      'jsx-a11y/anchor-is-valid': 'warn',

      /* ───── Import Sorting – OFF (Prettier handles it) ───── */
      'simple-import-sort/imports': 'off',
      'simple-import-sort/exports': 'off',
      'import/order': 'off',
      'import/newline-after-import': 'off',

      /* ───── Statement Padding ───── */
      'padding-line-between-statements': [
        'error',
        { blankLine: 'always', prev: '*', next: 'function' },
        { blankLine: 'always', prev: '*', next: 'export' },
        { blankLine: 'always', prev: 'block-like', next: 'export' },
        { blankLine: 'always', prev: 'export', next: 'export' },
      ],

      /* ───── DDD Boundaries ───── */
      'boundaries/element-types': [
        'error',
        {
          default: 'disallow',
          rules: [
            {
              from: ['presentation'],
              allow: [
                'application',
                'domain',
                'components',
                'lib',
                'types',
                'hooks',
                'infrastructure',
                'presentation',
              ],
            },
            { from: ['application'], allow: ['domain', 'infrastructure', 'lib', 'types'] },
            { from: ['domain'], allow: ['lib', 'types', 'domain'] },
            { from: ['infrastructure'], allow: ['lib', 'types', 'domain', 'infrastructure'] },
            {
              from: ['app'],
              allow: [
                'presentation',
                'application',
                'domain',
                'infrastructure',
                'components',
                'lib',
                'types',
                'hooks',
              ],
            },
            { from: ['components'], allow: ['lib', 'types', 'components', 'hooks'] },
            { from: ['hooks'], allow: ['lib', 'types'] },
          ],
        },
      ],

      /* ───── File Naming ───── */
      'unicorn/filename-case': [
        'error',
        { cases: { kebabCase: true, camelCase: true, pascalCase: true } },
      ],
      'validate-filename/naming-rules': [
        'error',
        {
          rules: [
            { case: 'kebab', target: 'src/app/**', patterns: '*.tsx' },
            { case: 'kebab', target: 'src/app/**', patterns: '*.css' },
            { case: 'kebab', target: 'src/components/**', patterns: '*.tsx' },
            { case: 'kebab', target: 'src/modules/*/presentation/components/**', patterns: '*.tsx' },
            { case: 'camel', target: 'src/modules/*/presentation/hooks/**', patterns: '^use.*.ts' },
            { case: 'camel', target: 'src/hooks/**', patterns: '^use.*.ts' },
            { case: 'kebab', target: 'src/modules/*/application/use-cases/**', patterns: '*.ts' },
            { case: 'kebab', target: 'src/modules/*/infrastructure/repositories/**', patterns: '*.ts' },
            { case: 'kebab', target: 'src/modules/*/application/stores/**', patterns: '*.ts' },
            { case: 'kebab', target: 'src/modules/*/ui/application/stores/**', patterns: '*.ts' },
            { case: 'kebab', target: 'src/lib/**', patterns: '*.ts' },
            { case: 'kebab', target: 'src/modules/*/presentation/config/**', patterns: '*.ts' },
            { case: 'pascal', target: 'src/modules/*/domain/entities/**', patterns: '*.ts' },
            { case: 'pascal', target: 'src/modules/*/domain/interfaces/**', patterns: '*.ts' },
            { case: 'pascal', target: 'src/types/**', patterns: '*.ts' },
            { case: 'kebab', target: '**/*.test.tsx', patterns: '*.tsx' },
            { case: 'kebab', target: '**/*.spec.tsx', patterns: '*.tsx' },
          ],
        },
      ],

      /* ───── Object Sorting ───── */
      'perfectionist/sort-objects': ['warn', { type: 'natural' }],

      /* ───── Node.js: Force node: prefix ───── */
      'n/prefer-node-protocol': 'error',
    },
  },

  // ───── TSX-specific Overrides ─────
  {
    files: ['**/*.tsx'],
    rules: {
      'react/jsx-filename-extension': [1, { extensions: ['.tsx'] }],
    },
  },

  // ───── Prettier must be LAST ─────
  prettierConfig,
];

export default eslintConfig;