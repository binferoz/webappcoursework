<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
    exit;
}
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare('SELECT a.*, u.Full_Name FROM articles a JOIN users u ON a.authorId=u.userId WHERE a.article_display="yes" ORDER BY a.article_created_date DESC LIMIT 6');
$stmt->execute();
$result = $stmt->get_result();
$articles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Articles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Latest Articles</h2>
    <?php if (empty($articles)): ?>
        <p>No articles found.</p>
    <?php else: ?>
        <?php foreach ($articles as $art): ?>
            <div style="border:1px solid #eee;padding:16px;margin-bottom:18px;border-radius:6px;">
                <h3><?php echo htmlspecialchars($art['article_title']); ?></h3>
                <div style="color:#888;font-size:13px;">By <?php echo htmlspecialchars($art['Full_Name']); ?> | <?php echo htmlspecialchars($art['article_created_date']); ?></div>
                <div style="margin:12px 0;"> <?php echo nl2br(htmlspecialchars($art['article_full_text'])); ?> </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="<?php 
        switch ($_SESSION['UserType']) {
            case 'Super_User': echo 'dashboard_super_user.php'; break;
            case 'Administrator': echo 'dashboard_admin.php'; break;
            case 'Author': echo 'dashboard_author.php'; break;
        }
    ?>" class="button">Back to Dashboard</a>
</div>
</body>
</html>
