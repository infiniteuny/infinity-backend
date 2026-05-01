# Task Completion Steps

When completing a task, you should ensure the following is done:
1. Run formatting using `./vendor/bin/pint` (or `vendor\bin\pint.bat` depending on terminal).
2. Run tests to ensure no breakages: `php artisan test`.
3. If new API endpoints or request/response structures were added, update or regenerate documentation using `php artisan scribe:generate`.
4. Ensure no unresolved issues or debugging statements are left.
