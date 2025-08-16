<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['UserType'] !== 'Super_User') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Super User</h2>
        <a href="profile.php" class="button">Update My Profile</a>
        <a href="manage_users.php" class="button">Manage Other Users</a>
        <a href="view_articles.php" class="button">View Articles</a>
        <a href="logout.php" class="button">Logout</a>
    </div>
</body>
</html>
