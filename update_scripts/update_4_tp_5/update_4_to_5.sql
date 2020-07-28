USE homestead;

ALTER TABLE movies DROP FOREIGN KEY movies_emergency_worker_id_foreign;
ALTER TABLE movies ADD FOREIGN KEY (emergency_worker_id) REFERENCES `users` (`id`);

ALTER TABLE movies DROP FOREIGN KEY movies_worker_id_foreign;
ALTER TABLE movies ADD FOREIGN KEY (worker_id) REFERENCES `users` (`id`);

ALTER TABLE broadcasts DROP FOREIGN KEY broadcasts_writer_user_id_foreign;
ALTER TABLE broadcasts ADD FOREIGN KEY (writer_user_id) REFERENCES `users` (`id`);

ALTER TABLE users MODIFY bv_member VARCHAR (191) NOT NULL;
UPDATE users SET bv_member = 'gemeldet' where bv_member = '1';
UPDATE users SET bv_member = '' where bv_member = '0';

ALTER TABLE groups ADD orderN INT NOT NULL DEFAULT 0;
ALTER TABLE subgroups ADD orderN INT NOT NULL DEFAULT 0;