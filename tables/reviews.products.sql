CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL, -- Product being reviewed (foreign key to products table)
    user_id INT NOT NULL, -- User who reviewed
    rating INT NOT NULL, -- Rating from 1-5
    review_text TEXT, -- Text review
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE product_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL, -- Recipe that the feedback applies to
    product_id INT NOT NULL, -- Product reviewed
    user_id INT NOT NULL, -- User who gave the feedback
    feedback_type ENUM('suggest', 'avoid') NOT NULL, -- Whether to suggest or avoid
    reason TEXT, -- Reason for suggestion/avoidance
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
