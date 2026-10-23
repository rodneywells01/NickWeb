# October 2026 PHP security review

Reviewed on 2026-10-23, starting from NickWeb `b788d7f2b437c591043b4f08492cbaa7a810bb71`.

## Finding and scope

The [PHP security advisory of October 21](https://www.php.net/archive/2026.php#2026-10-21-1)
and the upstream reports for
[CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w)
and [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp)
were checked against the repository. These are interpreter memory/state bugs.
They cannot be repaired by changing a PHP application or adding an application
version check. mod_php has a confirmed path before application code runs;
PHP-FPM and other web SAPIs must also be reviewed.

Update each web runtime to **8.5.12, 8.4.27, 8.3.36, or 8.2.35 or a later patched
release in that branch**, or obtain explicit vendor evidence that both CVE fixes
are backported to the installed package. Older upstream branches are unsupported;
a lower version number alone does not settle a vendor package's patch status.
Configuration measures only reduce particular paths. Updating is the complete fix.

Repository identification: this README describes Nicholas Nasibyan's website,
XAMP, and deployment using Git pull. `index.php` identifies the same musician and
loads the database/session-backed site. The alternate `NewNicholasNasibyan`
repository at `983f626` contains only a Flask Hello World skeleton. This establishes
NickWeb as the relevant historical PHP application, **not** as a verified copy of
the live deployment. No production HTTP requests, exploit attempts, infrastructure
changes or deployments were performed. Live document root, commit, PHP version,
SAPI, extensions and configuration remain unknown.

## Repository mitigations

- `includes/request.php`, called from the shared functions bootstrap, rejects
  array/nested request fields and malformed numeric event/media controls with HTTP
  400. The site's forms use scalar strings. Required-field validation now handles
  missing and non-string fields without calling string functions on arrays. These
  changes remove specific attacker-controlled TypeError/diagnostic paths on PHP 8;
  they run after PHP request parsing and cannot protect that earlier stage.
- Removed `test.php`, `includes/jsonParse.php`, and `includes/sqlcommands.php`.
  They were unfinished, had parse errors, and had no references in tracked source.
  Direct requests could otherwise reach those compile failures.
- `deploy/php-security.ini` disables unused uploads and upload progress at PHP
  startup and sets conservative request parsing limits. The application has no
  file inputs, `$_FILES` use, or multipart forms. Sessions remain enabled for login.
  No runtime session-serializer switching was found. The October 23
  [session upload progress notice](https://www.php.net/archive/2026.php#2026-10-23-1)
  also recommends disabling the unused feature.
- `deploy/apache-security.conf` is an Apache 2.4 VirtualHost include. It blocks
  multipart requests before PHP, limits request bodies to 8 MiB and bounds HTTP
  headers. It denies direct HTTP access to helpers, tooling, deployment files,
  Git metadata and the old mail/test endpoints. Local PHP includes still work.
  This assumes Apache with mod_rewrite; it is not an nginx configuration.
- `tools/check-php-runtime.php` is CLI-only. It checks known upstream version floors
  and selected startup settings without loading database configuration. It rejects
  unknown branches/prereleases/vendor suffixes as unconfirmed; it does not decide
  whether a vendor backport is present or certify the web server.

## Operator steps before production can be considered patched

1. From authorized server access, identify the vhost/document root and checked-out
   repository/commit. Determine every actual request handler (Apache module,
   FPM pool, CGI, etc.), package source, and loaded PHP configuration. CLI `php -v`
   and `php --ini` are useful inventory but may describe a different installation.
   Do not publish `phpinfo()` or configuration/credential dumps to the web.
2. Back up the site, instance-local database and server configuration securely.
   Upgrade the actual web interpreter/packages to the fixed versions or verified
   vendor backports, retaining required extensions. Test in staging with a private
   database copy: public tabs, login/logout and administrative content/media/event
   changes. This repository has no database schema/fixtures or verified PHP 8
   migration history, so syntax/regression checks are not a full application test.
3. Merge `deploy/php-security.ini` into the **web SAPI's startup php.ini or scanned
   conf.d**, reviewing existing limits. Do not rely on `ini_set()`, an application
   `require`, or FPM `.user.ini` to disable upload progress before request parsing.
   Confirm later files, pool/server values and per-directory overrides do not undo
   these controls. The `fatal_error_backtraces` control applies on PHP 8.5.
4. If Apache is the confirmed frontend, review and include the Apache fragment
   inside every relevant HTTP/HTTPS VirtualHost, before broader rewrite/proxy
   rules can bypass it. Enable mod_rewrite, run `apachectl configtest`, and check
   existing `<Location>`/authorization/alias rules for overrides. For another
   frontend, implement equivalent early multipart rejection, decoded body/header
   limits, and internal-path denial in that frontend. All ingress paths must be
   covered, including chunked/no-Content-Length requests. Bounds on field counts,
   parts, part headers and nesting require review across proxy and PHP layers.
5. Restart **every** PHP worker and any web service embedding PHP; a config reload
   alone may leave old workers. Verify the web worker executable/package and
   effective settings through private operator diagnostics. A changed CLI binary
   or completed PR does not prove that running workers were updated.
6. Run `php tools/check-php-runtime.php` against an appropriately configured CLI
   runtime as a supplemental check, then independently verify each web handler.
   Keep vendor advisories/package changelogs as evidence when version numbers are
   lower than the upstream floor. No automatic vendor-backport bypass is provided.
7. Exercise ordinary requests in staging and review startup, shutdown, fatal and
   timeout diagnostics using `E_ALL` logging. Remove invalid/deprecated INI values
   applied before initialization, including frontend `PHP_VALUE` parameters.
   Turning off display/logging or using `@` is not a CVE mitigation. Check worker
   recycling and service health after restart.

If upgrading is temporarily impossible, restrict public access until an operator
can patch. The upstream advisory also describes administratively enforcing
`memory_limit=-1` as a **partial, temporary** OOM mitigation. It is intentionally
not enabled by this PR: allocation failure can still reach vulnerable code and
unbounded worker memory needs an independently designed availability boundary.
Do not substitute `RLIMIT_AS` or `RLIMIT_DATA`, which can trigger allocation failure.
Do not treat any temporary configuration or absence of log errors as proof of safety.

## Validation and remaining limits

`tests/run.py` lints every PHP file, tests the four version boundaries, regression
checks for flat/nested/missing fields and numeric controls, and isolated HTTP 200,
400 and 422 responses. The HTTP fixture loads only shared validation/functions,
uses a temporary document root and never loads `connection.php` or sends mail.
It also starts a CLI process using the supplied startup INI fragment.

`tests/apache.py` runs `httpd -t` and HTTP checks in a disposable Apache 2.4 Docker
container, using synthetic static/CGI fixtures. It checks private-path denial,
ordinary requests, mixed-case multipart, and body limits with fixed-length and
chunked bodies. It does not load a PHP Apache module or test real FPM pools.
GitHub Actions runs both suites, including PHP checks on all four fixed releases.

The review is focused on the October advisory. Legacy authentication/authorization,
CSRF, SQL construction, output escaping and credential handling need separate
work. Credentials found in tracked configuration/client assets were not tested
or reproduced here; the owner should review and rotate any still-valid exposed
secrets. No database integration, full UI test, production configuration audit or
proof of absence of all fatal-error paths is included.
