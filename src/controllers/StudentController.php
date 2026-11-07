<?php
class StudentController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function viewDashboard($studentId) {
        // Logic to retrieve student tasks and submissions
        $tasks = $this->getTasks($studentId);
        // Pastikan path ini sesuai dengan struktur folder Anda
        include __DIR__ . '/../views/student/dashboard.php';
    }

    public function getTasks($studentId) {
        // Logic to fetch tasks from the database
        $query = "SELECT * FROM tasks WHERE student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentData($studentId) {
        // Contoh: ambil data user dari tabel users
        $query = "SELECT * FROM users WHERE id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function uploadSubmission($taskId, $file) {
        $targetDir = __DIR__ . "/../../uploads/";
        $targetFile = $targetDir . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $query = "INSERT INTO submissions (task_id, file_path) VALUES (:task_id, :file_path)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':file_path', $targetFile);
            $stmt->execute();
            return true;
        }
        return false;
    }

    public function deleteSubmission($submissionId) {
        $query = "DELETE FROM submissions WHERE id = :submission_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':submission_id', $submissionId);
        return $stmt->execute();
    }

    public function updateProfile($studentId, $username, $password) {
        $query = "UPDATE users SET username = :username, password = :password WHERE id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
        $stmt->bindParam(':student_id', $studentId);
        return $stmt->execute();
    }
}