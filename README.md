# NickWeb
Nicholas Nasibyans new Website. Looks fancy! First launched in 2014.

### Stack

XAMP

### PHP security and local checks

Read [the October 2026 PHP security review and operator runbook](docs/php-security-2026-10.md)
before deploying. A Git pull cannot patch the PHP interpreter. The added Apache and
PHP startup fragments require explicit installation and verification by the server
operator; they are not automatically activated by this repository.

With Python 3 and patched PHP on PATH, run `python3 tests/run.py` (or set
`PHP_BINARY` to the PHP executable). This lints PHP and runs isolated checks without
connecting to the site's database or sending email. `python3 tests/apache.py`
additionally tests the Apache example using Docker and synthetic files.

For the actual application, sessions, login and SQL flows, see
[the isolated local database and E2E instructions](docs/local-testing.md).
The fixtures are reconstructed from source and contain no production records.

### Deploy Strategy 

Git Pull. Sue me. A product of a naive Sophomore Student. 

### Ongoing Issues 

* Stack is cancer 
* No tests 
* Poor deployment strategy 
* DB localized to instance (Huge TODO)

### Credits 

Created by Rodney Wells.
