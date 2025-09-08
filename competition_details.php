<?php

include "config.php"; // Database connection
include "vendor/phpmailer/phpmailer/src/Exception.php"; // To ensure access to PHPMailer

// Function to validate and clean data
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if competition ID exists and is valid in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If ID is invalid, display an error page
    header("Location: error_page_not_found.php"); // A custom 404 page
    exit();
}

$competition_id = sanitizeInput($_GET['id']);

// --- Retrieve competition data from the database ---
$competition_data = null;
$stmt = $conn->prepare("SELECT * FROM competitions WHERE id = ?");
$stmt->bind_param("i", $competition_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $competition_data = $result->fetch_assoc();
} else {
    // If no competition with this ID is found
    header("Location: error_page_not_found.php"); // Or show a 404 error
    exit();
}
$stmt->close();

// --- Retrieve judges' information ---
$judges = [];
$stmt = $conn->prepare("SELECT name, email, title, linkedin_url FROM competition_judges WHERE competition_id = ?");
$stmt->bind_param("i", $competition_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $judges[] = $row;
}
$stmt->close();

// --- Retrieve participants' information ---
$participants = [];
$stmt = $conn->prepare("SELECT name, email FROM competition_participants WHERE competition_id = ?");
$stmt->bind_param("i", $competition_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $participants[] = $row;
}
$stmt->close();

$conn->close();
?>

<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($competition_data['competition_title']); ?> — Paperet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .competition-header {
            background-color: #ffffff;
            padding: 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .competition-body {
            padding: 2rem 0;
        }

        .card {
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>


    <style>
        /* استایل‌های شما */
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
    <nav>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="../">
                    <img src="../images\logo.png" alt="paperet" style="height: 30px;">
                </a>
            </li>
            <li class="nav-item mx-auto d-flex align-items-center" style="width: 50%;">
                <form class="d-flex w-100 search-container" role="search" method="GET" action="search.php">

                    <input class="form-control me-2" type="search" name="query" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
                    <button class="btn btn-info" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <div id="suggestions" class="search-results-box" style="display: none;"></div>
                </form>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="#" class="nav-link px-2 link-secondary"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                    <li><a href="../people" class="nav-link px-2 link-dark"><i class="fas fa-users"></i> People</a></li>
                    <li>
                        <a href="" class="nav-link px-2 link-dark"><i class="fas fa-flask"></i> Labs</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item m-1">
                <a class="btn btn-info" href="../login">Sign in</a>
            </li>
            <li class="nav-item m-1">
                <a class="btn btn-info" href="../register">Sign up</a>
            </li>
        </ul>
    </nav>


    <main class="container mt-5">
        <div class="competition-header text-center">
            <h1><?php echo htmlspecialchars($competition_data['competition_title']); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($competition_data['organizer_name']); ?></p>
        </div>

        <div class="competition-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Competition Description</h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($competition_data['competition_description'])); ?></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Start Date:</strong> <?php echo htmlspecialchars($competition_data['start_date']); ?></li>
                                <li class="list-group-item"><strong>End Date:</strong> <?php echo htmlspecialchars($competition_data['end_date']); ?></li>
                                <li class="list-group-item"><strong>Access:</strong> <?php echo htmlspecialchars($competition_data['participation_access'] == 'open' ? 'Public' : 'Invitation Only'); ?></li>
                                <li class="list-group-item"><strong>Submission Type:</strong> <?php echo htmlspecialchars($competition_data['submission_type']); ?></li>
                            </ul>
                        </div>
                    </div>

                    <?php if (!empty($judges)): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Judges</h5>
                                <div class="row">
                                    <?php foreach ($judges as $judge): ?>
                                        <div class="col-sm-6 mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($judge['name']); ?></h6>
                                                    <p class="card-text small">
                                                        <?php echo htmlspecialchars($judge['title']); ?><br>
                                                        <a href="mailto:<?php echo htmlspecialchars($judge['email']); ?>"><?php echo htmlspecialchars($judge['email']); ?></a><br>
                                                        <?php if (!empty($judge['linkedin_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($judge['linkedin_url']); ?>" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($participants)): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Participants</h5>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($participants as $participant): ?>
                                        <li class="list-group-item"><?php echo htmlspecialchars($participant['name']); ?> (<?php echo htmlspecialchars($participant['email']); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Additional Information</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Voting System:</strong> <?php echo htmlspecialchars($competition_data['voting_system']); ?></li>
                                <li class="list-group-item"><strong>Max Participants:</strong> <?php echo htmlspecialchars($competition_data['max_participants']); ?></li>
                                <li class="list-group-item"><strong>Results Visibility:</strong> <?php echo htmlspecialchars($competition_data['results_visibility']); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php include "footer.php"; ?>
</body>

</html>