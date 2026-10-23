-- Reconstructed from application queries. NOT a production schema export.
-- Only the synthetic nickweb_test database is used by the fixture runner.
DROP TABLE IF EXISTS admins, main, about, contact, events, songs, videos;
CREATE TABLE admins (id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(100) NOT NULL UNIQUE, hashed_password VARCHAR(255) NOT NULL);
CREATE TABLE main (id INT PRIMARY KEY, title TEXT NOT NULL, content TEXT NOT NULL);
CREATE TABLE about (id INT PRIMARY KEY, aboutnick TEXT NOT NULL, services TEXT NOT NULL, experiences TEXT NOT NULL);
CREATE TABLE contact (id INT PRIMARY KEY, email TEXT NOT NULL, location1 TEXT NOT NULL, location2 TEXT NOT NULL, homephone TEXT NOT NULL, mobilephone TEXT NOT NULL, gmaps TEXT NOT NULL, facebook TEXT NOT NULL, twitter TEXT NOT NULL, instagram TEXT NOT NULL, linkedin TEXT NOT NULL);
CREATE TABLE events (id INT PRIMARY KEY AUTO_INCREMENT, name TEXT NOT NULL, location TEXT NOT NULL, description TEXT NOT NULL, datetime DATETIME NOT NULL);
CREATE TABLE songs (id INT PRIMARY KEY AUTO_INCREMENT, songcode TEXT NOT NULL);
CREATE TABLE videos (id INT PRIMARY KEY AUTO_INCREMENT, videocode TEXT NOT NULL);
INSERT INTO main VALUES (1, 'Synthetic musician fixture', 'Local-only home page fixture');
INSERT INTO about VALUES (1, 'Synthetic biography', 'Synthetic music services', 'Synthetic teaching experience');
INSERT INTO contact VALUES (1, 'fixture@example.invalid', 'Fixture Street', '', '555-0101', '555-0100', 'https://example.invalid/map', 'https://example.invalid/facebook', 'https://example.invalid/twitter', 'https://example.invalid/instagram', 'https://example.invalid/linkedin');
INSERT INTO events VALUES (1, 'Synthetic recital one', 'Fixture Hall', 'First synthetic event', '2030-01-01 18:00:00'), (2, 'Synthetic recital two', 'Fixture Hall', 'Second synthetic event', '2030-02-01 18:00:00');
INSERT INTO songs VALUES (1, '<iframe title="Composition one" height="150" src="about:blank"></iframe>'), (2, '<iframe title="Composition two''s performance" height="150" src="about:blank"></iframe>');
INSERT INTO videos VALUES (1, '<iframe title="Video one" src="about:blank"></iframe>'), (2, '<iframe title="Video two''s performance" src="about:blank"></iframe>');
