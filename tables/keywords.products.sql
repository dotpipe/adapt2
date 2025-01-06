CREATE TABLE inventory (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL,
    FOREIGN KEY (store_id) REFERENCES stores(id)
);

CREATE TABLE keywords (
    keyword_id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE product_keywords (
    product_id INT,
    keyword_id INT,
    PRIMARY KEY (product_id, keyword_id),
    FOREIGN KEY (product_id) REFERENCES inventory(product_id),
    FOREIGN KEY (keyword_id) REFERENCES keywords(keyword_id)
);
