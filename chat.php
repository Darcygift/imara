<?php
// chat.php - Live Messaging Interface
require_once 'header.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$activePeerId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$chatThreads = [];
$messages = [];
$activePeer = null;

try {
    // 1. Fetch active thread list (who we have chatted with)
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.name, u.role, u.email
        FROM (
            SELECT from_id as peer_id FROM messages WHERE to_id = :uid
            UNION
            SELECT to_id as peer_id FROM messages WHERE from_id = :uid
        ) peers
        JOIN users u ON peers.peer_id = u.id
    ");
    $stmt->execute(['uid' => $userId]);
    $chatThreads = $stmt->fetchAll();

    // If active peer is set, fetch their details and our messages thread
    if ($activePeerId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$activePeerId]);
        $activePeer = $stmt->fetch();

        if ($activePeer) {
            // Load messages between user and activePeer
            $stmt = $pdo->prepare("
                SELECT * FROM messages 
                WHERE (from_id = :uid AND to_id = :pid) 
                   OR (from_id = :pid AND to_id = :uid)
                ORDER BY created_at ASC
            ");
            $stmt->execute(['uid' => $userId, 'pid' => $activePeerId]);
            $messages = $stmt->fetchAll();

            // Mark received messages from this peer as read
            $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE from_id = ? AND to_id = ?");
            $stmt->execute([$activePeerId, $userId]);
        }
    }
} catch (PDOException $e) {
    // Fail silently
}
?>

<div class="chat-page-grid">
    <!-- Chat Threads Sidebar -->
    <div class="chat-threads-sidebar">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-glass); background: var(--bg-secondary);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);">Inbox Threads</h3>
        </div>
        
        <?php if (empty($chatThreads)): ?>
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                No conversation history found.
            </div>
        <?php else: ?>
            <?php foreach ($chatThreads as $thread): ?>
                <div class="chat-thread-item <?php echo ($thread['id'] === $activePeerId) ? 'active' : ''; ?>" onclick="location.href='chat.php?user_id=<?php echo $thread['id']; ?>'">
                    <div class="chat-thread-meta">
                        <span class="chat-thread-name"><?php echo htmlspecialchars($thread['name']); ?></span>
                        <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--accent-primary);"><?php echo $thread['role']; ?></span>
                    </div>
                    <div class="chat-thread-preview">Click to view direct messages</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Active Chat Window -->
    <div class="chat-main-area">
        <?php if ($activePeer): ?>
            <!-- Header -->
            <div class="chat-main-header">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($activePeer['name']); ?></h3>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); text-transform: capitalize;">Role: <?php echo htmlspecialchars($activePeer['role']); ?></p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 0.5rem; height: 0.5rem; border-radius: 50%; background-color: var(--accent-success);"></span>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Secure Direct Stream</span>
                </div>
            </div>

            <!-- Messages List -->
            <div class="chat-messages-container" id="chatContainer">
                <?php if (empty($messages)): ?>
                    <div style="text-align: center; margin: auto; color: var(--text-muted); font-size: 0.9rem;" id="noMsgPrompt">
                        No messages exchanged yet. Send a greeting to start the conversation!
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="chat-bubble <?php echo ($msg['from_id'] == $userId) ? 'sent' : 'received'; ?>">
                            <?php echo htmlspecialchars($msg['text']); ?>
                            <span class="chat-bubble-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Input form -->
            <form id="sendMessageForm" onsubmit="sendMessage(event)" class="chat-input-bar">
                <input type="hidden" name="to_id" value="<?php echo $activePeerId; ?>">
                <input type="text" name="text" id="chatInputText" class="glass-input" required placeholder="Type your message here..." autocomplete="off">
                <button type="submit" class="btn btn-primary" style="border-radius: 0.75rem; padding: 0.75rem 1.5rem;">
                    Send
                </button>
            </form>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); padding: 2rem;">
                <span style="font-size: 4rem; opacity: 0.4;">💬</span>
                <h3 style="margin-top: 1rem; font-size: 1.25rem; color: var(--text-primary); font-weight: 700;">No Conversation Selected</h3>
                <p style="margin-top: 0.25rem; font-size: 0.85rem;">Select an inbox thread from the sidebar or request a viewing from listing catalog details.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto scroll chat to bottom
function scrollChatToBottom() {
    var container = document.getElementById("chatContainer");
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

// Initial scroll on load
document.addEventListener("DOMContentLoaded", function() {
    scrollChatToBottom();
});

<?php if ($activePeer): ?>
// Send message via AJAX
function sendMessage(e) {
    e.preventDefault();
    var input = document.getElementById("chatInputText");
    var text = input.value.trim();
    if(!text) return;
    
    var form = document.getElementById("sendMessageForm");
    var formData = new FormData(form);
    
    input.value = ""; // clear input immediately
    
    // Remove the placeholder if it exists
    var noMsg = document.getElementById("noMsgPrompt");
    if(noMsg) noMsg.style.display = "none";
    
    fetch('api/send-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Append message bubble
            var chatContainer = document.getElementById("chatContainer");
            var bubble = document.createElement("div");
            bubble.className = "chat-bubble sent";
            
            // Format time
            var d = new Date();
            var timeStr = d.getHours().toString().padStart(2, '0') + ":" + d.getMinutes().toString().padStart(2, '0');
            
            bubble.innerHTML = escapeHtml(text) + '<span class="chat-bubble-time">' + timeStr + '</span>';
            chatContainer.appendChild(bubble);
            scrollChatToBottom();
        } else {
            alert("Error sending: " + data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Connection failed.");
    });
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Live polling for new messages (polls every 3s)
var lastFetchedMessageId = <?php echo count($messages) > 0 ? intval(end($messages)['id']) : 0; ?>;
var activePeerId = <?php echo $activePeerId; ?>;

function pollMessages() {
    fetch('api/get-messages.php?user_id=' + activePeerId + '&last_id=' + lastFetchedMessageId)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.messages && data.messages.length > 0) {
                var chatContainer = document.getElementById("chatContainer");
                
                // Hide placeholder
                var noMsg = document.getElementById("noMsgPrompt");
                if(noMsg) noMsg.style.display = "none";
                
                data.messages.forEach(msg => {
                    var bubble = document.createElement("div");
                    // Identify if sent or received
                    bubble.className = (msg.from_id == <?php echo $userId; ?>) ? "chat-bubble sent" : "chat-bubble received";
                    
                    var date = new Date(msg.created_at);
                    var timeStr = date.getHours().toString().padStart(2, '0') + ":" + date.getMinutes().toString().padStart(2, '0');
                    
                    bubble.innerHTML = escapeHtml(msg.text) + '<span class="chat-bubble-time">' + timeStr + '</span>';
                    chatContainer.appendChild(bubble);
                    
                    // Update latest message ID
                    if(msg.id > lastFetchedMessageId) {
                        lastFetchedMessageId = msg.id;
                    }
                });
                
                scrollChatToBottom();
            }
        })
        .catch(err => console.error("Poll Error:", err));
}

// Set interval for message polling
setInterval(pollMessages, 3000);

<?php endif; ?>
</script>
