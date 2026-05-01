# Suggested Commands

As the system is Windows, use standard DOS/PowerShell commands where appropriate (e.g., `dir`, `cd`), or Git Bash if available. To run artisan or composer, use the standard `php` and `composer` commands.

**Development & Execution:**
- Start local server: `php artisan serve` or `php artisan octane:start` (since octane is present)
- Generate API Documentation: `php artisan scribe:generate`
- Run queue worker: `php artisan queue:work` or `php artisan horizon`
- Vite dev server (if doing blade/asset work): `npm run dev`

**Linting & Formatting:**
- Run Laravel Pint: `./vendor/bin/pint` (or `vendor\bin\pint` on Windows cmd)

**Testing:**
- Run PHPUnit tests: `php artisan test` or `./vendor/bin/phpunit`

**System Utils (Windows):**
- Navigation: `cd`, `dir`
- Search: Use agent's `grep` or `find_file` tools.
