<?php
require_once 'connection.php';
$db = new Database();
$conn = $db->getConnection();

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
    $userType = 'Author';

    if ($full_name && $email && $username && $password) {

        $stmt = $conn->prepare('SELECT userId FROM users WHERE User_Name=? OR email=?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (Full_Name, email, phone_Number, Address, profile_Image, User_Name, Password, UserType) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssssss', $full_name, $email, $phone, $address, $profile_image, $username, $hashedPassword, $userType);
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Account</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Register Account</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success" style="background:#43a047;color:#fff;padding:10px;border-radius:4px;margin-bottom:18px;text-align:center;"> <?php echo $success; ?> </div><?php endif; ?>
    <form method="post" action="">
        <label>Full Name:</label>
        <input type="text" name="Full_Name" required>
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Phone Number:</label>
        <input type="text" name="phone_Number">
        <label>Address:</label>
        <input type="text" name="Address">
        <label>Profile Image URL:</label>
        <input type="text" name="profile_Image">
        <label>Username:</label>
        <input type="text" name="User_Name" required>
        <label>Password:</label>
        <input type="password" name="Password" required>
        <input type="hidden" name="UserType" value="Author">
        <button type="submit">Register</button>
    </form>
    <br>
    <a href="index.php" class="button">Back to Login</a>
</div>
</body>
</html>
