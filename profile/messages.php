<?php
session_start();
include "../config.php"; // Ensure $conn is active and establishes database connection

if (!isset($_SESSION['user_data'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_data']['id'];
$unread_count = 0; // Unread message count for inbox badge
$conversations = []; // This array will hold the latest message from each conversation
$current_folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';

try {
    // Count total unread messages for the current user (across all incoming messages)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    // --- Fetch Conversations (Latest message for each thread) ---
    // Note: GROUP BY conversation_id is used to ensure we only get the latest message from each conversation.
    // JOIN with users is used to get sender/receiver information.

    $query_common_part = "
        SELECT 
            m.*, 
            u.name, 
            u.family, 
            u.profile_pic 
        FROM messages m 
    ";

    if ($current_folder === 'inbox') {
        $query = $query_common_part . "
            JOIN users u ON m.sender_id = u.id 
            WHERE m.receiver_id = ? 
            AND m.id IN (
                SELECT MAX(id)
                FROM messages
                WHERE receiver_id = ? AND conversation_id IS NOT NULL -- Only valid conversations
                GROUP BY conversation_id
            )
            ORDER BY m.sent_at DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
    } elseif ($current_folder === 'sent') {
        $query = $query_common_part . "
            JOIN users u ON m.receiver_id = u.id 
            WHERE m.sender_id = ? 
            AND m.id IN (
                SELECT MAX(id)
                FROM messages
                WHERE sender_id = ? AND conversation_id IS NOT NULL -- Only valid conversations
                GROUP BY conversation_id
            )
            ORDER BY m.sent_at DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
    } else {
        // If folder is invalid
        echo "<div class='alert alert-warning text-center'>Invalid message folder.</div>";
        exit();
    }

    if (!$stmt) {
        throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->execute();
    $conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Mark messages as read IF the user is viewing the inbox.
    // You can remove this if you prefer messages to be marked as read only when the chat box is opened (which is more logical).
    // For simplicity, we'll keep it for now, but consider this.
    if ($current_folder === 'inbox' && !empty($conversations)) {
        $update_stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND is_read = FALSE");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        // After all messages are marked as read, the unread count becomes zero
        $unread_count = 0;
    }
} catch (Exception $e) {
    error_log("Messages error: " . $e->getMessage());
    echo "<div class='alert alert-danger text-center'>Error loading messages. Please try again later.</div>";
} finally {
    // $conn->close(); // Only if this script is the only one needing the connection
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <?php include "../includes.php"; ?>
    <style>
        /* Your existing styles */
        #chatBox {
            flex-direction: column;
        }

        #chatBody {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }

        .message-container {
            max-height: 80vh;
            overflow-y: auto;
        }

        .message-card {
            transition: all 0.3s;
            border-left: 3px solid transparent;
            /* Changed from border-right for LTR */
        }

        .message-card:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
            /* Changed from border-right-color for LTR */
        }

        .message-unread {
            background-color: #f0f7ff;
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }

        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
            direction: ltr;
            /* Date and time displayed LTR */
            text-align: right;
            /* For display on the right */
        }

        /* Styles for the chat box */
        #chatBox {
            position: fixed;
            bottom: 0;
            right: 20px;
            /* Changed from left for LTR */
            width: 350px;
            height: 400px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            z-index: 1050;
        }

        #chatHeader {
            background-color: #0d6efd;
            color: white;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-direction: row;
            /* Changed from row-reverse for LTR */
        }

        #chatHeader span {
            margin-right: auto;
            /* Changed from margin-right for LTR */
        }

        #chatBody {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }

        .chat-message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.sent {
            background-color: #d1e7dd;
            align-self: flex-end;
            /* Changed from flex-start for LTR (sender on right) */
            margin-left: auto;
            /* Changed from margin-right for LTR (sender on right) */
            text-align: left;
            border-top-left-radius: 0;
            /* Changed from border-top-right-radius for LTR */
        }

        .chat-message.received {
            background-color: #e2e3e5;
            align-self: flex-start;
            /* Changed from flex-end for LTR (receiver on left) */
            margin-right: auto;
            /* Changed from margin-left for LTR (receiver on left) */
            text-align: left;
            border-top-right-radius: 0;
            /* Changed from border-top-left-radius for LTR */
        }

        /* Time alignment within the message bubble */
        .chat-message.sent .chat-time {
            text-align: right;
            /* Sent message time to the right of the bubble */
        }

        .chat-message.received .chat-time {
            text-align: left;
            /* Received message time to the left of the bubble */
        }

        .chat-time {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 5px;
            direction: ltr;
            /* Time displayed LTR */
        }

        #chatInputContainer {
            padding: 10px 15px;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            flex-direction: row;
            /* Changed from row-reverse for LTR */
        }

        #chatMessageInput {
            flex-grow: 1;
            border-radius: 20px;
            padding-right: 40px;
            /* Changed from padding-left for LTR */
            padding-left: 15px;
            text-align: left;
            /* Text from left to right */
        }

        #sendMessageButton {
            background: none;
            border: none;
            padding: 0;
            margin-left: 10px;
            /* Changed from margin-right for LTR */
            color: #0d6efd;
            font-size: 1.2rem;
        }

        #sendMessageButton:hover {
            color: #0a58ca;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #chatBox {
                width: 100%;
                left: 0;
                right: 0;
                border-radius: 0;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <?php include "sidebar.php"; ?>
            <div class="col-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Messages</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?= $current_folder === 'inbox' ? 'active' : '' ?>" href="?folder=inbox">
                                    <i class="fas fa-inbox me-2"></i> Inbox
                                    <?php if ($unread_count > 0): ?>
                                        <span class="badge bg-danger float-end"><?= $unread_count ?></span> <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_folder === 'sent' ? 'active' : '' ?>" href="?folder=sent">
                                    <i class="fas fa-paper-plane me-2"></i> Sent Messages
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= $current_folder === 'inbox' ? 'Inbox' : 'Sent Messages' ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i> Delete All</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-filter me-2"></i> Filter</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="message-container">
                            <?php if (!empty($conversations)): ?>
                                <?php foreach ($conversations as $message):
                                    // Determine the "other user" for this conversation summary
                                    $other_user_id = ($current_folder === 'inbox') ? $message['sender_id'] : $message['receiver_id'];
                                    $other_user_name = htmlspecialchars($message['name'] . ' ' . $message['family']);
                                    $other_user_profile_pic = !empty($message['profile_pic']) ? htmlspecialchars($message['profile_pic']) : '../images/default_profile.png';
                                    $display_prefix = ($current_folder === 'sent') ? 'To: ' : 'From: ';

                                    // Display content instead of static subject
                                    $display_summary_content = htmlspecialchars($message['content']);
                                    if (mb_strlen($display_summary_content) > 100) { // Limit display content length
                                        $display_summary_content = mb_substr($display_summary_content, 0, 100) . '...';
                                    }

                                    // Message sent date and time
                                    $formatted_time = date('Y/m/d H:i', strtotime($message['sent_at']));
                                ?>
                                    <div class="message-card p-3 border-bottom <?= (!$message['is_read'] && $current_folder === 'inbox') ? 'message-unread' : '' ?>"
                                        onclick="openChatBox(<?= htmlspecialchars(json_encode([
                                                                    'user_id' => $other_user_id,
                                                                    'name' => $message['name'], // Pass individual name/family for display in chat header
                                                                    'family' => $message['family'],
                                                                    'profile_pic' => $other_user_profile_pic
                                                                ])) ?>)"
                                        style="cursor: pointer;">
                                        <div class="d-flex">
                                            <img src="<?= $other_user_profile_pic ?>"
                                                class="message-sender-img rounded-circle me-3" alt="User Profile">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1"><?= $display_prefix . $other_user_name ?></h6>
                                                    <small class="message-time">
                                                        <?= $formatted_time ?>
                                                    </small>
                                                </div>
                                                <p class="mb-0 text-truncate"><?= $display_summary_content ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <?php if ($current_folder === 'inbox'): ?>
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Your inbox is empty.</p>
                                    <?php else: ?>
                                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">You have no sent messages.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chatBox">
        <div id="chatHeader">
            <span id="chatRecipientName"></span>
            <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="closeChatBox()"></button>
        </div>
        <div id="chatBody" class="d-flex flex-column">
            <div class="text-center text-muted p-3">Loading messages...</div>
        </div>
        <div id="chatInputContainer">
            <input type="text" class="form-control" id="chatMessageInput" placeholder="Write your message...">
            <button type="button" class="btn" id="sendMessageButton"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <input type="hidden" id="currentChatRecipientId">


    <script>
        $(document).ready(function() {
            // Function to open the chat box and load conversation
            window.openChatBox = function(recipientData) {
                const recipientName = recipientData.name + ' ' + recipientData.family;
                const recipientId = recipientData.user_id;

                $('#chatRecipientName').text(recipientName);
                $('#currentChatRecipientId').val(recipientId); // Store the current chat recipient ID
                $('#chatBox').css('display', 'flex'); // Show the chat box

                loadConversation(recipientId);
            };

            // Function to close the chat box
            window.closeChatBox = function() {
                $('#chatBox').css('display', 'none');
                $('#chatBody').empty(); // Clear chat messages
                $('#currentChatRecipientId').val(''); // Clear recipient ID
                // Optionally refresh the inbox to update unread counts
                location.reload(); // Refresh the page to update the main message list
            };

            // Function to load conversation history
            function loadConversation(otherUserId) {
                $.ajax({
                    url: 'get_conversation.php', // This file should retrieve messages between current user and otherUserId
                    type: 'GET',
                    data: {
                        other_user_id: otherUserId
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#chatBody').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');
                    },
                    success: function(messages) {
                        $('#chatBody').empty();
                        if (messages.length > 0) {
                            messages.forEach(function(msg) {
                                const isSent = msg.sender_id == '<?= $user_id ?>'; // Compare with current user's ID
                                const messageClass = isSent ? 'sent' : 'received';
                                const time = new Date(msg.sent_at).toLocaleString('en-US', { // Changed to 'en-US' for English
                                    year: 'numeric',
                                    month: 'numeric',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: 'numeric',
                                    hour12: false // 24-hour format
                                });

                                let messageHtml = `<div class="chat-message d-flex flex-column ${messageClass}">`;
                                messageHtml += `    <p class="mb-0">${msg.content}</p>
                                    <small class="chat-time">${time}</small>
                                </div>`;
                                $('#chatBody').append(messageHtml);
                            });
                            $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight); // Scroll to bottom
                        } else {
                            $('#chatBody').html('<div class="text-center text-muted p-3">No messages sent yet. Start a conversation!</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading conversation:", status, error);
                        $('#chatBody').html('<div class="alert alert-danger text-center">Error loading messages.</div>');
                    }
                });
            }

            // Send message from chat box
            $('#sendMessageButton').click(function() {
                sendMessageFromChatBox();
            });

            $('#chatMessageInput').keypress(function(e) {
                if (e.which == 13) { // Enter key pressed
                    sendMessageFromChatBox();
                    return false; // Prevent new line
                }
            });

            function sendMessageFromChatBox() {
                const messageContent = $('#chatMessageInput').val().trim();
                const recipientId = $('#currentChatRecipientId').val();

                if (messageContent === '' || !recipientId) {
                    return;
                }

                $.ajax({
                    url: 'send_message.php', // Re-use the existing send_message.php
                    type: 'POST',
                    data: {
                        // We do NOT send sender_id from JS, PHP should get it from session
                        receiver_id: recipientId,
                        subject: 'Chat Message', // A default subject for chat messages
                        content: messageContent
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Optionally disable input/button
                        $('#sendMessageButton').prop('disabled', true);
                        $('#chatMessageInput').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#chatMessageInput').val(''); // Clear input
                            // Optimistically add message to chat body
                            const time = new Date().toLocaleString('en-US', { // Changed to 'en-US'
                                year: 'numeric',
                                month: 'numeric',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                hour12: false
                            });
                            const newMessageHtml = `<div class="chat-message d-flex flex-column sent">
                                <p class="mb-0">${messageContent}</p>
                                <small class="chat-time">${time}</small>
                            </div>`;
                            $('#chatBody').append(newMessageHtml);
                            $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight); // Scroll to bottom

                            // Re-enable input/button
                            $('#sendMessageButton').prop('disabled', false);
                            $('#chatMessageInput').prop('disabled', false).focus();

                            // No need to fully refresh the page. The conversation list should also update.
                            // For simplicity, location.reload() remains in closeChatBox for now.

                        } else {
                            alert('Error sending chat message: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error sending chat message:", status, error);
                        alert('An error occurred while sending the chat message. Please try again.');
                    },
                    complete: function() {
                        $('#sendMessageButton').prop('disabled', false);
                        $('#chatMessageInput').prop('disabled', false);
                    }
                });
            }
        });
    </script>

    <?php include "footer.php"; ?>

</body>

</html>