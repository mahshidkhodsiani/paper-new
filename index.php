<?php

// این خط تمام خطاهای PHP را فعال می‌کند.
error_reporting(E_ALL);

// این خط نمایش خطاها را در مرورگر فعال می‌کند.
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>paperet</title>

    <?php
    // مطمئن شوید این فایل‌ها در مسیر صحیح قرار دارند
    include "config.php";
    include "includes.php";
    ?>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .btn-outline-info {
            color: #17a2b8;
            border-color: #17a2b8;
            width: 300px;
        }

        .btn-outline-info:hover {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        /* استایل‌های مربوط به جستجو */
        .search-container {
            position: relative;
        }

        .search-results-box {
            position: absolute;
            width: 100%;
            top: 100%;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ccc;
            border-top: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container">
        <div class="row mt-5 align-items-center">
            <div class="col-md-6">
                <img src="images/Metrics-pana.png" class="img-fluid">
            </div>

            <div class="col-md-6 text-center">
                <h2>Your Journey Starts Right Here!</h2>
                <p>Showcase Yourself To The World</p>

                <a href="profile" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-id-card-alt"></i> Your Profile
                </a>
                <br>

                <button class="btn btn-outline-info rounded mb-3" id="google-signin-btn">
                    <i class="fab fa-google"></i> Sign in/up with Google
                </button>
                <br>

                <!-- <a href="" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-file-contract"></i> Term and Conditions
                </a> -->


                <a href="present_request_form" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-microphone"></i> Request To Present
                </a>
                <br>

                <a href="host_competition" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-trophy"></i> Host a competition
                </a>
                <br>
     

                <!-- <a href="" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-flask"></i> Build your Lab Page
                </a> -->

                <a href="discover_competitions" class="btn btn-outline-info rounded mb-3">
                    <i class="fas fa-compass"></i> Discover Competitions
                </a>

                <p class="mt-3 small text-muted">
                    By using this site, you agree to our
                    <a href="terms" class="text-decoration-none">Terms and Conditions</a>.
                </p>



            </div>
        </div>

        <br />

        <div class="row align-items-center">
            <div class="col-md-6 text-center ">
                <h6>Paperet</h6>
                <h2>Take a peek into who we are and what we do</h2>
                <p>We believe in sharing our journey with you. Learn more about our mission, values, and the passion behind our work.</p>
            </div>
            <div class="col-md-6">
                <img src="images/Webinar-pana.png" class="img-fluid">
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="query"]');
            const suggestionsBox = document.getElementById('suggestions');
            const form = document.querySelector('form[role="search"]');

            function showSuggestions(data) {
                suggestionsBox.innerHTML = '';
                if (data.length > 0) {
                    const ul = document.createElement('ul');
                    ul.classList.add('list-group');
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.classList.add('list-group-item', 'list-group-item-action');
                        li.innerHTML = `<strong>${item.title}</strong><br><small>${item.description}</small>`;
                        li.onclick = function() {
                            searchInput.value = item.title;
                            suggestionsBox.style.display = 'none';
                            window.location.href = item.file_path;
                        };
                        ul.appendChild(li);
                    });
                    suggestionsBox.appendChild(ul);
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            }

            searchInput.addEventListener('keyup', function() {
                const query = this.value;
                if (query.length > 2) {
                    // آدرس فایل PHP را به 'search_live.php' تغییر دهید
                    fetch(`search_live.php?query=${encodeURIComponent(query)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const suggestionItems = doc.querySelectorAll('.list-group-item');

                            const suggestionsData = [];
                            suggestionItems.forEach(item => {
                                const titleElement = item.querySelector('h5 > a');
                                const descriptionElement = item.querySelector('p');

                                // بررسی وجود تگ‌ها قبل از دسترسی به خصوصیات
                                if (titleElement && descriptionElement) {
                                    suggestionsData.push({
                                        title: titleElement.textContent,
                                        description: descriptionElement.textContent,
                                        file_path: titleElement.href
                                    });
                                }
                            });
                            showSuggestions(suggestionsData);
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (!form.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        });

        // کدهای مربوط به Google Sign-in
        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo GOOGLE_REDIRECT_URI; ?>';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        window.onload = function() {
            google.accounts.id.initialize({
                client_id: "<?php echo GOOGLE_CLIENT_ID; ?>",
                callback: handleCredentialResponse
            });

            const signinButton = document.getElementById('google-signin-btn');
            signinButton.onclick = () => {
                google.accounts.id.prompt();
            };
        };
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const searchInput = $('input[name="query"]');
            const suggestionsBox = $('#suggestions');
            const searchForm = $('.search-container');
            let timeout = null;

            searchInput.on('keyup', function() {
                clearTimeout(timeout);
                const query = $(this).val();

                if (query.length > 2) { // جستجو با حداقل 3 کاراکتر
                    timeout = setTimeout(function() {
                        $.ajax({
                            url: 'search.php',
                            type: 'GET',
                            data: {
                                query: query
                            },
                            success: function(data) {
                                suggestionsBox.html(data).show();
                            },
                            error: function() {
                                suggestionsBox.html('<div class="list-group-item">خطا در بارگذاری نتایج.</div>').show();
                            }
                        });
                    }, 300); // تأخیر 300 میلی‌ثانیه‌ای برای جلوگیری از ارسال درخواست‌های زیاد
                } else {
                    suggestionsBox.hide().empty();
                }
            });

            // مخفی کردن باکس پیشنهادات هنگام کلیک در خارج از آن
            $(document).on('click', function(e) {
                if (!searchForm.is(e.target) && searchForm.has(e.target).length === 0) {
                    suggestionsBox.hide();
                }
            });

            // نمایش مجدد باکس پیشنهادات هنگام کلیک روی فیلد جستجو
            searchInput.on('focus', function() {
                if (suggestionsBox.html().trim() !== '') {
                    suggestionsBox.show();
                }
            });

        });
    </script>
</body>

</html>