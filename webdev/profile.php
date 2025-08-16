<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
    exit;
}
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

$userId = $_SESSION['userId'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['Full_Name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_Number'] ?? '');
    $address = trim($_POST['Address'] ?? '');
    $profile_image = trim($_POST['profile_Image'] ?? '');
    $password = $_POST['Password'] ?? '';
    $updatePassword = !empty($password);

    if ($full_name && $email) {
        if ($updatePassword) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=?, Password=? WHERE userId=?');
            $stmt->bind_param('ssssssi', $full_name, $email, $phone, $address, $profile_image, $hashedPassword, $userId);
        } else {
            $stmt = $conn->prepare('UPDATE users SET Full_Name=?, email=?, phone_Number=?, Address=?, profile_Image=? WHERE userId=?');
            $stmt->bind_param('sssssi', $full_name, $email, $phone, $address, $profile_image, $userId);
        }
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Update failed: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error = 'Full Name and Email are required.';
    }
}

$stmt = $conn->prepare('SELECT User_Name, Full_Name, email, phone_Number, Address, profile_Image FROM users WHERE userId=?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($User_Name, $Full_Name, $email, $phone, $address, $profile_image);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Update My Profile</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success" style="background:#43a047;color:#fff;padding:10px;border-radius:4px;margin-bottom:18px;text-align:center;">Profile updated successfully!</div><?php endif; ?>
    <form method="post" action="">
        <label>Username (cannot change):</label>
        <input type="text" value="<?php echo htmlspecialchars($User_Name); ?>" disabled>
        <label>Full Name:</label>
        <input type="text" name="Full_Name" value="<?php echo htmlspecialchars($Full_Name); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <label>Phone Number:</label>
        <input type="text" name="phone_Number" value="<?php echo htmlspecialchars($phone); ?>">
        <label>Address:</label>
        <input type="text" name="Address" value="<?php echo htmlspecialchars($address); ?>">
        <label>Profile Image URL:</label>
        <input type="text" name="profile_Image" value="<?php echo htmlspecialchars($profile_image); ?>">
        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="Password">
        <button type="submit">Update Profile</button>
    </form>
    <br>
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
