CREATE TABLE สมัครสมาชิก (
    id INT AUTO_INCREMENT PRIMARY KEY,
    User_id VARCHAR(100),
    Fullname VARCHAR(100),
    Birthday VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
