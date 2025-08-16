<?php
session_start();
if (!isset($_SESSION['userId']) || !in_array($_SESSION['UserType'], ['Super_User','Administrator'])) {
    header('Location: index.php');
    exit;
}
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

$isSuperUser = ($_SESSION['UserType'] === 'Super_User');

if (isset($_GET['delete']) && $isSuperUser) {
    $deleteId = intval($_GET['delete']);
    if ($deleteId !== $_SESSION['userId']) {
        $stmt = $conn->prepare('DELETE FROM users WHERE userId=?');
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_users.php');
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
    $userType = $_POST['UserType'] ?? 'Administrator';
    $editId = intval($_POST['editId'] ?? 0);
    if ($full_name && $email && $username && ($password || $editId)) {
        if ($editId) {
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=?, Password=?, UserType=? WHERE userId=?');
                $stmt->bind_param('sssssssi', $full_name, $email, $phone, $address, $profile_image, $hashedPassword, $userType, $editId);
            } else {
                $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=?, UserType=? WHERE userId=?');
                $stmt->bind_param('ssssssi', $full_name, $email, $phone, $address, $profile_image, $userType, $editId);
            }
            $stmt->execute();
            $stmt->close();
            $success = 'User updated.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (Full_Name, email, phone_Number, Address, profile_Image, User_Name, Password, UserType) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssssss', $full_name, $email, $phone, $address, $profile_image, $username, $hashedPassword, $userType);
            $stmt->execute();
            $stmt->close();
            $success = 'User added.';
        }
    } else {
        $error = 'All fields except password (on update) are required.';
    }
}

if ($isSuperUser) {
    $stmt = $conn->prepare('SELECT userId, Full_Name, email, User_Name, UserType FROM users WHERE userId != ?');
    $stmt->bind_param('i', $_SESSION['userId']);
} else {
    $stmt = $conn->prepare("SELECT userId, Full_Name, email, User_Name, UserType FROM users WHERE UserType = 'Author'");
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$editUser = null;
if (isset($_GET['edit']) && $isSuperUser) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM users WHERE userId=?');
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
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
    <style>.button {margin-right:8px;}</style>
</head>
<body>
<div class="container">
    <h2><?php echo $isSuperUser ? 'Manage Other Users' : 'Manage Authors'; ?></h2>
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
        <label>User Type:</label>
        <select name="UserType" <?php if(!$isSuperUser) echo 'disabled'; ?>>
            <?php if ($isSuperUser): ?>
                <option value="Administrator" <?php if(($editUser['UserType'] ?? '')==='Administrator') echo 'selected'; ?>>Administrator</option>
                <option value="Author" <?php if(($editUser['UserType'] ?? '')==='Author') echo 'selected'; ?>>Author</option>
            <?php else: ?>
                <option value="Author" selected>Author</option>
            <?php endif; ?>
        </select>
        <button type="submit"><?php echo $editUser ? 'Update User' : 'Add User'; ?></button>
    </form>
    <h3>All Users</h3>
    <table border="1" cellpadding="6" style="width:100%;margin-top:12px;">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>UserType</th><th>Actions</th></tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['userId']; ?></td>
                <td><?php echo htmlspecialchars($u['Full_Name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['User_Name']); ?></td>
                <td><?php echo htmlspecialchars($u['UserType']); ?></td>
                <td>
                    <a href="manage_users.php?edit=<?php echo $u['userId']; ?>" class="button">Edit</a>
                    <?php if ($isSuperUser): ?>
                        <a href="manage_users.php?delete=<?php echo $u['userId']; ?>" class="button" onclick="return confirm('Delete user?');">Delete</a>
                    <?php elseif ($u['UserType']==='Author'): ?>
                        <a href="manage_users.php?delete=<?php echo $u['userId']; ?>" class="button" onclick="return confirm('Delete author?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="<?php echo $isSuperUser ? 'dashboard_super_user.php' : 'dashboard_admin.php'; ?>" class="button">Back to Dashboard</a>
</div>
</body>
</html>
