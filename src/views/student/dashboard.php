<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Student Dashboard</title>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <div class="profile-menu">
            <h2>Profile Menu</h2>
            <ul>
                <li><a href="/student/profile.php">Change Username/Password</a></li>
            </ul>
        </div>

        <div class="task-list">
            <h2>Your Tasks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch tasks from the database
                    // Assuming $tasks is an array of task objects fetched from the database
                    foreach ($tasks as $task) {
                        echo "<tr>
                                <td>" . htmlspecialchars($task->title) . "</td>
                                <td>" . htmlspecialchars($task->deadline) . "</td>
                                <td>
                                    <a href='/upload.php?task_id=" . htmlspecialchars($task->id) . "'>Upload Assignment</a>
                                    <a href='/delete.php?task_id=" . htmlspecialchars($task->id) . "'>Delete Assignment</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>
</body>
</html>