<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class StoreReference {
    private $pdo;
    private $validTokens = ['token1', 'token2']; // Predefined valid tokens, you can adjust this.

    // Constructor to initialize the PDO connection
    public function __construct($host, $db, $user) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
    }

    // Method to check the token in the request headers
    private function checkToken() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }

        // Extract the token from the Authorization header
        $token = str_replace("Bearer ", "", $headers['Authorization']);
        
        // Validate the token (this can be changed to any mechanism you want, like JWT)
        if (in_array($token, $this->validTokens)) {
            return true;
        }

        return false;
    }

    // Method to add a new store reference
    public function addStore($brand, $region, $area, $storeName, $zipcode, $address) {
        if (!$this->checkToken()) {
            return $this->jsonResponse(["status" => "error", "message" => "Unauthorized: Invalid Token"]);
        }

        $sql = "INSERT INTO stores (brand, region, area, store_name, zipcode, address) 
                VALUES (:brand, :region, :area, :store_name, :zipcode, :address)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':brand' => $brand,
            ':region' => $region,
            ':area' => $area,
            ':store_name' => $storeName,
            ':zipcode' => $zipcode,
            ':address' => $address
        ]);
        return $this->jsonResponse(["status" => "success", "message" => "Store added successfully"]);
    }

    // Method to get store references based on filters
    public function getStores($filters = []) {
        if (!$this->checkToken()) {
            return $this->jsonResponse(["status" => "error", "message" => "Unauthorized: Invalid Token"]);
        }

        $sql = "SELECT * FROM stores WHERE 1";

        // Apply filters to the SQL query
        if (isset($filters['brand'])) {
            $sql .= " AND brand = :brand";
        }
        if (isset($filters['region'])) {
            $sql .= " AND region = :region";
        }
        if (isset($filters['area'])) {
            $sql .= " AND area = :area";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filters);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->jsonResponse($stores);
    }

    // Method to update a store's details
    public function updateStore($storeId, $brand, $region, $area, $storeName, $zipcode, $address) {
        if (!$this->checkToken()) {
            return $this->jsonResponse(["status" => "error", "message" => "Unauthorized: Invalid Token"]);
        }

        $sql = "UPDATE stores SET 
                brand = :brand, 
                region = :region, 
                area = :area, 
                store_name = :store_name, 
                zipcode = :zipcode, 
                address = :address 
                WHERE store_id = :store_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':brand' => $brand,
            ':region' => $region,
            ':area' => $area,
            ':store_name' => $storeName,
            ':zipcode' => $zipcode,
            ':address' => $address,
            ':store_id' => $storeId
        ]);
        return $this->jsonResponse(["status" => "success", "message" => "Store updated successfully"]);
    }

    // Method to delete a store reference
    public function deleteStore($storeId) {
        if (!$this->checkToken()) {
            return $this->jsonResponse(["status" => "error", "message" => "Unauthorized: Invalid Token"]);
        }

        $sql = "DELETE FROM stores WHERE store_id = :store_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':store_id' => $storeId]);
        return $this->jsonResponse(["status" => "success", "message" => "Store deleted successfully"]);
    }

    // Helper method to return a JSON response
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}

?>
