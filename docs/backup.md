# Backup and Restore Runbook

## Scope
- MySQL database `core_newspaper`
- Log files `logs/app.log`, `logs/cron.log`
- Application source (Git)

## Backup Strategy
1. **Database**
   ```bash
   mysqldump -u core_news_user -p --single-transaction core_newspaper > /backups/core_newspaper_$(date +%F).sql
   ```
   - Store dumps on encrypted volume or object storage.
   - Retention: 30 days.
2. **Logs**
   ```bash
   tar czf /backups/core_newspaper_logs_$(date +%F).tar.gz logs/*.log
   ```
   - Rotate weekly; purge logs older than 14 days unless required for audit.
3. **Source code**
   - Repository should be backed up through remote Git origin.
   - Capture deployed commit hash in deployment records.

## Restore Procedure
1. **Application downtime**
   - Disable Apache site or put maintenance page.
2. **Database**
   ```bash
   mysql -u core_news_user -p core_newspaper < /backups/core_newspaper_YYYY-MM-DD.sql
   ```
3. **Logs** (if needed)
   ```bash
   tar xzf /backups/core_newspaper_logs_YYYY-MM-DD.tar.gz -C /var/www/newspaper.core.fit
   ```
4. **Source**
   - Checkout required commit/tag from Git.
   - Run `composer install --no-dev --optimize-autoloader`.
5. **Permissions**
   ```bash
   chown -R core-news:www-data logs
   chmod 660 logs/*.log
   ```
6. **Verification**
   - Run smoke tests (redirect endpoints and admin login).
   - Review `/admin/cron-history` for expected history.

## Rollback Guidance
- Keep previous deployment directory until new release is validated.
- To roll back, switch Apache DocumentRoot symlink to previous release and restore DB backup if schema changed.
