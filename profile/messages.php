<?php
session_start();
// require_once __DIR__ . '../config.php';

include "../config.php";

// Redirect if not logged in
if (!isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_data']['id'];
$unread_count = 0;

// Get messages
$received_messages = [];
$sent_messages = [];
$current_folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';

try {
    // Count unread messages
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    // Get received messages
    if ($current_folder === 'inbox') {
        $stmt = $conn->prepare("SELECT m.*, u.name, u.family, u.profile_pic 
                              FROM messages m 
                              JOIN users u ON m.sender_id = u.id 
                              WHERE m.receiver_id = ? 
                              ORDER BY m.sent_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $received_messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Mark messages as read
        $conn->query("UPDATE messages SET is_read = TRUE WHERE receiver_id = $user_id");
    }

    // Get sent messages
    if ($current_folder === 'sent') {
        $stmt = $conn->prepare("SELECT m.*, u.name, u.family, u.profile_pic 
                              FROM messages m 
                              JOIN users u ON m.receiver_id = u.id 
                              WHERE m.sender_id = ? 
                              ORDER BY m.sent_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $sent_messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Messages error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <?php include "../includes.php"; ?>
    <style>
        #chatBox {
            /* ... */
            flex-direction: column;
            /* این مهمه */
        }

        #chatBody {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            /* باید فلکس باشه */
            flex-direction: column;
            /* و این هم مهمه */
        }

        .message-container {
            max-height: 80vh;
            /* این برای لیست پیام‌ها است */
            overflow-y: auto;
        }

        .message-card {
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .message-card:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
        }

        .message-unread {
            background-color: #f0f7ff;
            font-weight: 500;
        }

        .message-sender-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }

        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Styles for the chat box */
        #chatBox {
            position: fixed;
            bottom: 0;
            right: 20px;
            width: 350px;
            /* اندازه چت باکس */
            height: 400px;
            /* ارتفاع چت باکس */
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            /* در ابتدا مخفی باشد */
            flex-direction: column;
            z-index: 1050;
            /* بالاتر از سایر محتوا */
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
        }

        #chatBody {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
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
            /* Bootstrap success light */
            align-self: flex-end;
            margin-left: auto;
            text-align: right;
        }

        .chat-message.received {
            background-color: #e2e3e5;
            /* Bootstrap secondary light */
            align-self: flex-start;
            margin-right: auto;
            text-align: left;
        }

        .chat-time {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 5px;
        }

        #chatInputContainer {
            padding: 10px 15px;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
        }

        #chatMessageInput {
            flex-grow: 1;
            border-radius: 20px;
            padding-right: 40px;
            /* برای فضای آیکون ارسال */
        }

        #sendMessageButton {
            background: none;
            border: none;
            padding: 0;
            margin-left: 10px;
            color: #0d6efd;
            font-size: 1.2rem;
        }

        #sendMessageButton:hover {
            color: #0a58ca;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
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
                                        <span class="badge bg-danger float-end"><?= $unread_count ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_folder === 'sent' ? 'active' : '' ?>" href="?folder=sent">
                                    <i class="fas fa-paper-plane me-2"></i> Sent
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                                    <i class="fas fa-plus-circle me-2"></i> New Message
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= ucfirst($current_folder) ?></h5>
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
                            <?php if ($current_folder === 'inbox'): ?>
                                <?php if (!empty($received_messages)): ?>
                                    <?php foreach ($received_messages as $message): ?>
                                        <div class="message-card p-3 border-bottom <?= !$message['is_read'] ? 'message-unread' : '' ?>"
                                            onclick="openChatBox(<?= htmlspecialchars(json_encode([
                                                                        'user_id' => $message['sender_id'],
                                                                        'name' => $message['name'],
                                                                        'family' => $message['family'],
                                                                        'profile_pic' => $message['profile_pic']
                                                                    ])) ?>)"
                                            style="cursor: pointer;">

                                            <div class="d-flex">
                                                <img src="<?= !empty($message['profile_pic']) ? htmlspecialchars($message['profile_pic']) : '../images/default_profile.png' ?>"
                                                    class="message-sender-img rounded-circle me-3" alt="Sender">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1"><?= htmlspecialchars($message['name'] . ' ' . $message['family']) ?></h6>
                                                        <small class="message-time">
                                                            <?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <p class="mb-1"><strong><?= htmlspecialchars($message['subject']) ?></strong></p>
                                                    <p class="mb-0 text-truncate"><?= htmlspecialchars($message['content']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Your inbox is empty</p>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($current_folder === 'sent'): ?>
                                <?php if (!empty($sent_messages)): ?>
                                    <?php foreach ($sent_messages as $message): ?>
                                        <div class="message-card p-3 border-bottom"
                                            onclick="openChatBox(<?= htmlspecialchars(json_encode([
                                                                        'user_id' => $message['receiver_id'],
                                                                        'name' => $message['name'],
                                                                        'family' => $message['family'],
                                                                        'profile_pic' => $message['profile_pic']
                                                                    ])) ?>)"
                                            style="cursor: pointer;">
                                            <div class="d-flex">
                                                <img src="<?= !empty($message['profile_pic']) ? htmlspecialchars($message['profile_pic']) : '../images/default_profile.png' ?>"
                                                    class="message-sender-img rounded-circle me-3" alt="Recipient">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1">To: <?= htmlspecialchars($message['name'] . ' ' . $message['family']) ?></h6>
                                                        <small class="message-time">
                                                            <?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <p class="mb-1"><strong><?= htmlspecialchars($message['subject']) ?></strong></p>
                                                    <p class="mb-0 text-truncate"><?= htmlspecialchars($message['content']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No sent messages</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newMessageModal" tabindex="-1" aria-labelledby="newMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMessageModalLabel">New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newMessageForm">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">To:</label>
                            <input type="text" class="form-control" id="recipient" name="recipient"
                                placeholder="Search people..." required>
                            <input type="hidden" id="realRecipientId" name="real_recipient_id">
                        </div>
                        <div class="mb-3">
                            <label for="messageSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="messageSubject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="messageContent" class="form-label">Message</label>
                            <textarea class="form-control" id="messageContent" name="content" rows="5" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="sendNewMessageBtn">Send</button>
                </div>
            </div>
        </div>
    </div>

    <div id="chatBox">
        <div id="chatHeader">
            <span id="chatRecipientName"></span>
            <div>
                <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="closeChatBox()"></button>
            </div>
        </div>
        <div id="chatBody" class="d-flex flex-column">
            <div class="text-center text-muted p-3">Loading messages...</div>
        </div>
        <div id="chatInputContainer">
            <input type="text" class="form-control" id="chatMessageInput" placeholder="Type a message...">
            <button type="button" class="btn" id="sendMessageButton"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <input type="hidden" id="currentChatRecipientId">


    <script>
        $(document).ready(function() {
            // Handle sending new message (from the general "New Message" modal)
            $('#sendNewMessageBtn').click(function() {
                const recipientIdToSend = $('#realRecipientId').val();
                if (!recipientIdToSend) {
                    alert('Please select a valid recipient.');
                    return;
                }

                let formData = $('#newMessageForm').serializeArray();
                formData.push({
                    name: 'receiver_id',
                    value: recipientIdToSend
                }); // Corrected to receiver_id
                formData.push({
                    name: 'sender_id',
                    value: '<?= $user_id ?>'
                });

                $.ajax({
                    url: 'send_message.php', // Assuming this handles the DB insertion
                    type: 'POST',
                    data: $.param(formData),
                    dataType: 'json',
                    beforeSend: function() {
                        $('#sendNewMessageBtn').prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm" role="status"></span> Sending...');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#newMessageModal').modal('hide');
                            alert('Message sent successfully!');
                            $('#newMessageForm')[0].reset();
                            location.reload(); // Refresh to show new message
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        alert('An error occurred while sending the message. Check console for details.');
                    },
                    complete: function() {
                        $('#sendNewMessageBtn').prop('disabled', false).text('Send');
                    }
                });
            });

            // Auto-complete for recipient search in the general "New Message" modal
            $('#recipient').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: 'search_users.php', // This file should return JSON {id, label, profile_pic}
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.name + ' ' + item.family, // Display full name
                                    value: item.name + ' ' + item.family, // Value inserted into input
                                    id: item.id, // Actual user ID
                                    profile_pic: item.profile_pic // Profile picture
                                };
                            }));
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    $('#realRecipientId').val(ui.item.id); // Set the hidden input for recipient ID
                }
            }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append(`<div class="d-flex align-items-center">
                        <img src="${item.profile_pic || '../images/default_profile.png'}" class="rounded-circle me-2" width="30" height="30">
                        <span>${item.label}</span>
                    </div>`)
                    .appendTo(ul);
            };

            // --- Chat Box Specific Logic ---

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
            };

            // Function to load conversation history
            function loadConversation(otherUserId) {
                $.ajax({
                    url: 'get_conversation.php', // You'll create this file
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
                                const profilePic = msg.profile_pic || '../images/default_profile.png';
                                const senderName = msg.name + ' ' + msg.family;
                                const time = new Date(msg.sent_at).toLocaleString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: 'numeric',
                                    hour12: true
                                });

                                let messageHtml = `<div class="chat-message d-flex flex-column ${messageClass}">`;
                                // Display sender/receiver name only if it's not the current user for received messages
                                if (!isSent) {
                                    messageHtml += `<small class="text-muted mb-1">${senderName}</small>`;
                                }
                                messageHtml += `    <p class="mb-0">${msg.content}</p>
                                    <small class="chat-time">${time}</small>
                                </div>`;
                                $('#chatBody').append(messageHtml);
                            });
                            $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight); // Scroll to bottom
                        } else {
                            $('#chatBody').html('<div class="text-center text-muted p-3">No messages yet. Start a conversation!</div>');
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
                        sender_id: '<?= $user_id ?>',
                        receiver_id: recipientId, // Assuming send_message.php expects receiver_id
                        subject: 'Chat Message', // A default subject for chat messages
                        content: messageContent
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Optionally disable input/button
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#chatMessageInput').val(''); // Clear input
                            // Optimistically add message to chat body
                            const time = new Date().toLocaleString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                hour12: true
                            });
                            const newMessageHtml = `<div class="chat-message d-flex flex-column sent">
                                <p class="mb-0">${messageContent}</p>
                                <small class="chat-time">${time}</small>
                            </div>`;
                            $('#chatBody').append(newMessageHtml);
                            $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight); // Scroll to bottom

                            // Re-load conversation after a short delay to ensure consistency
                            // (or just rely on optimistic update if server response is fast)
                            // setTimeout(() => loadConversation(recipientId), 500); 

                        } else {
                            alert('Error sending chat message: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error sending chat message:", status, error);
                        alert('An error occurred while sending the chat message.');
                    }
                });
            }
        });
    </script>
</body>

</html>