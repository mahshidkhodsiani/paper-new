<div class="col-md-3">
    <div class="sidebar-content shadow p-3 mb-5 bg-white rounded">
        <div class="text-center mb-4">
            <img decoding="async" width="150" height="150"
                src="../<?= !empty($user['profile_pic']) ? $user['profile_pic'] : '../images/2.png'; ?>"
                class="img-fluid rounded-circle" alt="profile-pic">
        </div>

        <div class="text-center mb-4">
            <p class="">
                <i class="far fa-user-circle"></i>
                <?= $user['name'] . " " . $user['family']; ?>
            </p>
        </div>

        <div class="list-group">
            <?php
            // بررسی می‌کنیم که آیا کاربر لاگین کرده و ID سشن موجود است
            if (isset($_SESSION['user_data']['id'])) {
                $loggedInUserId = $_SESSION['user_data']['id'];
                // فقط در صورتی دکمه نمایش داده شود که ID کاربر لاگین کرده با ID پروفایل یکی نباشد
                if ($loggedInUserId != $user['id']) {
            ?>
                    <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#messageModal">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
            <?php
                }
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal ارسال پیام -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">New Message to <?= $user['name'] . ' ' . $user['family'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="messageForm">
                    <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
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
                <button type="button" class="btn btn-primary" id="sendMessageBtn">Send</button>
            </div>
        </div>
    </div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#sendMessageBtn').click(function() {
            const formData = $('#messageForm').serialize();

            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#sendMessageBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#messageModal').modal('hide');
                        alert('Message sent successfully!');
                        $('#messageForm')[0].reset();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while sending the message.');
                },
                complete: function() {
                    $('#sendMessageBtn').prop('disabled', false).text('Send');
                }
            });
        });
    });
</script>