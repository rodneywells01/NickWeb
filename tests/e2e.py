"""Real application HTTP/session/SQL flows against synthetic records only."""
from http.cookiejar import CookieJar
from html.parser import HTMLParser
import json
import subprocess
from urllib.error import HTTPError
from urllib.parse import urlencode
from urllib.request import build_opener, HTTPCookieProcessor

from local_site import LocalSite, ROOT, HIDDEN


class Client:
    def __init__(self, site):
        self.site = site
        self.opener = build_opener(HTTPCookieProcessor(CookieJar()))

    def request(self, path, form=None, expected=200):
        body = urlencode(form).encode() if form is not None else None
        try:
            response = self.opener.open(self.site.base + path, data=body, timeout=5)
        except HTTPError as error:
            response = error
        with response:
            text = response.read().decode('utf-8')
            assert response.status == expected, (path, response.status)
            assert response.headers.get('X-NickWeb-Fixture') == 'synthetic-only', 'Unsafe target'
            for diagnostic in ('Fatal error', 'Parse error', 'Warning:', 'Deprecated:', 'Notice:'):
                assert diagnostic not in text, (path, diagnostic)
            return text

    def login(self):
        self.request('/logon.php', {'submit': 'Log In', 'username': 'fixture-admin', 'password': 'local-fixture-password'})
        assert 'Control Panel' in self.request('/index.php'), 'Login session was not retained'


def snapshot(site):
    result = subprocess.check_output(site.php + [str(ROOT / 'tests/snapshot.php')], env=site.env, **HIDDEN)
    return json.loads(result)


def reset(site):
    subprocess.run(site.php + [str(ROOT / 'tests/seed.php')], env=site.env, check=True,
                   stdout=subprocess.DEVNULL, **HIDDEN)


def public(site):
    client = Client(site)
    for path, expected in [('/', 'Nicholas Nasibyan'), ('/main.php', 'Synthetic musician fixture'),
                           ('/about.php', 'Synthetic biography'), ('/events.php', 'Synthetic recital one'),
                           ('/songs.php', 'Composition one'), ('/videos.php', 'Video one'),
                           ('/contact.php', 'fixture@example.invalid')]:
        assert expected in client.request(path), path
    assert 'Control Panel' not in client.request('/index.php')
    for path in ('/includes/connection.php', '/emailtest.php', '/scripts/gmailscript.js'):
        client.request(path, expected=403)
    assert 'Messaging is disabled' in client.request('/scripts/email.js')


def login_logout(site):
    client = Client(site)
    client.request('/logon.php', {'submit': 'Log In', 'username': 'fixture-admin', 'password': 'wrong'})
    assert 'Control Panel' not in client.request('/index.php')
    client.login()
    assert 'Tab Control' in client.request('/controlpanel.php')
    client.request('/logout.php')
    assert 'Control Panel' not in client.request('/index.php')


def home(site):
    client = Client(site)
    client.login()
    client.request('/main.php', {'submit': 'Save Changes', 'title': 'Edited synthetic title', 'content': 'Edited synthetic home'})
    assert snapshot(site)['main'][0]['content'] == 'Edited synthetic home'
    assert 'Edited synthetic title' in Client(site).request('/main.php')


def biography(site):
    client = Client(site)
    client.login()
    client.request('/about.php', {'submit': 'Save Changes', 'aboutnick': 'Edited synthetic biography', 'services': 'Edited services', 'experiences': 'Edited experience'})
    state = snapshot(site)['about'][0]
    assert state['aboutnick'] == 'Edited synthetic biography'
    assert state['services'] == 'Edited services' and state['experiences'] == 'Edited experience'
    assert 'Edited synthetic biography' in Client(site).request('/about.php')


def events(site):
    client = Client(site)
    client.login()
    client.request('/events.php', {'add': 'Add Event', 'event_name3': 'Added fixture recital',
                                  'event_location3': 'Added Hall', 'event_description3': 'Added description',
                                  'event_datetime3': '2030-03-01 18:00:00'})
    assert len(snapshot(site)['events']) == 3
    form = {'submit': 'Save Changes'}
    for item in snapshot(site)['events']:
        number = str(item['id'])
        for field, column in [('name', 'name'), ('location', 'location'), ('description', 'description'), ('datetime', 'datetime')]:
            form['event_' + field + number] = item[column]
    form['event_name3'] = 'Edited fixture recital'
    client.request('/events.php', form)
    assert snapshot(site)['events'][2]['name'] == 'Edited fixture recital'
    assert 'Edited fixture recital' in Client(site).request('/events.php')
    client.request('/events.php?delete=2')
    state = snapshot(site)['events']
    assert len(state) == 2 and [int(row['id']) for row in state] == [1, 2]
    assert 'Synthetic recital two' not in Client(site).request('/events.php')


def media(site, page, table, field, control, direction, title):
    client = Client(site)
    client.login()
    code = f'<iframe title="{title}" height="450" src="about:blank#soundcloud"></iframe>'
    client.request('/' + page, {'submit': 'Add', control: code})
    assert len(snapshot(site)[table]) == 3
    assert title in Client(site).request('/' + page)
    before_move = sorted(row[field] for row in snapshot(site)[table])
    client.request('/' + page + '?moveid=3&movedirection=' + direction)
    state = snapshot(site)[table]
    assert title in state[1][field], 'Move did not persist'
    assert sorted(row[field] for row in state) == before_move, 'Swap changed or lost quoted embed content'
    client.request('/' + page + '?deleteid=1')
    state = snapshot(site)[table]
    assert len(state) == 2 and [int(row['id']) for row in state] == [1, 2]
    assert title in state[0][field], 'Delete/renumber did not preserve the moved record'


