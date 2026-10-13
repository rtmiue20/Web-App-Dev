-- Run once on databases created from the earlier users.role ENUM schema.
CREATE TABLE IF NOT EXISTS roles (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (name) VALUES ('member'), ('admin');

ALTER TABLE users ADD COLUMN role_id INT UNSIGNED NULL AFTER password_hash;

UPDATE users u
INNER JOIN roles r ON r.name = u.role
SET u.role_id = r.id;

ALTER TABLE users MODIFY role_id INT UNSIGNED NOT NULL;
ALTER TABLE users
  ADD CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT;
ALTER TABLE users DROP COLUMN role;
