<?php
// Set variables for messages
$message = '';
$message_type = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // آدرس ایمیلی که می‌خواید پیام‌ها بهش ارسال بشن
    $to_email = 'info@paperet.com';  // ← اینو با ایمیل واقعی خودتون عوض کنید

    // Collect and sanitize form data
    $requester_name = htmlspecialchars($_POST['requester_name'] ?? '');
    $requester_email = htmlspecialchars($_POST['requester_email'] ?? '');
    $requester_affiliation = htmlspecialchars($_POST['requester_affiliation'] ?? '');
    $requester_phone = htmlspecialchars($_POST['requester_phone'] ?? '');

    $presenter_name = htmlspecialchars($_POST['presenter_name'] ?? '');
    $presenter_email = htmlspecialchars($_POST['presenter_email'] ?? '');
    $presenter_affiliation = htmlspecialchars($_POST['presenter_affiliation'] ?? '');

    $paper_title = htmlspecialchars($_POST['paper_title'] ?? '');
    $paper_link = htmlspecialchars($_POST['paper_link'] ?? '');
    $paper_abstract = htmlspecialchars($_POST['paper_abstract'] ?? '');
    $tags = htmlspecialchars($_POST['tags'] ?? '');
    $is_online = isset($_POST['is_online']) ? 'Yes' : 'No';

    $include_comp = isset($_POST['include_comp']) && $_POST['include_comp'] == '1' ? 'Yes' : 'No';
    $comp_name = htmlspecialchars($_POST['comp_name'] ?? '');
    $comp_link = htmlspecialchars($_POST['comp_link'] ?? '');
    $comp_message = htmlspecialchars($_POST['comp_message'] ?? '');

    $custom_message = htmlspecialchars($_POST['custom_message'] ?? '');
    $cc_emails_str = htmlspecialchars($_POST['cc_emails'] ?? '');

    // Validate required fields
    if (empty($requester_name) || empty($requester_email) || empty($presenter_name) || empty($presenter_email) || empty($paper_title) || empty($paper_abstract) || !isset($_POST['consent'])) {
        $message_type = 'error';
        $message = 'لطفاً تمام فیلدهای الزامی (*) را پر کنید.';
    } else {
        // Build the email subject and body
        $subject = "New Presentation Submission: " . $paper_title;
        $email_body = "A new presentation request has been submitted with the following details:\n\n";

        // Requester Details
        $email_body .= "--- Requester Details ---\n";
        $email_body .= "Name: " . $requester_name . "\n";
        $email_body .= "Email: " . $requester_email . "\n";
        $email_body .= "Affiliation: " . $requester_affiliation . "\n";
        $email_body .= "Phone: " . $requester_phone . "\n\n";

        // Presenter Details
        $email_body .= "--- Presenter Details ---\n";
        $email_body .= "Name: " . $presenter_name . "\n";
        $email_body .= "Email: " . $presenter_email . "\n";
        $email_body .= "Affiliation: " . $presenter_affiliation . "\n\n";

        // Paper Details
        $email_body .= "--- Paper Details ---\n";
        $email_body .= "Paper Title: " . $paper_title . "\n";
        $email_body .= "Paper Link: " . ($paper_link ?: 'N/A') . "\n";
        $email_body .= "Present Online: " . $is_online . "\n";
        $email_body .= "Tags: " . ($tags ?: 'N/A') . "\n";
        $email_body .= "Abstract:\n" . $paper_abstract . "\n\n";

        // Competition Details
        $email_body .= "--- Competition Details ---\n";
        $email_body .= "Submitting for Competition: " . $include_comp . "\n";
        if ($include_comp === 'Yes') {
            $email_body .= "Competition Name: " . ($comp_name ?: 'N/A') . "\n";
            $email_body .= "Competition Link: " . ($comp_link ?: 'N/A') . "\n";
            $email_body .= "Message for Competition: " . ($comp_message ?: 'N/A') . "\n\n";
        } else {
            $email_body .= "N/A\n\n";
        }

        // Other Details
        $email_body .= "--- Other Details ---\n";
        $email_body .= "Custom Message: " . ($custom_message ?: 'N/A') . "\n";
        $email_body .= "CC Emails: " . ($cc_emails_str ?: 'N/A') . "\n\n";

        // تنظیم هدرها (از ایمیل دامنه استفاده کنید تا اسپم نشه)
        $headers = "From: info@paperet.com\r\n"; // ← باید با ایمیل هاست خودتون یکی باشه
        $headers .= "Reply-To: " . $requester_email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (!empty($cc_emails_str)) {
            $headers .= "Cc: " . $cc_emails_str . "\r\n";
        }


        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Send the email
        if (mail($to_email, $subject, $email_body, $headers)) {
            $message_type = 'success';
            $message = 'درخواست شما با موفقیت ارسال شد. متشکریم!';
        } else {
            $message_type = 'error';
            $message = 'مشکلی در ارسال درخواست شما پیش آمد. لطفاً دوباره تلاش کنید.';
        }
    }
}
