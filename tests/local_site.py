"""Run the real PHP application against a disposable, synthetic loopback database."""
from contextlib import ExitStack
import os
from pathlib import Path
import shutil
import socket
import subprocess
import tempfile
import time
from urllib.request import urlopen

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BINARY', 'php')
HIDDEN = {'creationflags': subprocess.CREATE_NO_WINDOW} if os.name == 'nt' else {}


def free_port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]


class LocalSite:
    def __enter__(self):
        self.stack = ExitStack()
        try:
            temp = Path(self.stack.enter_context(tempfile.TemporaryDirectory(prefix='nickweb-e2e-')))
            self.temp = temp
            (temp / 'sessions').mkdir()
            self.env = dict(os.environ, NICKWEB_SYNTHETIC_FIXTURE='1', PHP_INI_SCAN_DIR='')
            extension_dir = subprocess.check_output([PHP, '-n', '-r', 'echo ini_get("extension_dir");'], text=True).strip()
            if os.name == 'nt':
                extension_dir = str(Path(PHP).resolve().parent / 'ext')
            modules = subprocess.check_output([PHP, '-n', '-m'], text=True).splitlines()
            extensions = []
            for module in ('mysqlnd', 'mysqli'):
                if module not in modules:
                    extensions += ['-d', 'extension=' + module]
            self.php = [PHP, '-n', '-d', f'extension_dir={extension_dir}', *extensions,
                        '-d', 'output_buffering=4096', '-d', 'display_errors=1', '-d', 'log_errors=1',
                        '-d', f'session.save_path={temp / "sessions"}',
                        '-d', 'error_reporting=32767', '-d', 'date.timezone=UTC',
                        '-d', 'session.upload_progress.enabled=0', '-d', 'file_uploads=0',
                        '-d', 'allow_url_fopen=0', '-d', 'allow_url_include=0',
                        '-d', 'disable_functions=mail,curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,exec,shell_exec,passthru,system,proc_open,popen']
            database_bin = os.environ.get('MARIADB_BIN')
            if database_bin:
                if os.name != 'nt':
                    raise RuntimeError('Portable initialization is currently Windows-only; use the documented CI database service on Linux.')
                bindir = Path(database_bin)
                port = free_port()
                data = temp / 'database'
                subprocess.run([str(bindir / 'mariadb-install-db.exe'), f'--datadir={data}', f'--port={port}',
                                '--password=synthetic-root-only', '--silent'], check=True, **HIDDEN)
                db_logs = self.stack.enter_context((temp / 'database.log').open('w+'))
                database = subprocess.Popen([str(bindir / 'mariadbd.exe'), '--no-defaults', f'--datadir={data}',
                                             '--bind-address=127.0.0.1', f'--port={port}', '--skip-name-resolve',
                                             '--innodb-buffer-pool-size=32M', '--max-connections=10'],
                                            stdout=db_logs, stderr=db_logs, **HIDDEN)
                self.stack.callback(self.stop, database)
                client = [str(bindir / 'mariadb.exe'), '--no-defaults', '--protocol=tcp', '--host=127.0.0.1',
                          f'--port={port}', '--user=root']
                root_env = dict(self.env, MYSQL_PWD='synthetic-root-only')
                for attempt in range(100):
                    ready = subprocess.run(client + ['--execute=SELECT 1'], env=root_env, capture_output=True, **HIDDEN)
                    if ready.returncode == 0:
                        break
                    if database.poll() is not None:
                        raise RuntimeError('Disposable MariaDB stopped; inspect local database.log')
                    time.sleep(0.1)
                else:
                    raise RuntimeError('Disposable MariaDB did not start')
                sql = "CREATE DATABASE nickweb_test CHARACTER SET utf8mb4; CREATE USER 'nickweb_fixture'@'127.0.0.1' IDENTIFIED BY 'synthetic-local-only'; GRANT ALL PRIVILEGES ON nickweb_test.* TO 'nickweb_fixture'@'127.0.0.1';"
                subprocess.run(client, input=sql, text=True, env=root_env, check=True, capture_output=True, **HIDDEN)
            elif os.environ.get('NICKWEB_USE_CI_DATABASE') == '1':
                port = int(os.environ['NICKWEB_TEST_DB_PORT'])
                if port < 1024 or port > 65535:
                    raise RuntimeError('Only a non-default mapped loopback fixture port is supported')
            else:
                raise RuntimeError('Set MARIADB_BIN for a fresh portable server, or explicitly configure the documented synthetic CI service.')
            self.env['NICKWEB_TEST_DB_PORT'] = str(port)
            subprocess.run(self.php + [str(ROOT / 'tests/seed.php')], env=self.env, check=True, **HIDDEN)
            web = temp / 'website'
            web.mkdir()
            # Copy only the app, excluding production connection data and browser API keys.
            for file in ROOT.glob('*.php'):
                if file.name != 'emailtest.php':
                    shutil.copy2(file, web / file.name)
            for directory in ('includes', 'stylesheets', 'scripts', 'nickpics', 'icons', 'Hover-master'):
                shutil.copytree(ROOT / directory, web / directory,
                                ignore=shutil.ignore_patterns('connection.php', 'email.js', 'gmailscript.js'))
            shutil.copy2(ROOT / 'tests/local_connection.php', web / 'includes/connection.php')
            self.log_path = temp / 'php.log'
            logs = self.stack.enter_context(self.log_path.open('w+'))
            self.port = free_port()
            self.base = f'http://127.0.0.1:{self.port}'
            server = subprocess.Popen(self.php + ['-S', f'127.0.0.1:{self.port}', '-t', str(web), str(ROOT / 'tests/local_router.php')],
                                      cwd=web, env=self.env, stdout=logs, stderr=logs, **HIDDEN)
            self.stack.callback(self.stop, server)
            for attempt in range(100):
                try:
                    with urlopen(self.base, timeout=1) as response:
                        if response.headers.get('X-NickWeb-Fixture') != 'synthetic-only':
                            raise RuntimeError('Fixture safety header missing')
                    break
                except OSError:
                    if server.poll() is not None:
                        raise RuntimeError('Local PHP server stopped')
                    time.sleep(0.1)
            else:
                raise RuntimeError('Local PHP server did not start')
            print(f'Synthetic local site: {self.base}', flush=True)
            return self
        except BaseException:
            self.stack.close()
            raise

    @staticmethod
    def stop(process):
        if process.poll() is None:
            process.terminate()
            try:
                process.wait(timeout=10)
            except subprocess.TimeoutExpired:
                process.kill()
                process.wait(timeout=5)

    def __exit__(self, *args):
        self.stack.close()


if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--seconds', type=int, default=0, help='Automatically stop after this many seconds (0 waits for Ctrl+C).')
    args = parser.parse_args()
    with LocalSite() as site:
        print('Synthetic admin: fixture-admin / local-fixture-password; Ctrl+C stops and deletes this temporary environment.', flush=True)
        deadline = time.monotonic() + args.seconds if args.seconds else None
        try:
            while deadline is None or time.monotonic() < deadline:
                time.sleep(1)
        except KeyboardInterrupt:
            pass
