<?php

class Submission {
    private $conn;
    private $table_name = "submissions";

    public $id;
    public $student_id;
    public $task_id;
    public $submission_date;
    public $file_path;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (student_id, task_id, submission_date, file_path, status) VALUES (:student_id, :task_id, :submission_date, :file_path, :status)";
        
        $stmt = $this->conn->prepare($query);

        $this->submission_date = date('Y-m-d H:i:s');

        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':task_id', $this->task_id);
        $stmt->bindParam(':submission_date', $this->submission_date);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->execute();
        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
