<?php
session_start();
// Require config.php to ensure database connection and necessary configurations
require_once __DIR__ . '/../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_data']) || !isset($_SESSION['user_data']['id'])) {
    header('Location: /login.php'); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_data']['id']; // Get the logged-in user's ID
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن ارائه جدید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            max-width: 700px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h2><i class="fas fa-file-powerpoint me-2"></i>افزودن ارائه جدید</h2>
            </div>
            <div class="card-body">
                <form id="presentationForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">

                    <div class="mb-3">
                        <label for="presentationTitle" class="form-label">عنوان ارائه:</label>
                        <input type="text" class="form-control" id="presentationTitle" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="presentationDescription" class="form-label">توضیحات:</label>
                        <textarea class="form-control" id="presentationDescription" name="description" rows="5" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="presentationFile" class="form-label">فایل ارائه (PDF/PPTX):</label>
                        <input type="file" class="form-control" id="presentationFile" name="file" accept=".pdf,.pptx" required>
                        <div class="form-text">فقط فایل‌های PDF یا PPTX مجاز هستند.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i>آپلود ارائه
                    </button>
                </form>
                <div id="responseMessage" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#presentationForm').submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                const formData = new FormData(this); // Get form data, including file

                $.ajax({
                    url: 'upload_presentation.php', // Target PHP file for upload
                    type: 'POST',
                    data: formData,
                    processData: false, // Don't process the data (required for FormData)
                    contentType: false, // Don't set content type (required for FormData)
                    dataType: 'json', // Expect JSON response
                    beforeSend: function() {
                        $('#responseMessage').html('<div class="alert alert-info">در حال آپلود...</div>');
                        $('button[type="submit"]').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#responseMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#presentationForm')[0].reset(); // Clear the form
                        } else {
                            $('#responseMessage').html('<div class="alert alert-danger">خطا: ' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#responseMessage').html('<div class="alert alert-danger">یک خطای غیرمنتظره رخ داد: ' + xhr.responseText + '</div>');
                        console.error("AJAX Error: ", status, error, xhr.responseText);
                    },
                    complete: function() {
                        $('button[type="submit"]').prop('disabled', false);
                    }
                });
            });
        });
    </script>

    <?php include "footer.php"; ?>

</body>

</html>