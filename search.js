$(document).ready(function () {
  // از input مربوط به جستجو و div مربوط به نمایش نتایج استفاده می‌کنیم
  const searchInput = $('input[name="query"]');
  const suggestionsBox = $("#suggestions");

  // تابع برای جستجو و نمایش نتایج
  function fetchResults(query) {
    // شرط حداقل ۳ کاراکتر
    if (query.length < 3) {
      suggestionsBox.hide().html("");
      return;
    }

    $.ajax({
      url: "/search.php", // مسیر فایل search.php به صورت مطلق
      method: "GET",
      data: { query: query },
      dataType: "html", // نوع داده مورد انتظار
      success: function (data) {
        // نمایش نتایج در باکس
        suggestionsBox.html(data).show();

        // محدود کردن نتایج به ۵ مورد
        const listItems = suggestionsBox.find(".list-group-item");
        if (listItems.length > 5) {
          listItems.slice(5).remove();
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
      },
    });
  }

  // رویداد keyup برای تشخیص تایپ کاربر
  searchInput.on("keyup", function () {
    fetchResults($(this).val());
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
