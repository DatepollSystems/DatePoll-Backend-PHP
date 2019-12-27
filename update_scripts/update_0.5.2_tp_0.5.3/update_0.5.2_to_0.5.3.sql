USE homestead;

ALTER TABLE user_tokens DROP INDEX user_tokens_token_unique;

ALTER TABLE events
    DROP COLUMN location;

ALTER TABLE events
    DROP COLUMN startDate;

ALTER TABLE events
    DROP COLUMN endDate;