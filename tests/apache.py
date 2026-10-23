"""Test the Apache example in a disposable container with synthetic files only."""
import http.client
from pathlib import Path
import subprocess
import tempfile
import time

ROOT = Path(__file__).resolve().parents[1]
IMAGE = 'httpd:2.4'


def docker(*args):
    return subprocess.check_output(['docker', *args], text=True).strip()


with tempfile.TemporaryDirectory(prefix='nickweb-apache-') as directory:
    temp = Path(directory)
    temp.chmod(0o755)
    htdocs = temp / 'htdocs'
    htdocs.mkdir(mode=0o755)
    (htdocs / 'index.html').write_text('OK', encoding='ascii')
    probe = htdocs / 'probe.cgi'
    probe.write_text('#!/bin/sh\nprintf "Content-Type: text/plain\\r\\n\\r\\n"\ncat >/dev/null\necho OK\n', encoding='ascii')
    probe.chmod(0o755)
    for name in ('includes', 'deploy', 'tools', 'tests', 'docs', '.git'):
        folder = htdocs / name
        folder.mkdir(mode=0o755)
        (folder / 'fixture.txt').write_text('SHOULD NOT BE SERVED', encoding='ascii')
    for name in ('emailtest.php', 'test.php'):
        (htdocs / name).write_text('SHOULD NOT BE SERVED', encoding='ascii')
    config = docker('run', '--rm', IMAGE, 'cat', '/usr/local/apache2/conf/httpd.conf')
    for module in ('rewrite', 'cgid'):
        config = config.replace(f'#LoadModule {module}_module', f'LoadModule {module}_module')
    config += '''
ServerName localhost
<VirtualHost *:80>
    Include conf/extra/nickweb-security.conf
    ScriptAlias /probe /usr/local/apache2/htdocs/probe.cgi
</VirtualHost>
'''
    conf = temp / 'httpd.conf'
    conf.write_text(config, encoding='utf-8')
    volumes = [
        '-v', f'{htdocs}:/usr/local/apache2/htdocs:ro',
        '-v', f'{conf}:/usr/local/apache2/conf/httpd.conf:ro',
        '-v', f'{ROOT / "deploy/apache-security.conf"}:/usr/local/apache2/conf/extra/nickweb-security.conf:ro',
    ]
    docker('run', '--rm', *volumes, IMAGE, 'httpd', '-t')
    container = docker('run', '--detach', '--rm', '-p', '127.0.0.1::80', *volumes, IMAGE)
    try:
        port = int(docker('port', container, '80/tcp').rsplit(':', 1)[1])

        def request(method, path, body=None, headers=None, chunked=False):
            connection = http.client.HTTPConnection('127.0.0.1', port, timeout=10)
            try:
                connection.request(method, path, body=body, headers=headers or {}, encode_chunked=chunked)
                response = connection.getresponse()
                status = response.status
                response.read()
                return status
            finally:
                connection.close()

        for attempt in range(100):
            try:
                if request('GET', '/') == 200:
                    break
            except (OSError, http.client.HTTPException):
                pass
            time.sleep(0.1)
        else:
            raise RuntimeError('Apache did not start')
        for name in ('includes', 'deploy', 'tools', 'tests', 'docs', '.git'):
            assert request('GET', f'/{name}/fixture.txt') == 403, name
        for path in ('/emailtest.php', '/test.php', '/includes/fixture.txt/extra'):
            assert request('GET', path) == 403, path
        for content_type in ('multipart/form-data; boundary=fixture', 'MuLtIpArT/form-data; boundary=fixture'):
            assert request('POST', '/probe', b'fixture', {'Content-Type': content_type}) == 403
            assert request('POST', '/probe', iter([b'fixture']), {'Content-Type': content_type}, True) == 403
        assert request('POST', '/probe', b'name=Nick', {'Content-Type': 'application/x-www-form-urlencoded'}) == 200
        oversized = b'x' * (8 * 1024 * 1024 + 1)
        assert request('POST', '/probe', oversized) == 413
        # Unknown Content-Length is a distinct boundary highlighted by the advisory.
        assert request('POST', '/probe', iter([oversized]), chunked=True) == 413
        print('PASS: Apache syntax, internal paths, multipart and body-size boundaries (including chunked).')
    finally:
        docker('stop', container)
