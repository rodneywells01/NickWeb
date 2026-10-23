# Real application testing with synthetic data

This setup runs the actual PHP pages, sessions, authentication and SQL handlers.
The schema in `tests/schema.sql` was reconstructed from source queries; it is not a
production export and does not establish the live server's exact types/indexes.
All people, events, contact addresses, embeds and credentials are synthetic.

## Windows portable setup

Install no service and change no system database. Obtain PHP 8.5.12 NTS x64 (or a
later supported patched release) from [PHP's official downloads](https://www.php.net/downloads.php?os=windows)
and MariaDB 11.4.13 x64 ZIP from the [official archive](https://archive.mariadb.org/mariadb-11.4.13/winx64-packages/).
Check each archive against the vendor SHA-256 manifest before extracting it.
Python 3 is also required. In PowerShell, from the repository root:

```powershell
$env:PHP_BINARY = 'C:\path\to\php\php.exe'
$env:MARIADB_BIN = 'C:\path\to\mariadb\bin'
python tests/e2e.py
```

The runner initializes a new MariaDB datadir in a temporary directory, binds it
only to `127.0.0.1` on a dynamically chosen port, creates `nickweb_test` and an
application user limited to that database, and seeds synthetic fixtures. It
starts PHP on another dynamic loopback port. All owned processes stop and their
temporary data is removed on completion, including test failures.

For manual inspection instead, run `python tests/local_site.py`. It prints the
loopback URL. Log in with **fixture-admin / local-fixture-password**. Ctrl+C stops
and deletes that disposable environment. These credentials are test fixtures,
not secrets or credentials for any deployed environment.

## Isolation controls

- The original `includes/connection.php` is never executed or copied into the
  temporary website. A fixture-only replacement connects to literal `127.0.0.1`,
  database `nickweb_test`, using only the limited synthetic user and a validated
  non-default port. There is no remote-host option or production-config fallback.
- Client files containing messaging/OAuth configuration are excluded. The router
  replaces `scripts/email.js` with a disabled-message stub, blocks the old mail
  endpoint and internal helper paths, and sends a Content Security Policy that
  blocks external connections, fonts, styles, scripts and embeds.
- PHP URL wrappers, mail and common outbound socket/command APIs are disabled.
  Only mysqli's fixed loopback connection is used. Session files stay in the
  temporary environment; no production records or schema are downloaded.
- The test client checks a fixture-only response header. It takes its base URL
  only from the runner's own loopback server; it accepts no production URL flag.
- Temporary copies and logs are disposable and should not be deployed. Only the
  existing application files and explicitly scoped security fixes belong in
  production. Neither the synthetic router nor its relaxed inline-script CSP is
  a production server configuration.

## Coverage and limits

`tests/e2e.py` tests public page content, invalid/valid login, session persistence,
logout, home and biography/services/experience editing, event add/edit/delete,
composition/video add/move/delete, and contact editing. It checks SQL snapshots
and public reads after mutations. Anonymous-write regressions cover those
handlers and the control panel. Missing/nested/nonnumeric input is exercised
against the real application. PHP warnings, deprecations and fatal errors fail
the run, even if a redirect hides them from the response body.

Media fixtures include apostrophes. A database trigger injects a failure halfway
through a reorder, and the suite verifies that the transaction preserves every
record. Production reordering requires InnoDB and an unused ID 0 as a temporary
transactional position; unsupported legacy engines safely refuse the operation.
Contact tests submit the controls actually rendered by the page and verify that
hidden/absent optional fields keep their existing values.

These are HTTP/session/database E2E tests. They do not execute a browser's
JavaScript. A manual browser run can check the existing AJAX navigation and UI;
third-party embeds and email delivery are intentionally blocked. No real email,
SoundCloud, YouTube, social account or live PHP/Apache/FPM deployment is tested.
CSRF protection, wider SQL/output-escaping work and production upgrade validation
remain separate work.

CI runs the application suite on PHP 8.2.35, 8.3.36, 8.4.27 and 8.5.12 with a
disposable MariaDB 11.4.13 service. The service publishes a random port on the
ephemeral runner. `NICKWEB_USE_CI_DATABASE=1` explicitly selects this documented
mode; it still uses the fixed fixture database/user and literal loopback host.
This does not reproduce the historical operating system or prove a match to the
unknown current production runtime.
