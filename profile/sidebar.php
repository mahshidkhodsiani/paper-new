<div class="col-md-3">
    <div class="sidebar-content shadow p-3 mb-5 bg-white rounded">
        <div class="text-center mb-4">

            <img decoding="async" width="150" height="150"
                src="../<?= !empty($_SESSION['user_data']['profile_pic']) ? $_SESSION['user_data']['profile_pic'] : '../images/2.png'; ?>"
                class="img-fluid rounded-circle" alt="profile-pic">
        </div>

        <div class="text-center mb-4">
            <p class="">
                <i class="far fa-user-circle"></i>
                <?= $_SESSION['user_data']['name'] . " " . $_SESSION['user_data']['family']; ?>
            </p>
        </div>

        <div class="list-group">
            <a class="list-group-item list-group-item-action" href="./">Main</a>
            <a class="list-group-item list-group-item-action" href="settings.php">Settings</a>
            <a class="list-group-item list-group-item-action active" href="resume-media.php">Resume And Introduction Media</a>
            <a class="list-group-item list-group-item-action" href="my_presentations.php">My Presentations</a>
            <a class="list-group-item list-group-item-action" href="saved-presentations.php">Saved Presentations</a>
            <a class="list-group-item list-group-item-action" href="saved_peoples.php">Connections</a>
            <a class="list-group-item list-group-item-action" href="messages.php">Messages</a>
            <a class="list-group-item list-group-item-action" href="my_requests.php">My requests</a>
        </div>
    </div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // دریافت مسیر فعلی صفحه
        var currentPath = window.location.pathname;
        var fileName = currentPath.split('/').pop(); // فقط نام فایل رو استخراج می کنیم

        // حذف کلاس active از تمام لینک ها
        $('.list-group-item-action').removeClass('active');

        // اضافه کردن کلاس active به لینکی که آدرسش با نام فایل فعلی مطابقت دارد
        $('.list-group-item-action').each(function() {
            var linkHref = $(this).attr('href');
            var linkFileName = linkHref.split('/').pop();

            if (linkFileName === fileName) {
                $(this).addClass('active');
            }
        });

        // برای مدیریت کلیک ها در صورتی که نیاز به تغییر آدرس صفحه نداشته باشید
        // $('.list-group-item-action').on('click', function(e) {
        //     e.preventDefault(); // جلوی رفتن به آدرس جدید رو میگیره اگر نمیخواهید صفحه رفرش بشه
        //     $('.list-group-item-action').removeClass('active');
        //     $(this).addClass('active');
        //     // اینجا میتونید محتوای صفحه رو با Ajax لود کنید
        // });
    });
</script>