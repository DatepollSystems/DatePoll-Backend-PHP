USE homestead;

DROP TABLE jobs;

ALTER TABLE users ADD internal_comment TEXT NULL;
ALTER TABLE users ADD information_denied TINYINT DEFAULT 0 NOT NULL;
ALTER TABLE users ADD member_number INTEGER DEFAULT NULL;
ALTER TABLE users ADD bv_member TINYINT DEFAULT 0 NOT NULL;