CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_type VARCHAR(50) NOT NULL,  -- e.g., "Holiday", "Custom Event", etc.
    created_by INT, -- User ID of the creator
    event_date DATE, -- The date or date range for the event
    description TEXT
);

CREATE TABLE event_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT, -- Event ID this item is linked to
    item_name VARCHAR(255) NOT NULL,
    search_count INT DEFAULT 0, -- How many times this item is searched for
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE user_event_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    feedback TEXT, -- Community feedback, ideas, or recipes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);
