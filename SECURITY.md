# Security and deployment

## Production environment

Set these variables in the web-server/service environment, not in a public PHP file:

```text
APP_ENV=production
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=spa_management
DB_USER=<dedicated application user>
DB_PASSWORD=<strong unique secret>
APP_BACKUP_DIR=<private directory outside the web root>
```

Production requests are rejected unless PHP detects HTTPS. Configure TLS at the web server or trusted reverse proxy and make sure PHP receives the server's real HTTPS state; do not trust arbitrary client-supplied forwarding headers. Keep the database bound to a private interface and grant the application account only the SQL privileges it needs. Never use MySQL's `root` account for the application.

The local WAMP fallback uses `root` with an empty password only when `APP_ENV` is `local`. Do not expose that development database or configuration to a network.

Apache 2.4 `.htaccess` rules deny direct access to application/configuration folders and prevent uploaded scripts from being served. If `AllowOverride` is disabled or production uses IIS/Nginx, enforce equivalent web-server rules there. Keep `public/uploads` writable only by the application service account.

## Existing data and schema

`app/database/spa_management.sql` is a clean-install script and contains `DROP TABLE` statements. **Do not rerun it against an existing installation.** Apply only reviewed additive migrations to an existing database and take a backup first. Store schema changes in `app/database/migrations`.

## Sessions, authentication, and requests

The app uses PHP password hashes, strict cookie-only sessions, session ID rotation after login, idle and absolute session expiry, login-attempt throttling, role authorization, and a session-bound CSRF token for POST requests. Passwords created or changed in the app must meet its 16-character policy. Responses include anti-framing, MIME-sniffing, referrer, and cache-control protections; production responses also use HSTS.

All data-changing forms must include a CSRF token. Continue using `e()` for HTML output and PDO prepared statements for SQL values. Do not put secrets, passwords, card data, or full customer records in audit payloads.

## Uploads, audit trail, and backups

Product/service uploads are restricted to verified raster images, randomized filenames, 5 MB, and bounded image dimensions. Upload directories deny script execution and direct access to application/configuration files.

Administrative changes, POS checkouts, and backup activity are written to `audit_logs`; login attempts are recorded in `login_attempts`. Administrators can review recent events at `?route=audit-logs`.

Administrators can create and download database backups at `?route=backup`. By default, backup files are stored in a private directory outside the web root; set `APP_BACKUP_DIR` to a protected location on production. Restrict its filesystem permissions and downloaded copies because backups contain account and customer data. Backups are manual, so schedule and monitor off-site encrypted copies separately and test restores in a non-production environment. The generated SQL contains table drops for restore workflows; inspect the target database before restoring.

## Payments

The current POS records a payment method and amount in the spa database; it does not charge cards or connect to a payment processor. Checkout recalculates totals from the server-side catalog and records invoice, stock, payment, and audit changes in a database transaction. Do not collect or store card numbers/CVV in this app. Before connecting a gateway, choose a provider and implement its hosted/tokenized flow, verified webhooks, idempotency, and reconciliation; gateway-specific security cannot be enabled without that provider.

## Verification

Run PHP syntax checks after changes and validate production TLS, database permissions, upload denial, CSRF rejection, role restrictions, backup download authorization, and restore procedures in staging. This hardening is not a substitute for an independent penetration test or ongoing patching of PHP, MySQL, and the web server.
