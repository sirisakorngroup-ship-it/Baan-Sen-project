CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    purchase_date DATE NOT NULL
);
