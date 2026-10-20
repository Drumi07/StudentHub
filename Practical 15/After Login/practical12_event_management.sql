USE studenthub_db;

-- Practical 12: add fields needed for event descriptions and poster filenames.
ALTER TABLE events ADD COLUMN description TEXT NULL AFTER event_date;
ALTER TABLE events ADD COLUMN poster VARCHAR(255) NULL AFTER description;
