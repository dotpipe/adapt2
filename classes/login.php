<?php
require_once 'classes/Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Login {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return "Username and password cannot be empty.";
        }

        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return "Login successful.";
        }

        return "Invalid username or password.";
    }

    public function logout() {
        session_destroy();
        return "Logged out successfully.";
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>
