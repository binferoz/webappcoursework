<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['UserType'] !== 'Administrator') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Administrator</h2>
        <a href="profile.php" class="button">Update My Profile</a>
        <a href="manage_authors.php" class="button">Manage Authors</a>
        <a href="view_articles.php" class="button">View Articles</a>
        <a href="logout.php" class="button">Logout</a>
    </div>
</body>
</html>
