USE datepoll;

-- Add location field to events
ALTER TABLE events ADD location VARCHAR(191);

-- Add showInCalendar field to standard decisions and set true as default value
ALTER TABLE events_standard_decisions ADD showInCalendar TINYINT(1) NOT NULL;
UPDATE events_standard_decisions SET showInCalendar = 1;

-- Add showInCalendar field to decisions and set true as default value
ALTER TABLE events_decisions ADD showInCalendar TINYINT(1) NOT NULL;
UPDATE events_decisions SET showInCalendar = 1;