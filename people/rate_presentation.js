document.addEventListener("DOMContentLoaded", function () {
  const ratingForms = document.querySelectorAll(".rating-form");

  ratingForms.forEach((form) => {
    const stars = form.querySelectorAll(".rating-star");
    const submitBtn = form.querySelector(".submit-rating-btn");
    const commentBox = form.querySelector("textarea");
    const presentationId = form.dataset.presentationId;
    const commentBoxContainer = document.getElementById(
      "comment-box-" + presentationId
    );

    let selectedRating = 0;

    stars.forEach((star) => {
      star.addEventListener("click", function () {
        selectedRating = this.dataset.rating;
        stars.forEach((s) => {
          if (s.dataset.rating <= selectedRating) {
            s.classList.replace("far", "fas");
          } else {
            s.classList.replace("fas", "far");
          }
        });
        commentBoxContainer.style.display = "block";
        submitBtn.style.display = "inline-block";
      });
    });

    submitBtn.addEventListener("click", function () {
      if (selectedRating > 0) {
        const comment = commentBox.value;
        fetch("../profile/rate_presentation.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `presentation_id=${presentationId}&rating_value=${selectedRating}&comment=${encodeURIComponent(
            comment
          )}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status === "success") {
              // Now it just shows the message without reloading the page
              window.showMessage(data.message, "success");
            } else {
              window.showMessage(data.message, "danger");
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            window.showMessage(
              "An error occurred. Please try again.",
              "danger"
            );
          });
      } else {
        window.showMessage("Please select a star to rate.", "warning");
      }
    });
  });
});
