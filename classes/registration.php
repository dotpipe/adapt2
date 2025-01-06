<?php

class Registration {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function register($username, $password) {
        if (empty($username) || empty($password)) {
            return "Username and password cannot be empty.";
        }

        if ($this->userExists($username)) {
            return "Username already taken.";
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hashedPassword])) {
            return "Registration successful.";
        }

        return "Registration failed.";
    }

    private function userExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
}
?>
