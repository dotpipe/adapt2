<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Registration {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function register($username, $password) {
        if (empty($username) || empty($password)) {
            echo json_encode([ 'message' => "Registration unsuccessful.", 'success' => false ]);
            return "Username and password cannot be empty.";
        }

        if ($this->userExists($username)) {
            echo json_encode([ 'message' => "Registration unsuccessful. User Exists", 'success' => false ]);
            return "Username already taken.";
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hashedPassword])) {
            echo json_encode([ 'message' => "Registration successful.", 'success' => true ]);
            return 1;
        }
        } catch (Exception $e) {
                echo json_encode([ 'message' => "Registration unsuccessful. " . $e, 'success' => false ]);
                return 0;
        }
    }

    private function userExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$registration = new Registration($db);

$registration->register($username, $password);

?>