# Coursework System - README

## Setup Instructions

### 1. Requirements
- XAMPP (Apache + MySQL)
- PHP 7.4+

### 2. Database Setup
1. Start Apache and MySQL using XAMPP Control Panel.
2. Open phpMyAdmin at http://localhost/phpmyadmin/
3. Import the provided `coursework_db.sql` file to create the database and tables.

### 3. Creating the First Super_User
Since registration is not public, you must manually insert a Super_User:

1. Generate a password hash using PHP. Example code:
   ```php
   <?php echo password_hash('yourpassword', PASSWORD_DEFAULT); ?>
   ```
   Replace `yourpassword` with your desired password.
2. Run the following SQL in phpMyAdmin (replace the hash and details):
   ```sql
   INSERT INTO users (Full_Name, email, phone_Number, User_Name, Password, UserType, AccessTime, profile_Image, Address)
   VALUES ('Super User', 'superuser@email.com', '123456789', 'superuser', '$2y$10$...', 'Super_User', NOW(), '', 'Main Office');
   ```

### 4. Using the System
- Go to http://localhost/webdev/index.php
- Log in as Super_User and add Administrators and Authors.
- Each user type will see their respective dashboard and permissions.

### 5. Features
- Secure login and role-based dashboards
- Profile update for all users
- Super_User manages all users (except self)
- Administrator manages only Authors
- Author manages only own articles
- CRUD for users and articles
- View last 6 articles for all roles

### 6. Optional
- Email notification to admins on new article post (not implemented by default)

### 7. Security
- All pages except index.php are protected by session authentication
- Passwords are hashed using PHP's password_hash

---

## File List
- `index.php` - Login page
- `dashboard_super_user.php`, `dashboard_admin.php`, `dashboard_author.php` - Dashboards
- `manage_users.php` - Super_User & Admin user management
- `manage_authors.php` - Admin author management
- `manage_my_articles.php` - Article management (Admin/Author)
- `view_articles.php` - View last 6 articles
- `profile.php` - Update profile
- `logout.php` - Log out
- `constant.php` - DB connection constants
- `connection.php` - OOP DB connection
- `styles.css` - Styling
- `coursework_db.sql` - Database schema

