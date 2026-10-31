<h1 align="center">Infinity</h1>

<div align="center">
<a href="https://github.com/infiniteuny/infinity-backend/releases/latest"><img src="https://img.shields.io/github/v/release/infiniteuny/infinity-backend?style=for-the-badge&labelColor=000000" alt="Latest Version"></a>
<a href="https://github.com/infiniteuny/infinity-backend#GPL-3.0-1-ov-file"><img src="https://img.shields.io/github/license/infiniteuny/infinity-backend?style=for-the-badge&labelColor=000000" alt="License"></a>
</div>

The backend API for the INFINITE organization management system, built with [Laravel](https://laravel.com/).

## Getting Started

You will need PHP 8.3 or later, [Composer](https://getcomposer.org/), and Git.

1. Install dependencies, then create your local environment file and SQLite database:

   ```sh
   composer install
   cp .env.example .env
   touch database/database.sqlite
   ```

2. Generate an application key and apply database migrations:

   ```sh
   php artisan key:generate
   php artisan migrate
   ```

3. Configure any required integrations in `.env` (for example, authentication provider credentials), then start the application as described in [Development](#development).

## Project Structure

```text
.
├── app/          # API controllers, requests, resources, models, services, and other application code
├── bootstrap/    # Application bootstrapping
├── config/       # Application and integration configuration
├── database/     # Migrations, factories, and seeders
├── deployment/   # Container runtime, Caddy, and Supervisor configuration
├── public/       # Web server document root
├── resources/    # Frontend assets and views
├── routes/       # API and console route definitions
└── tests/        # Unit, feature, and load tests
```

### Learn More

- [Laravel Learn](https://laravel.com/learn/) — a guided introduction to Laravel and PHP.
- [Laravel documentation](https://laravel.com/framework/docs) — framework features and APIs.
- [Laravel deployment documentation](https://laravel.com/framework/docs/deployment) — production configuration guidance.

## Development

Start the Laravel API server:

```sh
php artisan serve
```

Run the test suite with:

```sh
php artisan test
```

## Deployment

The repository includes a `Dockerfile` for building the production image and runtime configuration in `deployment/`.

```sh
docker build -t infinity-backend .
```

Configure production environment variables and infrastructure (including the database and any required integrations) in your deployment platform. Keep secrets out of the image, run database migrations as part of deployment, and ensure Laravel's storage and cache directories are writable. See the [Laravel deployment guide](https://laravel.com/framework/docs/deployment) for production recommendations.

## Code of Conduct

This project follows INFINITE's [Contributor Covenant Code of Conduct](https://github.com/infiniteuny/infinity-backend#coc-ov-file). Report abusive, harassing, or otherwise unacceptable behavior to [community@infiniteuny.id](mailto:community@infiniteuny.id). Reports will be reviewed promptly and handled with respect for the reporter's privacy.

## Security

Do not report vulnerabilities in a public issue. Submit them privately using GitHub's [Security Advisories](https://github.com/infiniteuny/infinity-backend/security/advisories/new) feature.

## License

This project is licensed under the [GNU General Public License v3.0 or later](https://www.gnu.org/licenses/gpl-3.0.html). See [LICENSE](https://github.com/infiniteuny/infinity-backend#GPL-3.0-1-ov-file) for details.
