<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class StoreReference {
    private $pdo;
    private $validTokens = ['token1', 'token2']; // Predefined valid tokens, you can adjust this.

    // Constructor to initialize the PDO connection
    public function __construct($db) {
        $this->pdo = $db;
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
    public function addStore($brand, $region, $area, $location, $address, $store_id, $open, $hours, $zipcode) {
        if (!$this->checkToken()) {
            return ["status" => "error", "message" => "Unauthorized: Invalid Token"];
        }
	try {
	        $sql = "INSERT INTO stores (brand, region, area, location, store_id, address, open, hours, zipcode) 
	                VALUES (:brand, :region, :area, :location, :store_id, :address, :open, :hours, :zipcode)";
	        $stmt = $this->pdo->prepare($sql);
	        $stmt->execute([
	            ':brand' => $brand,
	            ':region' => $region,
	            ':area' => $area,
	            ':location' => $location,
	            ':address' => $address,
	            ':store_id' => $store_id,
	            ':open' => $open,
		    ':hours' => $hours,
		    ':zipcode' => $zipcode
	        ]);

		return ["status" => "success", "message" => "Store added successfully"];
	} catch (EXCEPTION $e) {
		return ["message" => $e, "status" => "error"];
	}
    }

    // Method to get store references based on filters
    public function getStores($filters = []) {
        if (!$this->checkToken()) {
            return ["status" => "error", "message" => "Unauthorized: Invalid Token"];
        }

        $sql = "SELECT * FROM stores WHERE";
	if (count($filters) > 0)
	    $sql .= " 1";
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
        if (isset($filters['location'])) {
            $sql .= " AND location = :location";
        }
        if (isset($filters['address'])) {
            $sql .= " AND address = :address";
        }
        if (isset($filters['store_id'])) {
            $sql .= " AND store_id = :store_id";
        }
        if (isset($filters['open'])) {
            $sql .= " AND open = :open";
        }
        if (isset($filters['hours'])) {
            $sql .= " AND hours = :hours";
        }
        if (isset($filters['zipcode'])) {
            $sql .= " AND zipcode = :zipcode";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filters);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stores;
    }

    // Method to update a store's details
    public function updateStore($brand, $region, $area, $location, $address, $store_id, $open, $hours, $zipcode) {
        if (!$this->checkToken()) {
            return ["status" => "error", "message" => "Unauthorized: Invalid Token"];
        }

        $sql = "UPDATE stores SET 
                brand = :brand, 
                region = :region, 
                area = :area, 
                location = :location, 
		address = :address,
		store_id = :store_id,
		open = :open,
                hours = :hours,
                zipcode = :zipcode 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':brand' => $brand,
            ':region' => $region,
            ':area' => $area,
            ':location' => $location,
            ':address' => $address,
            ':brand' => $store_id,
            ':region' => $open,
            ':area' => $hours,
	    ':zipcode' => $zipcode
        ]);
        return ["status" => "success", "message" => "Store updated successfully"];
    }
    // Method to delete a store reference
    public function deleteStore($storeId) {
        if (!$this->checkToken()) {
            return ["status" => "error", "message" => "Unauthorized: Invalid Token"];
        }

        $sql = "DELETE FROM stores WHERE id = :store_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':store_id' => $storeId]);
        return ["status" => "success", "message" => "Store deleted successfully"];
    }
}

// Now you can use $db for database operations

$storeReference = new StoreReference($db);

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the required fields are present
if (isset($data['brand'], $data['region'], $data['area'], $data['location'], $data['address'], $data['store_id'], $data['open'], $data['hours'], $data['zipcode'])) {
    $result = $storeReference->addStore(
	$data['brand'], $data['region'], $data['area'], $data['location'], $data['address'], $data['store_id'], $data['open'], $data['hours'], $data['zipcode']
    );
    echo json_encode($result);
} else {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
}
?>
