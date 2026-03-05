# Contributing to lphenom/core

Thank you for considering contributing to **LPhenom Core**! 🎉

## Requirements

- PHP >= 8.1
- Composer

## Getting Started

1. **Fork** the repository on GitHub.
2. **Clone** your fork:
   ```bash
   git clone git@github.com:<your-username>/core.git
   cd core
   ```
3. **Install** dependencies:
   ```bash
   make install
   ```

## Development Workflow

- Create a **feature branch** from `main`:
  ```bash
  git checkout -b feat/my-feature
  ```
- Make your changes following the code style (see below).
- Run **linting** before committing:
  ```bash
  make lint-fix
  ```
- Run **static analysis**:
  ```bash
  make analyse
  ```
- Run **tests** and make sure everything passes:
  ```bash
  make test
  ```
- Commit using [Conventional Commits](https://www.conventionalcommits.org/):
  ```
  feat(core): add new feature
  fix(core): fix a bug
  test(core): add tests
  docs(core): update docs
  chore: update tooling
  ```
- Open a **Pull Request** against `main`.

## Code Style

We use [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) with PSR-12 + `declare(strict_types=1)`.

Run `make lint-fix` to auto-fix style issues.

## KPHP Compatibility Rules

This package must remain KPHP-compatible:

- ❌ No `Reflection` API
- ❌ No `eval()`
- ❌ No dynamic class loading (`new $className()`)
- ❌ No variable variables (`$$var`)
- ❌ No heavy `__get`/`__call` magic
- ✅ Explicit type declarations everywhere
- ✅ `declare(strict_types=1)` in every file

## Pull Request Guidelines

- Keep PRs small and focused.
- Add tests for new functionality.
- Update documentation if needed.
- CI must pass before merging.

