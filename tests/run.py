"""Isolated regression checks. Never load connection.php or contact external services."""
import os
from pathlib import Path
import socket
import subprocess
import tempfile
import time
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BINARY', 'php')


def run(*args):
    subprocess.run([PHP, '-n', *args], cwd=ROOT, check=True)


# Include new files as well as tracked files; do not execute application entry points.
files = sorted(ROOT.rglob('*.php'))
for path in files:
    run('-l', str(path))
print(f'PASS: linted {len(files)} PHP files.', flush=True)
run(str(ROOT / 'tests/security.php'))

with tempfile.TemporaryDirectory(prefix='nickweb-tests-') as temp:
    router = Path(temp) / 'router.php'
    router.write_text('''<?php
// Legacy helper files contain whitespace outside PHP tags.
ob_start();
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require getenv('NICKWEB_TEST_ROOT') . '/includes/functions.php';
require getenv('NICKWEB_TEST_ROOT') . '/includes/validation_functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_presences(array('username', 'password'));
    if (!empty($errors)) { http_response_code(422); }
}
echo 'OK';
''', encoding='utf-8')
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]
    env = dict(os.environ, NICKWEB_TEST_ROOT=str(ROOT))
    # Temporary document root, no database, production credentials, or mail transport.
    with tempfile.TemporaryFile() as logs:
        server = subprocess.Popen(
            [PHP, '-n', '-S', f'127.0.0.1:{port}', '-t', temp, str(router)],
            env=env, stdout=logs, stderr=logs,
        )
        try:
            base = f'http://127.0.0.1:{port}'
            for attempt in range(100):
                try:
                    with urlopen(base, timeout=1) as response:
                        assert response.status == 200
                    break
                except URLError:
                    if server.poll() is not None:
                        raise RuntimeError('Local test server exited')
                    time.sleep(0.05)
            else:
                raise RuntimeError('Local test server did not start')
            cases = [
                ('/?redirect=events&event=1', None, 200),
                ('/?moveid=2&movedirection=-1', None, 200),
                ('/?moveid=word&movedirection=1', None, 400),
                ('/?movedirection=1', None, 400),
                ('/?event[]=1', None, 400),
                ('/', b'username[]=Nick&password=test', 400),
                ('/', b'username=Nick&password=test', 200),
                ('/', b'username=Nick', 422),
            ]
            for path, body, expected in cases:
                request = Request(base + path, data=body)
                try:
                    response = urlopen(request, timeout=3)
                except HTTPError as error:
                    response = error
                with response:
                    assert response.status == expected, (path, response.status, expected)
                    assert b'Fatal error' not in response.read()
            print(f'PASS: {len(cases)} isolated HTTP request checks.', flush=True)
        finally:
            server.terminate()
            server.wait(timeout=5)

# Confirm the startup fragment is actually accepted, with no system scan overrides.
env = dict(os.environ, PHP_INI_SCAN_DIR='')
subprocess.run(
    [PHP, '-c', str(ROOT / 'deploy/php-security.ini'),
     str(ROOT / 'tools/check-php-runtime.php')],
    env=env, cwd=ROOT, check=True,
)
