<?php
session_start();
require_once '../../config/database.php';
require_once '../../controllers/LecturerController.php';

$lecturerController = new LecturerController();
$tasks = $lecturerController->getAssignedTasks();
$submissions = $lecturerController->getStudentSubmissions();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Lecturer Dashboard</title>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="dashboard">
        <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
        
        <div class="profile-menu">
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>

        <h2>Assigned Tasks</h2>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <?php echo $task['title']; ?> - Deadline: <?php echo $task['deadline']; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Student Submissions</h2>
        <ul>
            <?php foreach ($submissions as $submission): ?>
                <li>
                    Student: <?php echo $submission['student_name']; ?> - Task: <?php echo $submission['task_title']; ?> - Submitted on: <?php echo $submission['submission_date']; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="assign-task">
            <h2>Assign New Task</h2>
            <form action="../../controllers/LecturerController.php" method="POST">
                <input type="text" name="task_title" placeholder="Task Title" required>
                <input type="date" name="deadline" required>
                <button type="submit" name="assign_task">Assign Task</button>
            </form>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>
</body>
</html>