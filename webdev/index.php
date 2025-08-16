<?php
session_start();
require_once 'connection.php';

// Redirect if already logged in
if (isset($_SESSION['userId'])) {
    switch ($_SESSION['UserType']) {
        case 'Super_User':
            header('Location: dashboard_super_user.php');
            exit;
        case 'Administrator':
            header('Location: dashboard_admin.php');
            exit;
        case 'Author':
            header('Location: dashboard_author.php');
            exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare('SELECT userId, User_Name, Password, UserType FROM users WHERE User_Name = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $User_Name, $hashedPassword, $UserType);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                $_SESSION['userId'] = $userId;
                $_SESSION['UserType'] = $UserType;
                switch ($UserType) {
                    case 'Super_User':
                        header('Location: dashboard_super_user.php');
                        exit;
                    case 'Administrator':
                        header('Location: dashboard_admin.php');
                        exit;
                    case 'Author':
                        header('Location: dashboard_author.php');
                        exit;
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Sign In</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Sign In</button>
        </form>
        <div style="text-align:center;margin-top:18px;">
            <a href="register.php">Don't have an account? Register here</a>
        </div>
    </div>
</body>
</html>
