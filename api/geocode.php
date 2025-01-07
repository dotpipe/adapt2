<?php
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class Geocode
{
    const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY';

    public static function getCoordinates($address)
    {
        // Google Maps Geocoding API URL
        $url = 'https://maps.googleapis.com/maps./api/geocode/json?address=' . urlencode($address) . '&key=' . self::GOOGLE_MAPS_API_KEY;

        // Send the GET request
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        // Check if the response is valid
        if (isset($data['results'][0])) {
            $location = $data['results'][0]['geometry']['location'];
            return ['latitude' => $location['lat'], 'longitude' => $location['lng']];
        } else {
            return null;
        }
    }
}

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the address is provided
if (isset($data['address'])) {
    $coordinates = Geocode::getCoordinates($data['address']);
    if ($coordinates) {
        echo json_encode($coordinates);
    } else {
        echo json_encode(["status" => "error", "message" => "Unable to geocode the address"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing address"]);
}
?>
