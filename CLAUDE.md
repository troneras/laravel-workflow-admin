# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

**Start development environment:**
```bash
composer run dev    # Runs Laravel server, queue worker, logs, and Vite dev server concurrently
```

**Alternative development commands:**
```bash
npm run dev         # Vite dev server only
php artisan serve   # Laravel server only
```

**Build and quality checks:**
```bash
npm run build       # Production build
npm run build:ssr   # Build with server-side rendering
npm run lint        # ESLint
npm run format      # Prettier formatting
composer run test   # Run Pest tests
```

## Architecture Overview

This is a **Laravel 12 application** with **Vue.js/TypeScript frontend** using **Inertia.js** for seamless SPA-like experience. Key architectural components:

- **Backend**: Laravel with Pest testing framework
- **Frontend**: Vue.js with TypeScript, built with Vite
- **Bridge**: Inertia.js for server-driven single page application
- **UI**: Shadcn-vue components with Tailwind CSS 4
- **Type Safety**: Laravel Wayfinder for type-safe route generation between Laravel and Vue

## Key Technologies

- **Laravel Wayfinder**: Generates TypeScript route definitions from Laravel routes
- **Inertia.js**: Configured with SSR support in `config/inertia.php`
- **Shadcn-vue**: Component library configured in `components.json`
- **Tailwind CSS 4**: Modern utility-first CSS framework
- **Pest**: Modern PHP testing framework (not PHPUnit)

## File Structure Patterns

- `app/Http/Controllers/` - Laravel controllers
- `resources/js/` - Vue.js components and TypeScript code
- `resources/js/Pages/` - Inertia page components
- `resources/js/Components/` - Reusable Vue components
- `routes/web.php` - Main application routes
- `tests/Feature/` - Integration tests
- `tests/Unit/` - Unit tests

## Testing

Uses **Pest** (not PHPUnit). Test configuration in `phpunit.xml` with SQLite in-memory database. Run tests with `composer run test` or `php artisan test`.

## Note on Dify Integration

Despite the project name, no Dify integration currently exists in the codebase. This appears to be a Laravel starter template that could serve as a foundation for future Dify integration.