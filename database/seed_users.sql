-- Utenti iniziali dell'applicazione.
-- I placeholder __ADMIN_HASH__ e __USER_HASH__ vengono sostituiti in fase di installazione.
INSERT IGNORE INTO users (username, last_name, first_name, password_hash, role, phone, status)
VALUES ('admin', 'System', 'Admin', '__ADMIN_HASH__', 'admin', '', 'attivo');

INSERT IGNORE INTO users (username, last_name, first_name, password_hash, role, phone, status)
VALUES ('user', 'System', 'User', '__USER_HASH__', 'user', '', 'attivo');

INSERT IGNORE INTO users (username, last_name, first_name, password_hash, role, phone, status)
VALUES ('supervisor', 'System', 'Supervisor', '__USER_HASH__', 'supervisor', '', 'attivo');
