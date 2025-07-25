<?php
session_start();
include "../config.php";

if (!isset($_GET['id']) || !isset($_SESSION['user_data'])) {
    die("Invalid request");
}

$messageId = $_GET['id'];
$userId = $_SESSION['user_data']['id'];

try {
    $stmt = $conn->prepare("SELECT m.*, u.name, u.family, u.profile_pic, u.email 
                           FROM messages m 
                           JOIN users u ON m.sender_id = u.id 
                           WHERE m.id = ? AND m.receiver_id = ?");
    $stmt->bind_param("ii", $messageId, $userId);
    $stmt->execute();
    $message = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$message) {
        die("Message not found");
    }

    // Mark as read
    $conn->query("UPDATE messages SET is_read = TRUE WHERE id = $messageId");

    // Prepare data for reply
    $messageData = [
        'sender_id' => $message['sender_id'],
        'sender_name' => $message['name'] . ' ' . $message['family'],
        'subject' => $message['subject']
    ];

?>
    <input type="hidden" id="messageData" value='<?= json_encode($messageData) ?>'>

    <div class="row">
        <div class="col-md-2">
            <img src="<?= !empty($message['profile_pic']) ? htmlspecialchars($message['profile_pic']) : '../images/default_profile.png' ?>"
                class="rounded-circle img-fluid" alt="Sender">
        </div>
        <div class="col-md-10">
            <h5><?= htmlspecialchars($message['name'] . ' ' . $message['family']) ?></h5>
            <small class="text-muted"><?= htmlspecialchars($message['email']) ?></small>
            <p class="mt-2"><strong><?= htmlspecialchars($message['subject']) ?></strong></p>
            <p class="text-muted mb-2"><?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?></p>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="border-top pt-3">
                <?= nl2br(htmlspecialchars($message['content'])) ?>
            </div>
        </div>
    </div>
<?php
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>