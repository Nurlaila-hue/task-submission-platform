<?php

class LecturerController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function assignTask($taskData) {
        // Logic to assign a new task to students
        $query = "INSERT INTO tasks (title, description, deadline, lecturer_id) VALUES (:title, :description, :deadline, :lecturer_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $taskData['title']);
        $stmt->bindParam(':description', $taskData['description']);
        $stmt->bindParam(':deadline', $taskData['deadline']);
        $stmt->bindParam(':lecturer_id', $taskData['lecturer_id']);
        return $stmt->execute();
    }

    public function viewSubmissions($taskId) {
        // Logic to view submissions for a specific task
        $query = "SELECT * FROM submissions WHERE task_id = :task_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':task_id', $taskId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLecturerProfile($lecturerId) {
        // Logic to get lecturer profile information
        $query = "SELECT * FROM users WHERE id = :lecturer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturerId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($studentId, $username, $password) {
    $query = "UPDATE users SET username = :username, password = :password WHERE id = :student_id";
    $stmt = $this->db->prepare($query);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

    return $stmt->execute();
    }
}