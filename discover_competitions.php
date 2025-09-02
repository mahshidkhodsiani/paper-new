<?php
// discover_competitions.php

// این خط فایل اتصال به دیتابیس را فراخوانی می‌کند.
include "config.php";

// این خط فایل هدر سایت را فراخوانی می‌کند.
include 'header.php';

// کوئری برای دریافت تمام مسابقات از دیتابیس
$sql = "SELECT * FROM competitions ORDER BY start_date DESC";
$result = $conn->query($sql);

$competitions = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $competitions[] = $row;
  }
}
?>

<style>
  :root {
    --brand: #0ea5b3;
    --brand-dark: #0c8a96;
    --brand-light: #ecfeff;
    --text: #0f172a;
    --text-light: #64748b;
    --bg: #f8fafc;
    --surface: #ffffff;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --success: #10b981;
    --warning: #f59e0b;
    --error: #ef4444;
    --radius: 12px;
    --radius-sm: 8px;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  }

  body {
    background-color: var(--bg);
    color: var(--text);
  }

  .container {
    max-width: 1200px;
    margin: auto;
    padding: 24px;
  }

  .section-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text);
  }

  .filters-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding: 16px;
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
  }

  .filters-bar select,
  .filters-bar input,
  .filters-bar button {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    font-size: 1rem;
  }

  .grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
  }

  .competition-card {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease-in-out;
  }

  .competition-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
  }

  .card-header {
    height: 120px;
    position: relative;
    background-color: var(--brand);
    color: white;
    display: flex;
    align-items: flex-end;
    padding: 16px;
  }

  .card-header .status-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background-color: rgba(255, 255, 255, 0.85);
    color: var(--text);
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
  }

  .card-header .status-badge.active {
    background-color: var(--success);
    color: white;
  }

  .card-header .status-badge.upcoming {
    background-color: var(--brand-light);
    color: var(--brand-dark);
  }

  .card-header .status-badge.completed {
    background-color: var(--text-light);
    color: white;
  }

  .card-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
  }

  .card-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }

  .card-body .org-name {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 8px;
  }

  .card-body .competition-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 12px;
  }

  .card-body .competition-details span {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .card-body .description {
    font-size: 0.95rem;
    color: var(--text);
    flex-grow: 1;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
  }

  .card-tags .tag {
    background-color: var(--border-light);
    color: var(--text-light);
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
  }

  .card-footer {
    padding: 16px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
  }

  .card-footer .prize-info {
    font-weight: 600;
    color: var(--brand-dark);
    font-size: 1rem;
  }

  .btn {
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    font-weight: 500;
    text-decoration: none;
    text-align: center;
  }

  .btn-primary {
    background-color: var(--brand);
    color: white;
    border: none;
  }

  .btn-primary:hover {
    background-color: var(--brand-dark);
    color: white;
  }

  .btn-secondary {
    background-color: var(--border-light);
    color: var(--text-light);
    border: none;
  }

  .btn-secondary:hover {
    background-color: var(--border);
  }
</style>

<div class="container">
  <h1 class="section-title">Explore Competitions</h1>
  <div class="filters-bar">
    <div>
      <label for="sort_by">Sort by:</label>
      <select id="sort_by">
        <option value="date">Newest</option>
        <option value="popularity">Popularity</option>
      </select>
    </div>
    <div>
      <label for="category_filter">Category:</label>
      <select id="category_filter">
        <option value="all">All</option>
        <option value="AI">AI</option>
        <option value="Biomed">Biomed</option>
        <option value="Art">Art</option>
        <option value="Engineering">Engineering</option>
      </select>
    </div>
    <button class="btn btn-primary" onclick="window.location.href='host_competetion.php'">Host a Competition</button>
  </div>

  <div class="grid-container" id="competitions-grid">
    <?php
    if (!empty($competitions)) {
      foreach ($competitions as $competition) {
        $status_class = 'upcoming';
        $status_text = 'Upcoming';
        if (strtotime($competition['end_date']) < time()) {
          $status_class = 'completed';
          $status_text = 'Completed';
        } elseif (strtotime($competition['start_date']) <= time()) {
          $status_class = 'active';
          $status_text = 'Active';
        }
    ?>
        <div class="competition-card">
          <div class="card-header" style="background-color: var(--brand);">
            <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
            <h3><?= htmlspecialchars($competition['title']) ?></h3>
          </div>
          <div class="card-body">
            <span class="org-name"><?= htmlspecialchars($competition['org_name']) ?></span>
            <p class="description"><?= htmlspecialchars($competition['description']) ?></p>
            <div class="competition-details">
              <span><i class="fa fa-calendar-alt"></i> <?= htmlspecialchars(date('M d, Y', strtotime($competition['start_date']))) ?></span>
            </div>
          </div>
          <div class="card-footer">
            <span class="prize-info"><?= htmlspecialchars($competition['prizes']) ?></span>
            <button class="btn btn-primary">Details</button>
          </div>
        </div>
    <?php
      }
    } else {
      echo "<p>No competitions found. Be the first to host one!</p>";
    }
    ?>
  </div>
</div>

<?php
// این خط فایل فوتر سایت را فراخوانی می‌کند.
include 'footer.php';
?>