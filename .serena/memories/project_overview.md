# Project Overview

**Purpose:**
This project is a backend application named "INFINITE Dashboard", developed for "restek, uny, profile".

**Tech Stack:**
- **Language:** PHP 8.2+
- **Framework:** Laravel 12.x
- **Key Packages:**
  - `spatie/laravel-data` for data transfer objects
  - `spatie/laravel-permission` for role/permission management
  - `spatie/laravel-query-builder` for API queries
  - `laravel/octane` for high-performance execution
  - `laravel/horizon` for queue monitoring
  - `knuckleswtf/scribe` for API documentation generation
  - `facile-it/php-jose-verifier` and `facile-it/php-openid-client` for auth (using OpenID connect)

**Codebase Structure:**
Standard Laravel structure:
- `app/` - Contains core logic (Models, Controllers, Data transfer objects).
- `config/` - Configuration files.
- `routes/` - API and web routes.
- `database/` - Migrations, Factories, and Seeders.
- `tests/` - PHPUnit test suites.
