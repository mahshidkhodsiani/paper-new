<?php
// این بخش باید قبل از هر خروجی HTML قرار گیرد
if (isset($_POST['initial_load']) || isset($_POST['search'])) {
    header('Content-Type: application/json; charset=utf-8');

    include 'config.php';

    // Logik for initial load or search
    if (isset($_POST['initial_load'])) {
        $sql = "SELECT * FROM user";
    } else {
        $searchTerm = $conn->real_escape_string($_POST['search']);
        $sql = "SELECT * FROM user WHERE name LIKE '%$searchTerm%'";
    }

    $result = $conn->query($sql);
    $titles = [];

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $titles[] = $row['name'];
            }
        }
        echo json_encode($titles);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }

    $conn->close();
    exit(); // مهم: بعد از ارسال JSON باید اسکریپت را متوقف کنید
}
?>

<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جستجو در بلاگ</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #resultsList li,
        #allnamesList li {
            list-style-type: none;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        #resultsList,
        #allnamesList {
            margin-top: 15px;
            padding: 0;
        }
    </style>
</head>

<body>
    <h2>جستجو در بلاگ</h2>
    <input type="text" id="searchInput" placeholder="عنوان را وارد کنید">
    <button id="searchButton">جستجو</button>

    <h3>نتایج جستجو</h3>
    <ul id="resultsList"></ul>

    <hr>

    <h3>همه تایتل‌ها</h3>
    <ul id="allnamesList"></ul>

    <script>
        $(document).ready(function() {
            // ... (بقیه کدهای جاوااسکریپت شما بدون تغییر)
            function loadAllTitles() {
                $.ajax({
                    url: 'index.php', // URL به همین فایل
                    method: 'POST',
                    data: {
                        initial_load: true
                    },
                    success: function(response) {
                        try {
                            var titles = JSON.parse(response);
                            $('#allnamesList').empty();
                            if (titles.length > 0) {
                                titles.forEach(function(name) {
                                    $('#allnamesList').append('<li>' + name + '</li>');
                                });
                            } else {
                                $('#allnamesList').append('<li>مطلب دیگری وجود ندارد.</li>');
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response: " + response);
                            alert('خطا در پردازش اطلاعات.');
                        }
                    },
                    error: function() {
                        alert('خطا در برقراری ارتباط با سرور برای بارگذاری اولیه.');
                    }
                });
            }

            loadAllTitles();

            $('#searchButton').click(function() {
                var searchTerm = $('#searchInput').val().trim();
                $('#resultsList').empty();
                $('#allnamesList').hide();

                if (searchTerm !== '') {
                    $.ajax({
                        url: 'index.php', // URL به همین فایل
                        method: 'POST',
                        data: {
                            search: searchTerm
                        },
                        success: function(response) {
                            try {
                                var titles = JSON.parse(response);
                                if (titles.length > 0) {
                                    titles.forEach(function(name) {
                                        $('#resultsList').append('<li>' + name + '</li>');
                                    });
                                } else {
                                    $('#resultsList').append('<li>نتیجه‌ای یافت نشد.</li>');
                                }
                            } catch (e) {
                                console.error("Error parsing JSON response: " + response);
                                alert('خطا در پردازش اطلاعات.');
                            }
                        },
                        error: function() {
                            alert('خطا در برقراری ارتباط با سرور.');
                        }
                    });
                } else {
                    alert('لطفا عبارتی را برای جستجو وارد کنید.');
                    $('#allnamesList').show();
                }
            });
        });
    </script>
</body>

</html>