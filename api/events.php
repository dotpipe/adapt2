<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class EventHandler
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function getPopularItemsForEvent($eventType, $startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            SELECT item_name, SUM(search_count) AS total_searches 
            FROM event_items 
            JOIN events ON event_items.event_id = events.id 
            WHERE event_type = ? AND event_date BETWEEN ? AND ?
            GROUP BY item_name
            ORDER BY total_searches DESC
            LIMIT 10");
        $stmt->execute([$eventType, $startDate, $endDate]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$eventHandler = new EventHandler($db);

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the required fields are present
if (isset($data['eventType'], $data['startDate'], $data['endDate'])) {
    $result = $eventHandler->getPopularItemsForEvent(
        $data['eventType'],
        $data['startDate'],
        $data['endDate']
    );
    echo json_encode($result);
} else {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
}

?>
