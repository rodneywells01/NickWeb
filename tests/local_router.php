<?php
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
// This router is outside the temporary document root and is not a deployment target.
header('X-NickWeb-Fixture: synthetic-only');
header("Content-Security-Policy: default-src 'self' data:; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self'; frame-src 'none'; form-action 'self'; base-uri 'self'");
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($path === '/scripts/email.js') {
    header('Content-Type: application/javascript');
    echo 'function construct_email(){alert("Messaging is disabled in the synthetic local fixture.");}';
    exit;
}
if (preg_match('~(?:^|/)(?:includes|tests|tools|deploy|docs|\.git)(?:/|$)|\.\.|emailtest\.php|gmailscript\.js~i', $path)) {
    http_response_code(403); exit('Local fixture: route blocked.');
}
$pages = array('/', '/index.php', '/main.php', '/about.php', '/events.php', '/songs.php', '/videos.php', '/contact.php', '/logon.php', '/logout.php', '/controlpanel.php', '/new_admin.php', '/services.php');
if (in_array($path, $pages, true)) {
    require getcwd() . ($path === '/' ? '/index.php' : $path);
    return true;
}
if (preg_match('~^/(?:nickpics|icons|stylesheets|scripts|Hover-master/css)/.*\.(?:png|jpe?g|gif|css|js)$~i', $path)) {
    return false;
}
http_response_code(404);
echo 'Local fixture: route not found.';
