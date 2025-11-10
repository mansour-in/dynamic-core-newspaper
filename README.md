# CORE Newspaper QR Redirector

A lightweight PHP 8.2 application that manages permanent redirect endpoints for newspaper QR codes and provides an admin interface to maintain issue URLs. Built for LAMP deployments on Ubuntu 22.04 with Apache 2.4, MySQL 8, and PHP 8.2+.

## Features
- Pattern-based redirect engine (daily date, numeric sequence, monthly slug).
- Secure admin panel with role-based access, CSRF protection, and login throttling.
- Provider audit log and cron execution history.
- Daily cron job automation with append-only logging and optional URL probing.
- Comprehensive operational runbooks and automated tests.

## Prerequisites
- Ubuntu 22.04 LTS or compatible Linux.
- Apache HTTPD 2.4 with `mod_rewrite` and `mod_headers`.
- PHP 8.2 with extensions: `pdo_mysql`, `mbstring`, `json`, `openssl`, `ctype`.
- MySQL 8.x server.
- Composer 2.x.

## Installation
1. Clone the repository:
   ```bash
   git clone https://example.com/core/newspaper-redirector.git
   cd newspaper-redirector
   ```
2. Install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Copy environment file and configure values:
   ```bash
   cp .env.example .env
   nano .env
   ```
4. Initialize database schema and seed providers:
   ```bash
   mysql -u root -p < sql/001_init.sql
   mysql -u root -p < sql/010_seed_providers.sql
   ```
5. Provision an admin account (`docs/admin.md`).
6. Ensure `logs/` directory is writable by the web server user.

## Apache Virtual Host Example
```
<VirtualHost *:80>
    ServerName newspaper.core.fit
    Redirect permanent / https://newspaper.core.fit/
</VirtualHost>

<VirtualHost *:443>
    ServerName newspaper.core.fit
    DocumentRoot /var/www/newspaper.core.fit/public

    <Directory /var/www/newspaper.core.fit/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/newspaper-error.log
    CustomLog ${APACHE_LOG_DIR}/newspaper-access.log combined

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/newspaper.core.fit/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/newspaper.core.fit/privkey.pem
</VirtualHost>
```

## Cron Setup
See `docs/cron.md` for scheduling details. The crontab entry should be:
```
5 0 * * * /usr/bin/php /var/www/newspaper.core.fit/scripts/run_cron.php >> /var/www/newspaper.core.fit/logs/cron.log 2>&1
```

## Environment Variables
See `.env.example` for documented configuration keys:
- Database credentials (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- Session settings (`SESSION_NAME`, `SESSION_SAMESITE`).
- Feature flags (`SEQUENCE_STRATEGY`, `PROBE_ENABLED`).
- Log file paths (`LOG_PATH`, `CRON_LOG_PATH`).

## Smoke Tests
After deployment:
1. Visit `/admin/login`, authenticate with admin credentials.
2. Confirm `/admin/providers` lists all providers.
3. Update the sequence provider issue ID and verify redirect changes immediately.
4. Hit `/okaz`, `/arabnews`, `/ring` and confirm 302 responses to computed URLs.
5. Run `php scripts/run_cron.php` and verify a new cron history entry appears.

## Development
- Run unit tests: `vendor/bin/phpunit`
- Coding standards: `vendor/bin/phpcs --standard=PSR12 app`
- Static analysis: `vendor/bin/phpstan analyse app --level=5`

## Troubleshooting
- Enable `APP_DEBUG=true` in `.env` for verbose logging (never in production).
- Check `logs/app.log` and `logs/cron.log` for errors.
- Ensure database user has only necessary privileges (SELECT/INSERT/UPDATE/DELETE on application tables).
- Verify system timezone matches `APP_TIMEZONE`.

## Documentation
Operational guides are located in the `docs/` directory:
- `deploy.md`
- `cron.md`
- `backup.md`
- `admin.md`
- `acceptance.md`
- `changelog.md`
