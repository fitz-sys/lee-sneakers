<?php
/**
 * =============================================
 * LEE SNEAKERS CHATBOT API
 * File: chatbot/api.php
 * =============================================
 * Handles all chatbot backend operations
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        
        case 'create_session':
            createChatSession();
            break;
            
        case 'save_conversation':
            saveConversation();
            break;
            
        case 'log_quick_reply':
            logQuickReply();
            break;
            
        case 'save_message':
            insertMessage();
            break;
            
        case 'get_chat_messages':
            getChatMessages();
            break;
            
        case 'save_admin_message':
            saveAdminMessage();
            break;
            
        case 'get_admin_messages':
            getAdminMessages();
            break;
            
        case 'get_order':
            getOrderTracking();
            break;
            
        case 'get_all_sessions':
            getAllChatSessions();
            break;
            
        case 'get_session_messages':
            getSessionMessages();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// ============================================
// FUNCTIONS
// ============================================

function createChatSession() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO chat_sessions (user_id, full_name, contact_number, session_started)
        VALUES (?, '', '', NOW())
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'session_id' => $conn->insert_id
    ]);
}

function saveConversation() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $session_id = $data['session_id'];
    $full_name = $data['full_name'];
    $contact_number = $data['contact_number'];
    $concern = $data['concern'];
    
    // Update session with user info
    $stmt = $conn->prepare("
        UPDATE chat_sessions 
        SET full_name = ?, contact_number = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $full_name, $contact_number, $session_id);
    $stmt->execute();
    
    // Save user concern
    $stmt = $conn->prepare("
        INSERT INTO chat_user_info (session_id, user_concern, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("is", $session_id, $concern);
    $stmt->execute();
    
    // Log actual message (not auto-reply)
    $stmt = $conn->prepare("
        INSERT INTO chat_messages (session_id, sender_type, message_text, is_quick_reply, created_at)
        VALUES (?, 'user', ?, FALSE, NOW())
    ");
    $stmt->bind_param("is", $session_id, $concern);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}

function logQuickReply() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $session_id = $data['session_id'];
    $action_type = $data['action_type'];
    
    // Log quick reply action (DO NOT save as regular message)
    $stmt = $conn->prepare("
        INSERT INTO chat_messages (session_id, sender_type, action_type, is_quick_reply, created_at)
        VALUES (?, 'system', ?, TRUE, NOW())
    ");
    $stmt->bind_param("is", $session_id, $action_type);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}

function getOrderTracking() {
    global $conn;
    
    $order_id = $_GET['order_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT id, total_amount, status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
    }
}

function getAllChatSessions() {
    global $conn;
    
    // Check admin permission
    if (!isLoggedIn() || !isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT 
            cs.id,
            cs.user_id,
            cs.full_name,
            cs.contact_number,
            cs.session_started,
            COUNT(cm.id) as message_count
        FROM chat_sessions cs
        LEFT JOIN chat_messages cm ON cs.id = cm.session_id AND cm.is_quick_reply = FALSE
        GROUP BY cs.id
        ORDER BY cs.session_started DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
}

function getSessionMessages() {
    global $conn;
    
    // Check admin permission
    if (!isLoggedIn() || !isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $session_id = $_GET['session_id'] ?? 0;
    
    $stmt = $conn->prepare("
        SELECT 
            cm.id,
            cm.sender_type,
            cm.message_text,
            cm.is_quick_reply,
            cm.action_type,
            cm.created_at
        FROM chat_messages cm
        WHERE cm.session_id = ? AND cm.is_quick_reply = FALSE
        ORDER BY cm.created_at ASC
    ");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
}

function insertMessage() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['chat'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields: username, chat'
        ]);
        return;
    }
    
    $username = (string)$data['username'];
    // echo json_encode([
    //         'success' => false,
    //         'error' => 'Database error: ' . $username
    //     ]);
    // return;
    $chat = (string)$data['chat'];
    $receiver = "admin";
    
    $stmt = $conn->prepare("
        INSERT INTO chat (username, chat, receiver, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $conn->error
        ]);
        return;
    }
    
    $stmt->bind_param("sss", $username, $chat, $receiver);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message_id' => $conn->insert_id,
            'message' => 'Message saved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to insert message: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

function getChatMessages() {
    global $conn;
    
    $username = $_GET['username'] ?? '';
    
    if (empty($username)) {
        echo json_encode([
            'success' => false,
            'error' => 'Username is required'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT id, chat, username, receiver, created_at
        FROM chat
        WHERE username = ? OR receiver = ?
        ORDER BY created_at ASC
    ");
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $conn->error
        ]);
        return;
    }
    
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);
    
    $stmt->close();
}

function saveAdminMessage() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['chat']) || !isset($data['receiver'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields: username, chat, receiver'
        ]);
        return;
    }
    
    $username = (string)$data['username'];  // admin username
    $chat = (string)$data['chat'];
    $receiver = (string)$data['receiver'];   // target user
    
    $stmt = $conn->prepare("
        INSERT INTO chat (username, chat, receiver, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $conn->error
        ]);
        return;
    }
    
    $stmt->bind_param("sss", $username, $chat, $receiver);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message_id' => $conn->insert_id,
            'message' => 'Message sent successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send message: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

function getAdminMessages() {
    global $conn;
    
    $adminUsername = $_GET['username'] ?? '';
    
    if (empty($adminUsername)) {
        echo json_encode([
            'success' => false,
            'error' => 'Admin username is required'
        ]);
        return;
    }
    
    // Get all messages where:
    // 1. Admin is the receiver (messages FROM users)
    // 2. Admin is the sender (messages TO users)
    $stmt = $conn->prepare("
        SELECT id, chat, username, receiver, created_at
        FROM chat
        WHERE receiver = ? OR username = ?
        ORDER BY created_at ASC
    ");
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $conn->error
        ]);
        return;
    }
    
    $stmt->bind_param("ss", $adminUsername, $adminUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);
    
    $stmt->close();
}
?>