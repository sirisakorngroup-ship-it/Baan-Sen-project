CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(100),
    fullname VARCHAR(100),
    Birthday VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
