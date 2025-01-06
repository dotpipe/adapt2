<?php

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

?>
