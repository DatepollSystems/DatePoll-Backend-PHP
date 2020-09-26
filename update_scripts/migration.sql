# Migrations are handled via the datepoll-update-db command.
# Execute it with docker-compose exec datepoll-php php /backend/artisan datepoll-update-db
#
# THIS IS ONLY FOR OVERVIEW reasons! Only execute this MySQL command if you know what you are doing!

# 0.1.1 to 0.2.0
# -------------------------------------------------------------------------------------------
# First migration
ALTER TABLE users
    DROP email;
ALTER TABLE users
    ADD username VARCHAR(191) NOT NULL;
# Second migration
ALTER TABLE users
    ADD UNIQUE (username);

# 0.3.1 to 0.4.0
# -------------------------------------------------------------------------------------------
-- Add location field to events
ALTER TABLE events
    ADD location VARCHAR(191);

-- Add showInCalendar field to standard decisions and set true as default value
ALTER TABLE events_standard_decisions
    ADD showInCalendar TINYINT(1) NOT NULL;
UPDATE events_standard_decisions
SET showInCalendar = 1;

-- Add showInCalendar field to decisions and set true as default value
ALTER TABLE events_decisions
    ADD showInCalendar TINYINT(1) NOT NULL;
UPDATE events_decisions
SET showInCalendar = 1;

# 0.5.1 to 0.5.2
# -------------------------------------------------------------------------------------------
ALTER TABLE events_users_voted_for
    ADD additionalInformation VARCHAR(128);

# 0.5.2 to 0.5.3
# -------------------------------------------------------------------------------------------
ALTER TABLE user_tokens
    DROP INDEX user_tokens_token_unique;

ALTER TABLE events
    DROP COLUMN location;

ALTER TABLE events
    DROP COLUMN startDate;

ALTER TABLE events
    DROP COLUMN endDate;

# -------------------------------------------------------------------------------------------
# This is where we changed to a versioned database schema
# 2 to 3
# -------------------------------------------------------------------------------------------
DROP TABLE jobs;

ALTER TABLE users
    ADD internal_comment TEXT NULL;
ALTER TABLE users
    ADD information_denied TINYINT DEFAULT 0 NOT NULL;
ALTER TABLE users
    ADD member_number INTEGER DEFAULT NULL;
ALTER TABLE users
    ADD bv_member TINYINT DEFAULT 0 NOT NULL;

# 3 to 4
# -------------------------------------------------------------------------------------------
ALTER TABLE users
    DROP member_number;

ALTER TABLE users
    ADD member_number VARCHAR(191) DEFAULT NULL;

# 4 to 5
# -------------------------------------------------------------------------------------------
ALTER TABLE movies
    DROP FOREIGN KEY movies_emergency_worker_id_foreign;
ALTER TABLE movies
    ADD FOREIGN KEY (emergency_worker_id) REFERENCES `users` (`id`);

ALTER TABLE movies
    DROP FOREIGN KEY movies_worker_id_foreign;
ALTER TABLE movies
    ADD FOREIGN KEY (worker_id) REFERENCES `users` (`id`);

ALTER TABLE broadcasts
    DROP FOREIGN KEY broadcasts_writer_user_id_foreign;
ALTER TABLE broadcasts
    ADD FOREIGN KEY (writer_user_id) REFERENCES `users` (`id`);

ALTER TABLE users
    MODIFY bv_member VARCHAR(191) NOT NULL;
UPDATE users
SET bv_member = 'gemeldet'
where bv_member = '1';
UPDATE users
SET bv_member = ''
where bv_member = '0';

ALTER TABLE groups
    ADD orderN INT NOT NULL DEFAULT 0;
ALTER TABLE subgroups
    ADD orderN INT NOT NULL DEFAULT 0;

# 5 to 6
# -------------------------------------------------------------------------------------------
ALTER TABLE logs
    ADD COLUMN user_id INT UNSIGNED;
ALTER TABLE logs
    ADD FOREIGN KEY (user_id) REFERENCES `users` (`id`);
