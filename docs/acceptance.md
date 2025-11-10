# Acceptance Checklist

- [ ] `/okaz`, `/arabnews`, `/ring` redirect with HTTP 302 to correct URLs based on patterns.
- [ ] Admin login enforces password length and CSRF protection.
- [ ] Viewer accounts cannot access edit form (403).
- [ ] Admin can update sequence provider current issue; change is reflected immediately in redirect and logged in `provider_changes`.
- [ ] Cron job runs at 00:05 KSA and records entries in `cron_runs` with accurate counts.
- [ ] Gaps > 24h in cron runs show warning banner in `/admin/cron-history`.
- [ ] Logs written to `logs/app.log` and `logs/cron.log` with rotation enabled.
- [ ] HSTS header present in responses.
- [ ] Unit tests (`vendor/bin/phpunit`) pass.
- [ ] phpstan (level 5) and phpcs (PSR-12) pass locally.
- [ ] README instructions followable on fresh Ubuntu 22.04 droplet.
