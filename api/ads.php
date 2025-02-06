<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Create a new ad
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['title'], $data['description'], $data['type'], $data['start_date'], $data['end_date'])) {
            $stmt = $conn->prepare("INSERT INTO ads (title, description, type, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $data['title'], $data['description'], $data['type'], $data['start_date'], $data['end_date']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Ad created successfully']);
            } else {
                echo json_encode(['error' => 'Failed to create ad']);
            }
        } else {
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    case 'GET':
        // Retrieve ads
        $sql = "SELECT * FROM ads";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $ads = [];
            while ($row = $result->fetch_assoc()) {
                $ads[] = $row;
            }
            echo json_encode($ads);
        } else {
            echo json_encode(['message' => 'No ads found']);
        }
        break;

    case 'PUT':
        // Update an existing ad
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['id'], $data['title'], $data['description'], $data['type'], $data['start_date'], $data['end_date'])) {
            $stmt = $conn->prepare("UPDATE ads SET title = ?, description = ?, type = ?, start_date = ?, end_date = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $data['title'], $data['description'], $data['type'], $data['start_date'], $data['end_date'], $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Ad updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update ad']);
            }
        } else {
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    case 'DELETE':
        // Delete an ad
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM ads WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Ad deleted successfully']);
            } else {
                echo json_encode(['error' => 'Failed to delete ad']);
            }
        } else {
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method']);
        break;
}

$conn->close();
