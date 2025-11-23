<?php
/**
 * =============================================
 * LEE SNEAKERS CHATBOT SYSTEM
 * File: chatbot/widget.php
 * =============================================
 * Standalone chatbot module
 * Include in user/index.php as: <?php include '../chatbot/widget.php'; ?>
 */

require_once '../config/database.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$username = $is_logged_in ? $_SESSION['username'] : '';

// Create chat_sessions table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS chat_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        full_name VARCHAR(100),
        contact_number VARCHAR(20),
        session_started TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        session_ended TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// Create chat_messages table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS chat_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT,
        sender_type ENUM('user', 'bot', 'admin') DEFAULT 'user',
        message_text LONGTEXT,
        is_quick_reply BOOLEAN DEFAULT FALSE,
        action_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
    )
");

// Create chat_user_info table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS chat_user_info (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT,
        user_concern LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
    )
");
?>

<!-- CHATBOT WIDGET HTML -->
<div class="chatbot-widget">
  <button class="chat-bubble-btn" id="chatBubbleBtn" onclick="toggleChat()">
    <i class="fas fa-comments"></i>
  </button>

  <div class="chat-window" id="chatWindow">
    <div class="chat-header">
      <h3 id="chatHeaderTitle">LEE Sneakers Support</h3>
      <button class="chat-back-btn" id="chatBackBtn" onclick="goBackToMenu()" style="display: none;" title="Back">
        <i class="fas fa-arrow-left"></i>
      </button>
      <button class="chat-close-btn" onclick="closeChat()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- MESSAGES AREA -->
    <div class="chat-messages" id="chatMessages">
      <div class="message bot">
        <div class="message-content">
          <div class="greeting-icon">👋</div>
          <div class="greeting-title">Chat with us</div>
          <div class="greeting-text">Hi, message us with any questions. We're happy to help!</div>
        </div>
      </div>
    </div>

    <!-- QUICK REPLIES -->
    <div id="quickRepliesContainer" style="display: block;">
      <div class="quick-replies">
        <button class="quick-reply-btn" onclick="handleQuickReply('track')">
          <i class="fas fa-box me-2"></i>Track my order
        </button>
        <button class="quick-reply-btn" onclick="handleQuickReply('contact')">
          <i class="fas fa-phone me-2"></i>What is your contact info?
        </button>
        <button class="quick-reply-btn" onclick="handleQuickReply('shipping')">
          <i class="fas fa-truck me-2"></i>What are your shipping details?
        </button>
        <button class="quick-reply-btn" onclick="handleQuickReply('delivery')">
          <i class="fas fa-calendar-check me-2"></i>When can I expect my order?
        </button>
        <button class="quick-reply-btn" onclick="handleQuickReply('refund')">
          <i class="fas fa-undo me-2"></i>I want to refund my order
        </button>
      </div>
    </div>

    <!-- INPUT AREA -->
    <div class="chat-input-section">
      <input 
        type="text" 
        class="chat-input" 
        id="chatInput" 
        placeholder="Type your message..." 
        onkeypress="handleKeyPress(event)"
      >
      <button class="chat-send-btn" onclick="sendMessage()">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </div>
</div>

<!-- CHATBOT JAVASCRIPT -->
<script>
let currentSessionId = null;
let currentStage = 'menu'; // menu, get_name, get_phone, get_concern
let tempUserData = {
  full_name: '',
  contact_number: '',
  concern: ''
};

const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
const userId = <?php echo $user_id ? $user_id : 'null'; ?>;

// Initialize chat session
function initializeChatSession() {
  if (!currentSessionId) {
    fetch('../chatbot/api.php?action=create_session', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
      currentSessionId = data.session_id;
    });
  }
}

function toggleChat() {
  const chatWindow = document.getElementById('chatWindow');
  const chatBubbleBtn = document.getElementById('chatBubbleBtn');
  
  if (chatWindow.classList.contains('active')) {
    chatWindow.classList.remove('active');
    chatBubbleBtn.classList.remove('active');
  } else {
    chatWindow.classList.add('active');
    chatBubbleBtn.classList.add('active');
    initializeChatSession();
  }
}

function closeChat() {
  document.getElementById('chatWindow').classList.remove('active');
  document.getElementById('chatBubbleBtn').classList.remove('active');
}

function goBackToMenu() {
  currentStage = 'menu';
  tempUserData = { full_name: '', contact_number: '', concern: '' };
  
  document.getElementById('quickRepliesContainer').style.display = 'block';
  document.getElementById('chatBackBtn').style.display = 'none';
  document.getElementById('chatHeaderTitle').textContent = 'LEE Sneakers Support';
  document.getElementById('chatMessages').scrollTop = 0;
}

