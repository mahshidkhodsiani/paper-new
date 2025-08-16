// search.js

$(document).ready(function () {
  const searchInput = $('input[name="query"]');
  const suggestionsBox = $("#suggestions");
  const searchForm = $("#search-form"); // مطمئن شوید که فرم شما id="search-form" دارد
  let timeout = null;

  function fetchResults(query) {
    if (query.length < 3) {
      suggestionsBox.hide().html("");
      return;
    }

    clearTimeout(timeout);
    timeout = setTimeout(function () {
      $.ajax({
        url: "search.php",
        method: "GET",
        data: { query: query, ajax: true }, // اضافه کردن پارامتر ajax برای تشخیص نوع درخواست
        dataType: "html",
        success: function (data) {
          suggestionsBox.html(data).show();
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          suggestionsBox
            .html('<div class="list-group-item">خطا در بارگذاری نتایج.</div>')
            .show();
        },
      });
    }, 300); // 300 میلی‌ثانیه تأخیر
  }

  searchInput.on("keyup", function (e) {
    if (e.keyCode === 13) {
      searchForm.submit(); // ارسال فرم هنگام فشردن اینتر
    } else {
      fetchResults($(this).val());
    }
  });

  // مخفی کردن باکس نتایج وقتی روی جای دیگری کلیک شود
  $(document).on("click", function (event) {
    if (!$(event.target).closest(".search-container").length) {
      suggestionsBox.hide();
    }
  });

  // نمایش دوباره باکس وقتی روی input کلیک شود
  searchInput.on("focus", function () {
    if (suggestionsBox.html().trim() !== "") {
      suggestionsBox.show();
    }
  });
});
