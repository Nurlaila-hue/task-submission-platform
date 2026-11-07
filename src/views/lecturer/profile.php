<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'lecturer') {
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

    header('Location: lecturer_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Lecturer Profile</title>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
        <form action="" method="POST">
            <div>
                <label for="username">Change Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>
            <div>
                <label for="password">Change Password:</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </div>
    <?php include '../partials/footer.php'; ?>
</body>
</html>