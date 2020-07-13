USE homestead;

ALTER TABLE users DROP member_number;

ALTER TABLE users ADD member_number VARCHAR(191) DEFAULT null;