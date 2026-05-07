# AGENTS.md

## Overview

ShortDescription is a MediaWiki extension (requires MW 1.43+) that adds a `{{SHORTDESC:...}}` magic word and exposes the stored description through hooks (`OutputPageParserOutput`, `BeforePageDisplay`, `InfoAction`, `SearchResultProvideDescription`) and an API module (`prop=description`). It can also feed OpenSearch suggestions and render the description as a site tagline. PHP for parsing/storage and the API, vanilla JS + LESS for the optional tagline rendering via ResourceLoader.

## Verification

Run only what's relevant to the files you changed.

| Files changed | Command |
| --- | --- |
| `*.php` | `composer preflight` (lint, style, and Phan) |
| `*.js` | `npm run lint:js` |
| `*.less`, `*.css` | `npm run lint:styles` |
| `i18n/` | `npm run lint:i18n` |

Auto-fix commands: `composer fix` (PHP), `npm run lint:fix:js` (JS), `npm run lint:fix:styles` (styles).

**Preflight**: Run `npm run preflight` to execute all Node-based lints in one command. Run `composer preflight` from within a MediaWiki installation to execute all PHP lints, style checks, and Phan static analysis.

**Always run the relevant checks before committing.** Read the full output — PHPCS warnings must be fixed, not just errors. The command exits 0 even with warnings, so do not treat exit code alone as a pass.

### Dev environment

This project's standard dev environment is the MediaWiki Docker setup defined in the parent `mediawiki/` directory. The user may be using a different environment. Ask the user for their dev environment URL and how to run commands if not already known.

To run composer commands in the standard Docker environment:

```sh
docker compose exec mediawiki bash -c "cd /var/www/html/w/extensions/ShortDescription && composer preflight"
```

### Phan

Phan requires a full MediaWiki installation at `../../` for type resolution.

```sh
docker compose exec mediawiki bash -c "cd /var/www/html/w/extensions/ShortDescription && composer phan"
```

## Coding conventions

### PHP

- New files start with `declare( strict_types=1 );`
- Use native PHP types (properties, parameters, return values); use PHPDoc only for collection types like `string[]`
- Always use MediaWiki-namespaced imports (`use MediaWiki\Title\Title;`), never legacy shims (`use Title;`)
- Hook handlers live under `includes/Hooks/` and are wired through `HookHandlers` in `extension.json`

### JavaScript

- CommonJS modules: `require()` for imports, `module.exports` for exports
- Client code lives in `modules/`

### LESS/CSS

- Styles live in `modules/`
- Class names follow the `ext-shortdesc` prefix pattern (enforced by `.stylelintrc.json`)

### extension.json

`extension.json` is the source of truth for how the extension is wired — ResourceLoader modules, hooks, config variables, API modules, and dependencies are all declared here.

- When adding or removing files under `modules/`, update the corresponding `scripts` or `styles` list in `ResourceModules`
- Config variables are declared under `config` in `extension.json` (prefixed `wgShortDescription`)
- New hooks must be registered under both `Hooks` and `HookHandlers`

### Commits

- Use [Conventional Commits](https://www.conventionalcommits.org/) (e.g. `fix:`, `feat:`, `refactor:`)
- Use `ci:` or `chore:` for non-user-facing changes (tooling, config, dependencies)

### i18n

- Any user-facing string needs a message key in `i18n/en.json` (or `i18n/api/en.json` for API-only messages)
- Every key in `en.json` must also have a documentation entry in the matching `qqq.json`
