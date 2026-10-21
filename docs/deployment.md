# Local Deployment

Requirements: PHP 8.3+, Composer, Node.js, and SQLite for local development. MySQL 8 is the production target.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed --no-interaction
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8011
```

Development users use the seeded `@example.test` accounts with password `DevOnly!2026`. Change these credentials before any shared environment.

Production must use a managed MySQL database, HTTPS, secure session/cookie settings, database queue/cache or Redis, scheduled queue workers, daily backups with restore drills, log redaction, and a separate secrets manager. Configure payment, messaging, GST statutory, and CCTV gateway adapters only after provider and legal verification.
