<?php
class ChatAPI {
    private $chatData = [];

    // Method to start a chat
    public function startChat($visitorId, $storeZipcode, $storeType) {
        $chatId = uniqid();
        $this->chatData[$chatId] = [
            'visitor_id' => $visitorId,
            'store_zipcode' => $storeZipcode,
            'store_type' => $storeType,
            'messages' => [],
            'status' => 'active',
            'timestamp' => time()
        ];
        return $this->jsonResponse(["chat_id" => $chatId]);
    }

    // Method to send a message
    public function sendMessage($chatId, $user, $message, $cussingMode) {
        if (isset($this->chatData[$chatId])) {
            $this->chatData[$chatId]['messages'][] = [
                'user' => $user,
                'message' => $message,
                'timestamp' => time(),
                'cussing_mode' => $cussingMode
            ];
            return $this->jsonResponse(["status" => "success"]);
        }
        return $this->jsonErrorResponse("Chat not found");
    }

    // Method to put the visitor on hold
    public function putOnHold($chatId, $holdMessage, $internalMessage) {
        if (isset($this->chatData[$chatId])) {
            $this->chatData[$chatId]['messages'][] = [
                'user' => 'store',
                'message' => $holdMessage,
                'timestamp' => time(),
                'cussing_mode' => false
            ];
            $this->chatData[$chatId]['messages'][] = [
                'user' => 'internal',
                'message' => $internalMessage,
                'timestamp' => time(),
                'cussing_mode' => false
            ];
            return $this->jsonResponse(["status" => "success", "message" => "Visitor is on hold."]);
        }
        return $this->jsonErrorResponse("Chat not found");
    }

    // Method to forward the visitor to another store
    public function forwardVisitor($chatId, $newStoreZipcode, $newStoreType) {
        if (isset($this->chatData[$chatId])) {
            $this->chatData[$chatId]['store_zipcode'] = $newStoreZipcode;
            $this->chatData[$chatId]['store_type'] = $newStoreType;
            $this->chatData[$chatId]['messages'][] = [
                'user' => 'store',
                'message' => "You are now chatting with Store $newStoreType for further assistance.",
                'timestamp' => time(),
                'cussing_mode' => false
            ];
            return $this->jsonResponse(["status" => "success", "message" => "Visitor forwarded to new store."]);
        }
        return $this->jsonErrorResponse("Chat not found");
    }

    // Method to get chat history
    public function getChatHistory($chatId) {
        if (isset($this->chatData[$chatId])) {
            return $this->jsonResponse($this->chatData[$chatId]);
        }
        return $this->jsonErrorResponse("Chat not found");
    }

    // Helper method to return a JSON response
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    // Helper method to return an error JSON response
    private function jsonErrorResponse($message) {
        return $this->jsonResponse(["status" => "error", "message" => $message]);
    }
}

// Create an instance of the API class
$chatAPI = new ChatAPI();

// Handle incoming API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'start_chat':
            echo $chatAPI->startChat($data['visitor_id'], $data['store_zipcode'], $data['store_type']);
            break;
        case 'send_message':
            echo $chatAPI->sendMessage($data['chat_id'], $data['user'], $data['message'], $data['cussing_mode']);
            break;
        case 'put_on_hold':
            echo $chatAPI->putOnHold($data['chat_id'], $data['hold_message'], $data['internal_message']);
            break;
        case 'forward_visitor':
            echo $chatAPI->forwardVisitor($data['chat_id'], $data['new_store_zipcode'], $data['new_store_type']);
            break;
        default:
            echo $chatAPI->jsonErrorResponse("Invalid action");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_chat_history') {
    $chatAPI->getChatHistory($_GET['chat_id']);
} else {
    echo $chatAPI->jsonErrorResponse("Invalid request method or missing parameters.");
}
?>
