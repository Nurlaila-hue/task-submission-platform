<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../public/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];

    if (!empty($newUsername)) {
        $updateQuery = "UPDATE users SET username = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $newUsername, $userId);
        $updateStmt->execute();
    }

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
        $updatePasswordStmt = $conn->prepare($updatePasswordQuery);
        $updatePasswordStmt->bind_param("si", $hashedPassword, $userId);
        $updatePasswordStmt->execute();
    }

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Student Profile</title>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <div class="container">
        <h1>Profile</h1>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
            <button type="submit">Update Profile</button>
        </form>
    </div>
    <?php include '../partials/footer.php'; ?>
</body>
</html>