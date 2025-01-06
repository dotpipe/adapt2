<?php
require_once 'classes/Database.php';

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
?>
