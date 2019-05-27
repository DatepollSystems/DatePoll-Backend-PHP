USE datepoll;
ALTER TABLE users DROP email;
ALTER TABLE users ADD username VARCHAR(191) NOT NULL;