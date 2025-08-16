CREATE DATABASE coursework_db;


CREATE TABLE IF NOT EXISTS users (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_Number VARCHAR(20),
    User_Name VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    UserType ENUM('Super_User', 'Administrator', 'Author') NOT NULL,
    AccessTime DATETIME,
    profile_Image VARCHAR(255),
    Address VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS articles (
    articleId INT AUTO_INCREMENT PRIMARY KEY,
    authorId INT NOT NULL,
    article_title VARCHAR(255) NOT NULL,
    article_full_text TEXT NOT NULL,
    article_created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    article_last_update DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    article_display ENUM('yes','no') DEFAULT 'yes',
    article_order INT DEFAULT 0,
    FOREIGN KEY (authorId) REFERENCES users(userId) ON DELETE CASCADE
);
