<?php

include "config.php";

// Get the ID from the URL
$competition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($competition_id <= 0) {
    echo "Invalid competition ID.";
    exit;
}

// Fetch main competition details
$sql = "SELECT * FROM competitions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $competition_id);
$stmt->execute();
$result = $stmt->get_result();
$competition = $result->fetch_assoc();
$stmt->close();

if (!$competition) {
    echo "Competition not found.";
    exit;
}

// Fetch judges
$judges = [];
$sql_judges = "SELECT name, title FROM competition_judges WHERE competition_id = ?";
$stmt_judges = $conn->prepare($sql_judges);
$stmt_judges->bind_param("i", $competition_id);
$stmt_judges->execute();
$result_judges = $stmt_judges->get_result();
while ($row = $result_judges->fetch_assoc()) {
    $judges[] = $row;
}
$stmt_judges->close();

// Fetch awards
$awards = [];
$sql_awards = "SELECT award_name, award_value FROM competition_awards WHERE competition_id = ?";
$stmt_awards = $conn->prepare($sql_awards);
$stmt_awards->bind_param("i", $competition_id);
$stmt_awards->execute();
$result_awards = $stmt_awards->get_result();
while ($row = $result_awards->fetch_assoc()) {
    $awards[] = $row;
}
$stmt_awards->close();

$conn->close();

// Calculate competition status
$status = 'Upcoming';
$now = new DateTime();
$start_date = new DateTime($competition['start_date']);
$end_date = new DateTime($competition['end_date']);
if ($now > $start_date && $now < $end_date) {
    $status = 'Active';
} elseif ($now >= $end_date) {
    $status = 'Completed';
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Competition Details: <?php echo htmlspecialchars($competition['competition_title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .hero-section {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 4rem;
        }

        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .status-badge {
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="hero-section text-center mb-5">
            <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($competition['competition_title']); ?></h1>
            <span class="badge bg-primary rounded-pill status-badge mb-3"><?php echo $status; ?></span>
            <p class="lead text-muted"><?php echo htmlspecialchars($competition['organizer_name']); ?></p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="section-card mb-4">
                    <h3 class="mb-3">About the Competition</h3>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($competition['competition_description'])); ?></p>
                </div>

                <div class="section-card mb-4">
                    <h3 class="mb-3">Key Details</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Start Date</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($competition['start_date']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">End Date</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($competition['end_date']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Participation Access</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($competition['participation_access']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Voting System</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($competition['voting_system']); ?></p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($judges)) : ?>
                    <div class="section-card mb-4">
                        <h3 class="mb-3">Judges</h3>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($judges as $judge) : ?>
                                <li class="list-group-item">
                                    <p class="mb-1 fw-bold"><?php echo htmlspecialchars($judge['name']); ?></p>
                                    <small class="text-muted"><?php echo htmlspecialchars($judge['title']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($competition['submission_guidelines']) : ?>
                    <div class="section-card mb-4">
                        <h3 class="mb-3">Submission Guidelines</h3>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($competition['submission_guidelines'])); ?></p>
                    </div>
                <?php endif; ?>

            </div>

            <div class="col-lg-4">
                <div class="section-card mb-4">
                    <h4 class="mb-3">Prizes & Awards</h4>
                    <?php if (!empty($awards)) : ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($awards as $award) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <p class="mb-0"><?php echo htmlspecialchars($award['award_name']); ?></p>
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($award['award_value']); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="text-muted">No information about prizes is available.</p>
                    <?php endif; ?>
                </div>

                <div class="section-card">
                    <h4 class="mb-3">Participate</h4>
                    <p class="text-muted">Click the button below to participate in this competition.</p>
                    <a href="#" class="btn btn-primary w-100 btn-lg">Register Now</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>