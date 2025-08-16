<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['UserType'] !== 'Author') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Author Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Author</h2>
        <a href="profile.php" class="button">Update My Profile</a>
        <a href="manage_my_articles.php" class="button">Manage My Articles</a>
        <a href="view_articles.php" class="button">View Articles</a>
        <a href="logout.php" class="button">Logout</a>
    </div>
</body>
</html>
