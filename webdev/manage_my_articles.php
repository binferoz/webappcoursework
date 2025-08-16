<?php
session_start();
if (!isset($_SESSION['userId']) || !in_array($_SESSION['UserType'], ['Administrator','Author'])) {
    header('Location: index.php');
    exit;
}
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

$userId = $_SESSION['userId'];
$isAdmin = ($_SESSION['UserType'] === 'Administrator');

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    if ($isAdmin) {
        $stmt = $conn->prepare('DELETE FROM articles WHERE articleId=?');
        $stmt->bind_param('i', $deleteId);
    } else {
        $stmt = $conn->prepare('DELETE FROM articles WHERE articleId=? AND authorId=?');
        $stmt->bind_param('ii', $deleteId, $userId);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: manage_my_articles.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['article_title'] ?? '');
    $text = trim($_POST['article_full_text'] ?? '');
    $display = $_POST['article_display'] ?? 'yes';
    $order = intval($_POST['article_order'] ?? 0);
    $editId = intval($_POST['editId'] ?? 0);
    $author = $isAdmin ? intval($_POST['authorId'] ?? $userId) : $userId;
    if ($title && $text) {
        if ($editId) {
            if ($isAdmin) {
                $stmt = $conn->prepare('UPDATE articles SET article_title=?, article_full_text=?, article_display=?, article_order=? WHERE articleId=?');
                $stmt->bind_param('sssii', $title, $text, $display, $order, $editId);
            } else {
                $stmt = $conn->prepare('UPDATE articles SET article_title=?, article_full_text=?, article_display=?, article_order=? WHERE articleId=? AND authorId=?');
                $stmt->bind_param('sssiis', $title, $text, $display, $order, $editId, $userId);
            }
            $stmt->execute();
            $stmt->close();
            $success = 'Article updated.';
        } else {
            $stmt = $conn->prepare('INSERT INTO articles (authorId, article_title, article_full_text, article_display, article_order) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('isssi', $author, $title, $text, $display, $order);
            $stmt->execute();
            $stmt->close();
            $success = 'Article added.';
        }
    } else {
        $error = 'Title and full text are required.';
    }
}

if ($isAdmin) {
    $stmt = $conn->prepare('SELECT a.*, u.Full_Name FROM articles a JOIN users u ON a.authorId=u.userId ORDER BY a.article_created_date DESC');
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $authors = $conn->query("SELECT userId, Full_Name FROM users WHERE UserType='Author'")->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conn->prepare('SELECT a.*, u.Full_Name FROM articles a JOIN users u ON a.authorId=u.userId WHERE a.authorId=? ORDER BY a.article_created_date DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$editArticle = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($isAdmin) {
        $stmt = $conn->prepare('SELECT * FROM articles WHERE articleId=?');
        $stmt->bind_param('i', $editId);
    } else {
        $stmt = $conn->prepare('SELECT * FROM articles WHERE articleId=? AND authorId=?');
        $stmt->bind_param('ii', $editId, $userId);
    }
    $stmt->execute();
    $editArticle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Articles</title>
    <link rel="stylesheet" href="styles.css">
    <style>.button {margin-right:8px;}</style>
</head>
<body>
<div class="container">
    <h2>Manage Articles</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success" style="background:#43a047;color:#fff;padding:10px;border-radius:4px;margin-bottom:18px;text-align:center;"> <?php echo $success; ?> </div><?php endif; ?>
    <form method="post" action="">
        <?php if ($editArticle): ?><input type="hidden" name="editId" value="<?php echo $editArticle['articleId']; ?>"><?php endif; ?>
        <?php if ($isAdmin): ?>
            <label>Author:</label>
            <select name="authorId">
                <?php foreach ($authors as $a): ?>
                    <option value="<?php echo $a['userId']; ?>" <?php if(($editArticle['authorId'] ?? $userId)==$a['userId']) echo 'selected'; ?>><?php echo htmlspecialchars($a['Full_Name']); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <label>Title:</label>
        <input type="text" name="article_title" value="<?php echo htmlspecialchars($editArticle['article_title'] ?? ''); ?>" required>
        <label>Full Text:</label>
        <textarea name="article_full_text" rows="6" required><?php echo htmlspecialchars($editArticle['article_full_text'] ?? ''); ?></textarea>
        <label>Display:</label>
        <select name="article_display">
            <option value="yes" <?php if(($editArticle['article_display'] ?? '')==='yes') echo 'selected'; ?>>Yes</option>
            <option value="no" <?php if(($editArticle['article_display'] ?? '')==='no') echo 'selected'; ?>>No</option>
        </select>
        <label>Order:</label>
        <input type="number" name="article_order" value="<?php echo htmlspecialchars($editArticle['article_order'] ?? 0); ?>">
        <button type="submit"><?php echo $editArticle ? 'Update Article' : 'Add Article'; ?></button>
    </form>
    <h3>All Articles</h3>
    <table border="1" cellpadding="6" style="width:100%;margin-top:12px;">
        <tr><th>ID</th><th>Title</th><th>Author</th><th>Display</th><th>Order</th><th>Created</th><th>Actions</th></tr>
        <?php foreach ($articles as $art): ?>
            <tr>
                <td><?php echo $art['articleId']; ?></td>
                <td><?php echo htmlspecialchars($art['article_title']); ?></td>
                <td><?php echo htmlspecialchars($art['Full_Name']); ?></td>
                <td><?php echo htmlspecialchars($art['article_display']); ?></td>
                <td><?php echo htmlspecialchars($art['article_order']); ?></td>
                <td><?php echo htmlspecialchars($art['article_created_date']); ?></td>
                <td>
                    <a href="manage_my_articles.php?edit=<?php echo $art['articleId']; ?>" class="button">Edit</a>
                    <a href="manage_my_articles.php?delete=<?php echo $art['articleId']; ?>" class="button" onclick="return confirm('Delete article?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="<?php echo $isAdmin ? 'dashboard_admin.php' : 'dashboard_author.php'; ?>" class="button">Back to Dashboard</a>
</div>
</body>
</html>
