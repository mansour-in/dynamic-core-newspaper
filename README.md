# CORE Newspaper Redirector (Phase 1)

A minimal PHP 8.2 application that exposes stable provider endpoints such as `/okaz` and `/arabnews`. Each provider immediately 302-redirects to its most recent issue URL according to simple pattern rules (date, numeric sequence, or monthly slug). Configuration lives entirely in flat files under `config/`.

## Project Structure
```
config/
  app.php          # Environment + logging configuration
  providers.php    # Provider definitions (slug, template, etc.)
logs/
  app.log          # Optional redirect log (if writable)
public/
  index.php        # Front controller and router
  .htaccess        # Routes all requests to index.php (except health/version)
src/
  Redirector.php   # Computes target URL for a provider
  Responder.php    # Small response helpers (302/404/500)
```

## Requirements
- PHP 8.2 with `mbstring`, `curl`, `json`, `xml`, and `zip` extensions.
- Apache 2.4 with `mod_rewrite` enabled.
- System timezone set to `Asia/Riyadh` (also enforced in PHP at runtime).
- `logs/` directory writable by the Apache user if redirect logging is desired.

## Installation
1. Copy the repository to `/var/www/dynamic-core-newspaper` (or preferred path).
2. Ensure `logs/` exists and Apache can write to it:
   ```bash
   mkdir -p /var/www/dynamic-core-newspaper/logs
   chown -R www-data:www-data /var/www/dynamic-core-newspaper
   chmod -R 750 /var/www/dynamic-core-newspaper
   ```
3. Configure Apache to point the vhost document root to `/var/www/dynamic-core-newspaper/public` and allow overrides.
4. Adjust `config/providers.php` to match your providers. Supported `pattern_type` values:
   - `date`: Uses `{YYYY}`, `{MM}`, `{DD}` (KSA timezone).
   - `sequence`: Uses `{ISSUE_ID}` from `current_issue`.
   - `monthly`: Uses `{MM_slug}` (english month in lowercase) and `{YYYY}`.
5. (Optional) Update `config/app.php` to change the version or disable logging by setting `LOG_PATH` to `null`.

## Health & Smoke Tests
With Apache running:
```bash
curl -i http://newspaper.core.fit/health      # should return 200 OK
curl -i http://newspaper.core.fit/version     # prints version string
curl -i http://newspaper.core.fit/okaz        # 302 with YYYY/MM/DD in Location
curl -i http://newspaper.core.fit/arabnews    # 302 with ISSUE_ID from config
curl -i http://newspaper.core.fit/ring        # 302 with {mm_slug}_{YYYY}
```
Unknown or inactive providers respond with HTTP 404. Configuration errors return HTTP 500 with a short HTML body. Access logs are handled by Apache; redirect summaries are appended to `logs/app.log` if the path is writable.
