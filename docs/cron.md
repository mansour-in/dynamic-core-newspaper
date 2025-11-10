# Cron Operations Runbook

## Schedule
- Run daily at **00:05 Asia/Riyadh**.
- System timezone must be set to `Asia/Riyadh`.

## Installation
1. Ensure PHP CLI binary path (e.g., `/usr/bin/php`).
2. Edit the cron table for the deployment user (e.g., `core-news`).
   ```bash
   sudo -u core-news crontab -e
   ```
3. Add the job:
   ```cron
   5 0 * * * /usr/bin/php /var/www/newspaper.core.fit/scripts/run_cron.php >> /var/www/newspaper.core.fit/logs/cron.log 2>&1
   ```
4. Save and exit.

## Validation
- Run manually: `sudo -u core-news php scripts/run_cron.php`.
- Confirm a new row exists in `cron_runs` table and `logs/cron.log` contains an entry.
- Review `/admin/cron-history` to ensure status is `success` and gap indicator is clear.

## Troubleshooting
- Check PHP errors in `logs/app.log`.
- Confirm database connectivity.
- Verify `.env` configuration for credentials.
- For sequence providers, confirm `SEQUENCE_STRATEGY` and `PROBE_ENABLED` flags.
- If cron fails repeatedly, set provider `is_active=0` to stop processing until resolved.
