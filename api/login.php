<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Login {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            echo json_encode([ 'message' => 'Unsuccessful', 'success' => false]);
            return "Username and password cannot be empty.";
        }

        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['message' => 'Success', 'success' => true]);
            return "Login successful.";
        }
        echo json_encode(['message' => 'Unsuccessful', 'success' => false]);

        return "Invalid username or password.";
    }

    public function logout() {
        session_destroy();

        echo json_encode(['message' => 'Success', 'success' => true]);
        return "Logged out successfully.";
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$login = new Login($db);
$login->login($username, $password);
?>