def contact_form():
    return {'submit': 'Save Changes', 'email': 'edited@example.invalid', 'location1': 'Edited Street',
            'location2': 'Edited City', 'mobilephone': '555-0199',
            **{field: 'https://example.invalid/edited-' + field for field in ('facebook', 'twitter', 'instagram', 'linkedin')}}


def contact(site):
    client = Client(site)
    client.login()
    class Controls(HTMLParser):
        def __init__(self):
            super().__init__()
            self.values = {}

        def handle_starttag(self, tag, attributes):
            attributes = dict(attributes)
            if tag == 'input' and 'name' in attributes:
                self.values[attributes['name']] = attributes.get('value', '')

    controls = Controls()
    controls.feed(client.request('/contact.php'))
    assert 'location1' not in controls.values, 'Fixture must reflect the actual form'
    for field, value in contact_form().items():
        if field in controls.values:
            controls.values[field] = value
    client.request('/contact.php', controls.values)
    state = snapshot(site)['contact'][0]
    assert state['email'] == 'edited@example.invalid' and state['mobilephone'] == '555-0199'
    assert state['location1'] == 'Fixture Street', 'Hidden location must be preserved'
    assert state['location2'] == '' and state['homephone'] == '555-0101', 'Absent optional controls must preserve stored data'
    assert 'edited@example.invalid' in Client(site).request('/contact.php')


def malformed(site):
    client = Client(site)
    client.request('/logon.php', {'submit': 'Log In', 'username': 'fixture-admin'})
    client.request('/songs.php?moveid=word&movedirection=1', expected=400)
    client.request('/index.php?event%5B%5D=1', expected=400)


def unauthorized(site, path, form=None):
    before = snapshot(site)
    Client(site).request(path, form)
    assert snapshot(site) == before, 'Anonymous request changed database records'


def reorder_rollback(site):
    client = Client(site)
    client.login()
    subprocess.run(site.php + [str(ROOT / 'tests/fail_reorder.php')], env=site.env, check=True, **HIDDEN)
    before = snapshot(site)
    client.request('/songs.php?moveid=1&movedirection=-1')
    client.request('/videos.php?moveid=1&movedirection=1')
    assert snapshot(site) == before, 'A failed swap must roll back without losing records'


if __name__ == '__main__':
    cases = [
        ('public pages and blocked integrations', public),
        ('invalid login, login session and logout', login_logout),
        ('admin home edit and public persistence', home),
        ('admin biography/services/experience edit', biography),
        ('admin event add/edit/delete and renumber', events),
        ('admin composition add/move/delete', lambda site: media(site, 'songs.php', 'songs', 'songcode', 'newsong', '1', 'Added composition')),
        ('admin video add/move/delete', lambda site: media(site, 'videos.php', 'videos', 'videocode', 'newvideo', '-1', 'Added video')),
        ('admin contact edit and public persistence', contact),
        ('malformed and missing input', malformed),
        ('media swap SQL-failure rollback', reorder_rollback),
        ('anonymous home write denied', lambda site: unauthorized(site, '/main.php', {'submit': 'Save Changes', 'title': 'Unauthorized', 'content': 'Unauthorized'})),
        ('anonymous contact write denied', lambda site: unauthorized(site, '/contact.php', contact_form())),
        ('anonymous composition write denied', lambda site: unauthorized(site, '/songs.php', {'submit': 'Add', 'newsong': '<iframe height="150" src="about:blank#soundcloud"></iframe>'})),
        ('anonymous event delete denied', lambda site: unauthorized(site, '/events.php?delete=1')),
        ('anonymous event add denied', lambda site: unauthorized(site, '/events.php', {'add': 'Add Event', 'event_name3': 'Unauthorized', 'event_location3': 'Unauthorized', 'event_description3': 'Unauthorized', 'event_datetime3': '2030-03-01 18:00:00'})),
        ('anonymous biography write denied', lambda site: unauthorized(site, '/about.php', {'submit': 'Save Changes', 'aboutnick': 'Unauthorized', 'services': 'Unauthorized', 'experiences': 'Unauthorized'})),
        ('anonymous video write denied', lambda site: unauthorized(site, '/videos.php', {'submit': 'Add', 'newvideo': '<iframe src="about:blank"></iframe>'})),
        ('anonymous composition delete denied', lambda site: unauthorized(site, '/songs.php?deleteid=1')),
        ('anonymous video delete denied', lambda site: unauthorized(site, '/videos.php?deleteid=1')),
        ('anonymous control panel denied', lambda site: unauthorized(site, '/controlpanel.php')),
    ]
    failures = []
    with LocalSite() as site:
        for name, test in cases:
            reset(site)
            try:
                test(site)
                print('PASS: ' + name, flush=True)
            except Exception as error:
                failures.append(name)
                print('FAIL: ' + name + ': ' + str(error), flush=True)
        log = site.log_path.read_text(errors='replace')
        diagnostics = [line for line in log.splitlines() if any(word in line for word in ('Fatal error', 'Parse error', 'Warning:', 'Deprecated:', 'Notice:'))]
        if diagnostics:
            print('PHP diagnostics: ' + '\n'.join(diagnostics), flush=True)
            failures.append('PHP diagnostics must be empty')
        print(f'Application E2E groups: {len(cases) - len(failures)} passed, {len(failures)} failed.', flush=True)
    if failures:
        raise SystemExit(1)