function handleQuickReply(action) {
  if (!currentSessionId) initializeChatSession();
  
  // Log quick reply (but not in conversation)
  addMessage(`Selected: ${action}`, 'user');
  document.getElementById('quickRepliesContainer').style.display = 'none';
  document.getElementById('chatBackBtn').style.display = 'block';

  const responses = {
    track: {
      title: 'Track my order',
      action: 'track',
      message: 'Please provide your order number to track.'
    },
    contact: {
      title: 'What is your contact info?',
      action: 'contact',
      message: 'Redirecting to contact information...'
    },
    shipping: {
      title: 'What are your shipping details?',
      action: 'shipping',
      message: 'Redirecting to shipping policy...'
    },
    delivery: {
      title: 'When can I expect my order?',
      action: 'delivery',
      message: 'Redirecting to delivery information...'
    },
    refund: {
      title: 'I want to refund my order',
      action: 'refund',
      message: 'Redirecting to refund policy...'
    }
  };

  const response = responses[action];
  
  // Log to database (don't save as regular message)
  logQuickReplyAction(action);
  
  setTimeout(() => {
    addMessage(response.message, 'bot');
    
    // Redirect based on action
    if (action === 'contact') {
      setTimeout(() => window.location.href = 'about.php', 2000);
    } else if (action === 'shipping') {
      setTimeout(() => window.open('../Legal/shipping_policy.php', '_blank'), 2000);
    } else if (action === 'delivery') {
      setTimeout(() => window.open('../Legal/shipping_policy.php', '_blank'), 2000);
    } else if (action === 'refund') {
      setTimeout(() => window.open('../Legal/refund_policy.php', '_blank'), 2000);
    } else if (action === 'track') {
      currentStage = 'get_order_number';
    }
  }, 300);
}

function sendMessage() {
  console.log('Sending message');
  const input = document.getElementById('chatInput');
  const message = input.value.trim();

  console.log('Sending message:', message);
  console.log('Current Session ID:', userId);
  
  if (!message) return;
  if (!currentSessionId) initializeChatSession();

  // Save message to database via Fetch API
  fetch('../chatbot/api.php?action=save_message', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      user_id: userId || 0,
      chat: message
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log('Message saved:', data);
  })
  .catch(error => {
    console.error('Error saving message:', error);
  });

  addMessage(message, 'user');
  input.value = '';

  // Route based on current stage
  if (currentStage === 'menu') {
    requestUserInfo();
  } else if (currentStage === 'get_name') {
    tempUserData.full_name = message;
    currentStage = 'get_phone';
    addMessage('Thank you! What is your contact number?', 'bot');
  } else if (currentStage === 'get_phone') {
    tempUserData.contact_number = message;
    currentStage = 'get_concern';
    addMessage('What is your concern today?', 'bot');
  } else if (currentStage === 'get_concern') {
    tempUserData.concern = message;
    saveUserConversation();
    currentStage = 'menu';
    addMessage('Thank you! Your message has been received. Our team will get back to you soon.', 'bot');
  } else if (currentStage === 'get_order_number') {
    fetchOrderTracking(message);
  }
}

function requestUserInfo() {
  currentStage = 'get_name';
  addMessage('What is your full name?', 'bot');
}

function saveUserConversation() {
  fetch('../chatbot/api.php?action=save_conversation', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      session_id: currentSessionId,
      full_name: tempUserData.full_name,
      contact_number: tempUserData.contact_number,
      concern: tempUserData.concern
    })
  });
}

function logQuickReplyAction(action) {
  fetch('../chatbot/api.php?action=log_quick_reply', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      session_id: currentSessionId,
      action_type: action
    })
  });
}

function fetchOrderTracking(orderNumber) {
  fetch('../chatbot/api.php?action=get_order&order_id=' + orderNumber)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        addMessage(`Order #${data.order.id}: ${data.order.status.toUpperCase()} - Amount: ₱${data.order.total_amount}`, 'bot');
      } else {
        addMessage('Order not found. Please check the order number and try again.', 'bot');
      }
      currentStage = 'menu';
    });
}

function addMessage(text, sender) {
  const chatMessages = document.getElementById('chatMessages');
  const messageDiv = document.createElement('div');
  messageDiv.className = `message ${sender}`;

  const contentDiv = document.createElement('div');
  contentDiv.className = 'message-content';
  contentDiv.innerHTML = text.replace(/\n/g, '<br>');

  messageDiv.appendChild(contentDiv);
  chatMessages.appendChild(messageDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function handleKeyPress(event) {
  if (event.key === 'Enter') {
    sendMessage();
  }
}
</script>