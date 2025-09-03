<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Multi-step Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light p-5">

<div class="container">
  <ul class="nav nav-tabs" id="formTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab">
        1. Organizer & Branding
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link disabled" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab">
        2. Competition Details
      </button>
    </li>
  </ul>

  <div class="tab-content border border-top-0 p-4 bg-white">
    
    <!-- Tab 1 -->
    <div class="tab-pane fade show active" id="tab1" role="tabpanel">
      <form id="formStep1">
        <div class="mb-3">
          <label class="form-label">Organization *</label>
          <input type="text" class="form-control" name="organization" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Organizer Contact Email *</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Logo (optional)</label>
          <input type="file" class="form-control" name="logo" accept="image/*">
        </div>
        <button type="button" class="btn btn-primary" id="goToTab2">Next</button>
      </form>
    </div>

    <!-- Tab 2 -->
    <div class="tab-pane fade" id="tab2" role="tabpanel">
      <form id="formStep2">
        <div class="mb-3">
          <label class="form-label">Competition Title *</label>
          <input type="text" class="form-control" name="title" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description *</label>
          <textarea class="form-control" name="description" required></textarea>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Start Date *</label>
            <input type="date" class="form-control" name="start_date" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">End Date *</label>
            <input type="date" class="form-control" name="end_date" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Timezone</label>
          <input type="text" class="form-control" name="timezone" placeholder="America/Chicago (CT)">
        </div>
        <div class="mb-3">
          <label class="form-label">Room / Meeting Link</label>
          <input type="text" class="form-control" name="room">
        </div>
        <div class="mb-3">
          <label class="form-label">Session / Track</label>
          <input type="text" class="form-control" name="track" placeholder="AI, Bio, Design">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Presentation Duration (minutes)</label>
            <input type="number" class="form-control" name="duration" value="12">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Buffer Between Presentations (minutes)</label>
            <input type="number" class="form-control" name="buffer" value="3">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Presentation Order</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="order" value="randomize" checked>
            <label class="form-check-label">Randomize</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="order" value="lock">
            <label class="form-check-label">Lock order after publishing</label>
          </div>
        </div>
        <button type="submit" class="btn btn-success">Submit</button>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById("goToTab2").addEventListener("click", function () {
  const formStep1 = document.getElementById("formStep1");
  if (formStep1.checkValidity()) {
    const tab2 = document.getElementById("tab2-tab");
    tab2.classList.remove("disabled");
    tab2.click();
  } else {
    formStep1.reportValidity();
  }
});
</script>

</body>
</html>
