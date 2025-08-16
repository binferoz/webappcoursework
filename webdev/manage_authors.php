<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['UserType'] !== 'Administrator') {
    header('Location: index.php');
    exit;
}
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM users WHERE userId=? AND UserType="Author"');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_authors.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['Full_Name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_Number'] ?? '');
    $address = trim($_POST['Address'] ?? '');
    $profile_image = trim($_POST['profile_Image'] ?? '');
    $username = trim($_POST['User_Name'] ?? '');
    $password = $_POST['Password'] ?? '';
    $editId = intval($_POST['editId'] ?? 0);
    if ($full_name && $email && $username && ($password || $editId)) {
        if ($editId) {
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=?, Password=? WHERE userId=? AND UserType="Author"');
                $stmt->bind_param('ssssssi', $full_name, $email, $phone, $address, $profile_image, $hashedPassword, $editId);
            } else {
                $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=? WHERE userId=? AND UserType="Author"');
                $stmt->bind_param('sssssi', $full_name, $email, $phone, $address, $profile_image, $editId);
            }
            $stmt->execute();
            $stmt->close();
            $success = 'Author updated.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (Full_Name, email, phone_Number, Address, profile_Image, User_Name, Password, UserType) VALUES (?, ?, ?, ?, ?, ?, ?, "Author")');
            $stmt->bind_param('sssssss', $full_name, $email, $phone, $address, $profile_image, $username, $hashedPassword);
            $stmt->execute();
            $stmt->close();
            $success = 'Author added.';
        }
    } else {
        $error = 'All fields except password (on update) are required.';
    }
}
$stmt = $conn->prepare('SELECT userId, Full_Name, email, User_Name FROM users WHERE UserType = "Author"');
$stmt->execute();
$result = $stmt->get_result();
$authors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$editUser = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM users WHERE userId=? AND UserType="Author"');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Authors</title>
    <link rel="stylesheet" href="styles.css">
    <style>.button {margin-right:8px;}</style>
</head>
<body>
<div class="container">
    <h2>Manage Authors</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success" style="background:#43a047;color:#fff;padding:10px;border-radius:4px;margin-bottom:18px;text-align:center;"> <?php echo $success; ?> </div><?php endif; ?>
    <form method="post" action="">
        <?php if ($editUser): ?><input type="hidden" name="editId" value="<?php echo $editUser['userId']; ?>"><?php endif; ?>
        <label>Full Name:</label>
        <input type="text" name="Full_Name" value="<?php echo htmlspecialchars($editUser['Full_Name'] ?? ''); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>" required>
        <label>Phone Number:</label>
        <input type="text" name="phone_Number" value="<?php echo htmlspecialchars($editUser['phone_Number'] ?? ''); ?>">
        <label>Address:</label>
        <input type="text" name="Address" value="<?php echo htmlspecialchars($editUser['Address'] ?? ''); ?>">
        <label>Profile Image URL:</label>
        <input type="text" name="profile_Image" value="<?php echo htmlspecialchars($editUser['profile_Image'] ?? ''); ?>">
        <label>Username:</label>
        <input type="text" name="User_Name" value="<?php echo htmlspecialchars($editUser['User_Name'] ?? ''); ?>" <?php if($editUser) echo 'readonly'; ?> required>
        <label>Password: <?php if($editUser) echo '(leave blank to keep current)'; ?></label>
        <input type="password" name="Password">
        <button type="submit"><?php echo $editUser ? 'Update Author' : 'Add Author'; ?></button>
    </form>
    <h3>All Authors</h3>
    <table border="1" cellpadding="6" style="width:100%;margin-top:12px;">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Actions</th></tr>
        <?php foreach ($authors as $a): ?>
            <tr>
                <td><?php echo $a['userId']; ?></td>
                <td><?php echo htmlspecialchars($a['Full_Name']); ?></td>
                <td><?php echo htmlspecialchars($a['email']); ?></td>
                <td><?php echo htmlspecialchars($a['User_Name']); ?></td>
                <td>
                    <a href="manage_authors.php?edit=<?php echo $a['userId']; ?>" class="button">Edit</a>
                    <a href="manage_authors.php?delete=<?php echo $a['userId']; ?>" class="button" onclick="return confirm('Delete author?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard_admin.php" class="button">Back to Dashboard</a>
</div>
</body>
</html>
