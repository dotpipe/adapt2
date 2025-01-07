<?php
/**
 * Class AnalyticsDashboard
 *
 * This class generates a heatmap and dashboard for daily or weekly hourly feedback activity.
 * It outputs an HTML page optimized for mobile WebView.
 */
require_once './Database.php';

$db = Database::getInstance()->getConnection();

// Now you can use $db for database operations

class AnalyticsDashboard
{
    private $pdo;

    /**
     * Constructor to initialize the database connection.
     */
    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=shopping_db', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Retrieves feedback data for a given store and week.
     *
     * @param int $storeId
     * @return array
     */
    public function getFeedbackData($storeId)
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $query = "SELECT feedback_time FROM store_feedback WHERE store_id = ? AND week_start = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$storeId, $weekStart]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return str_split($result['feedback_time']);
        }

        return array_fill(0, 168, '0'); // Default to no activity for the week
    }

    /**
     * Generates an HTML heatmap for feedback activity.
     *
     * @param int $storeId
     * @return string
     */
    public function generateHeatmap($storeId)
    {
        $feedbackData = $this->getFeedbackData($storeId);
        $html = '<div class="heatmap">';

        for ($day = 0; $day < 7; $day++) {
            $html .= '<div class="day-row">';
            $html .= '<span class="day-label">' . date('l', strtotime("Sunday +{$day} days")) . '</span>';

            for ($hour = 0; $hour < 24; $hour++) {
                $hourlyIndex = $day * 24 + $hour;
                $activity = $feedbackData[$hourlyIndex];
                $color = $activity === '1' ? 'green' : 'lightgray';

                $html .= "<span class='hour-cell' style='background-color: $color' title='Hour: {$hour}'></span>";
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Generates the complete dashboard page.
     *
     * @param int $storeId
     * @return string
     */
    public function generateDashboard($storeId)
    {
        $heatmap = $this->generateHeatmap($storeId);
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Feedback Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .heatmap { display: flex; flex-direction: column; padding: 10px; }
        .day-row { display: flex; align-items: center; margin-bottom: 5px; }
        .day-label { width: 100px; font-weight: bold; }
        .hour-cell { width: 20px; height: 20px; margin: 2px; display: inline-block; }
    </style>
</head>
<body>
    <h1>Store Feedback Dashboard</h1>
    <p>Activity Heatmap (Weekly by Hour)</p>
    {$heatmap}
</body>
</html>
HTML;
    }

    /**
     * Displays the dashboard in the browser.
     *
     * @param int $storeId
     */
    public function renderDashboard($storeId)
    {
        echo $this->generateDashboard($storeId);
    }
}

// Now you can use $db for database operations

$dashboard = new AnalyticsDashboard($db);

// Capture the incoming JSON request body
$requestBody = file_get_contents("php://input");

// Decode the JSON body into an associative array
$data = json_decode($requestBody, true);

// Check if the store_id is provided
if (isset($data['store_id'])) {
    $storeId = $data['store_id'];
    $result = $dashboard->generateDashboard($storeId);
    echo $result; // This already returns HTML content
} else {
    echo json_encode(["status" => "error", "message" => "Missing store_id"]);
}
?>
