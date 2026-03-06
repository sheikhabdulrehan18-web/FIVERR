<?php
require_once 'db.php';
redirectIf(!isLoggedIn(), 'login.php');
 
$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? null;
 
// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $receiver_id) {
    $msg_text = trim($_POST['message']);
    if (!empty($msg_text)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $receiver_id, $msg_text]);
        header("Location: messages.php?receiver_id=$receiver_id");
        exit();
    }
}
 
// Fetch conversation list
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username 
    FROM users u
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
");
$stmt->execute([$user_id, $user_id, $user_id]);
$contacts = $stmt->fetchAll();
 
// If receiver is specified but not in contacts (new conversation)
if ($receiver_id) {
    $is_in_contacts = false;
    foreach ($contacts as $contact) {
        if ($contact['id'] == $receiver_id) {
            $is_in_contacts = true;
            break;
        }
    }
    if (!$is_in_contacts) {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->execute([$receiver_id]);
        $new_contact = $stmt->fetch();
        if ($new_contact) {
            $contacts[] = $new_contact;
        }
    }
}
 
// Fetch messages for active conversation
$chat_messages = [];
$active_contact_name = '';
if ($receiver_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $active_contact_name = $stmt->fetchColumn();
 
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $chat_messages = $stmt->fetchAll();
 
    // Mark as read
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$receiver_id, $user_id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | FiberClone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo">Fiber<span>Clone</span></a>
        <div class="nav-links">
            <a href="index.php">Explore</a>
            <a href="my_orders.php">Orders</a>
            <a href="messages.php">Messages</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
    </nav>
 
    <div class="container">
        <div class="chat-container">
            <div class="sidebar">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e4e5e7; font-weight: 700; font-size: 1.2rem;">Messages</div>
                <?php if (empty($contacts)): ?>
                    <p style="padding: 2rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">No conversations yet.</p>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <a href="messages.php?receiver_id=<?= $contact['id'] ?>" style="display: block; padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f1f2; text-decoration: none; color: inherit; transition: background 0.2s; <?= $receiver_id == $contact['id'] ? 'background: #f1fcf7; border-left: 4px solid var(--primary);' : '' ?>">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="https://via.placeholder.com/40?text=U" alt="User" style="border-radius: 50%;">
                                <div>
                                    <div style="font-weight: 600;"><?= escape($contact['username']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Active now</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
 
            <div class="main-chat">
                <?php if ($receiver_id): ?>
                    <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #e4e5e7; display: flex; align-items: center; gap: 15px;">
                        <img src="https://via.placeholder.com/40?text=U" alt="User" style="border-radius: 50%;">
                        <span style="font-weight: 700; font-size: 1.1rem;"><?= escape($active_contact_name) ?></span>
                    </div>
 
                    <div class="chat-messages" id="message-area">
                        <?php if (empty($chat_messages)): ?>
                            <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Start a conversation with <?= escape($active_contact_name) ?></p>
                        <?php else: ?>
                            <?php foreach ($chat_messages as $m): ?>
                                <div class="message <?= $m['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                    <?= escape($m['message']) ?>
                                    <div style="font-size: 0.7rem; margin-top: 5px; opacity: 0.8; text-align: right;">
                                        <?= date('h:i A', strtotime($m['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
 
                    <form method="POST" class="chat-input">
                        <input type="text" name="message" placeholder="Write a message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                <?php else: ?>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; color: var(--text-muted); padding: 2rem; text-align: center;">
                        <i class="far fa-comments" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                        <h3>Your Messages</h3>
                        <p>Select a contact from the sidebar to start chatting.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
 
    <script>
        const messageArea = document.getElementById('message-area');
        if (messageArea) {
            messageArea.scrollTop = messageArea.scrollHeight;
        }
    </script>
</body>
</html>
 
