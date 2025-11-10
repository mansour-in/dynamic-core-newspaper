# Deployment Runbook

## Environment
- Ubuntu Server 22.04 LTS
- Apache HTTPD 2.4+
- PHP 8.2 with extensions: `pdo_mysql`, `mbstring`, `json`, `curl`, `openssl`
- MySQL 8.x

## Steps
1. **Create system user** (optional but recommended)
   ```bash
   sudo adduser --system --group core-news
   ```
2. **Clone repository**
   ```bash
   sudo mkdir -p /var/www/newspaper.core.fit
   sudo chown core-news:core-news /var/www/newspaper.core.fit
   sudo -u core-news git clone https://example.com/core/newspaper-redirector.git /var/www/newspaper.core.fit
   ```
3. **Install PHP dependencies**
   ```bash
   cd /var/www/newspaper.core.fit
   sudo -u core-news composer install --no-dev --optimize-autoloader
   ```
4. **Configure environment**
   ```bash
   sudo -u core-news cp .env.example .env
   sudo -u core-news nano .env
   ```
5. **Set permissions**
   ```bash
   sudo chown -R core-news:www-data logs public/assets
   sudo chmod -R 770 logs
   ```
6. **MySQL schema**
   ```bash
   mysql -u root -p < sql/001_init.sql
   mysql -u root -p < sql/010_seed_providers.sql
   ```
7. **Apache virtual host**: place configuration (see README) into `/etc/apache2/sites-available/newspaper.core.fit.conf` and enable it.
   ```bash
   sudo a2ensite newspaper.core.fit.conf
   sudo a2enmod rewrite headers ssl
   sudo systemctl reload apache2
   ```
8. **Timezone & locale**
   ```bash
   sudo timedatectl set-timezone Asia/Riyadh
   ```
9. **Log directories**: ensure `logs/app.log` and `logs/cron.log` are writable by Apache and cron user.
   ```bash
   sudo touch logs/app.log logs/cron.log
   sudo chown core-news:www-data logs/*.log
   sudo chmod 660 logs/*.log
   ```
10. **Smoke test**: open `https://newspaper.core.fit/admin/login` and validate redirect endpoints.

## Post-deploy checklist
- [ ] Admin account created (see `docs/admin.md`)
- [ ] HTTPS certificate installed (e.g., via Certbot)
- [ ] Cron job configured (`docs/cron.md`)
- [ ] Backups scheduled (`docs/backup.md`)
