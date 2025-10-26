<?php

session_start();
include "config.php";

// Assume the logged-in user's ID and Organizer name are stored here in the session.
// You must ensure these variables are set after login.
$loggedInOrganizerName = isset($_SESSION['user_organizer_name']) ? $_SESSION['user_organizer_name'] : null;
$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


function get_competitions()
{
    global $conn;
    $competitions = [];

    // FIX: Changed column name from 'organizer_user_id' to the correct 'user_id'
    $sql = "SELECT id, competition_title, organizer_name, start_date, end_date, submission_type, user_id FROM competitions";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = 'Upcoming';
            $now = new DateTime();
            // Handle case where date might be null or invalid
            try {
                $start_date = new DateTime($row['start_date']);
                $end_date = new DateTime($row['end_date']);

                if ($now > $start_date && $now < $end_date) {
                    $status = 'Active';
                } elseif ($now >= $end_date) {
                    $status = 'Completed';
                }
            } catch (Exception $e) {
                // Keep default 'Upcoming' or handle date error
            }


            $competitions[] = [
                'id' => $row['id'],
                'title' => $row['competition_title'],
                'organizer' => $row['organizer_name'],
                'date' => $row['start_date'],
                'prize' => 'TBD',
                'status' => $status,
                'format' => 'TBD',
                'submission' => $row['submission_type'],
                // FIX: Use the correct DB field 'user_id' and map it to our internal array key
                'organizer_user_id' => $row['user_id'],
            ];
        }
    }

    // Close connection before sorting
    // NOTE: If you use $conn in other parts of the script after this call, you should remove this line.
    // For now, I will keep it commented out to prevent unexpected 'connection closed' errors
    // $conn->close(); 

    // CUSTOM SORTING LOGIC: Priority for user_id = 1, then sort by date DESC
    usort($competitions, function ($a, $b) {
        // Check if the competition is from the main Paperet account (ID = 1)
        $is_a_paperet = (isset($a['organizer_user_id']) && $a['organizer_user_id'] == '1');
        $is_b_paperet = (isset($b['organizer_user_id']) && $b['organizer_user_id'] == '1');

        // 1. Prioritize Paperet Competitions
        if ($is_a_paperet && !$is_b_paperet) {
            return -1; // A comes before B
        }
        if (!$is_a_paperet && $is_b_paperet) {
            return 1; // B comes before A
        }

        // 2. If both are Paperet OR neither are, sort by date (DESC - latest date first)
        $date_a = strtotime($a['date']);
        $date_b = strtotime($b['date']);

        // Return 1 if A's date is older than B's date (puts B first). Return -1 if A is newer (puts A first).
        if ($date_a == $date_b) {
            return 0;
        }
        return ($date_a < $date_b) ? 1 : -1;
    });

    return $competitions;
}

$competitions = get_competitions();

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discover Competitions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="icon" type="image/x-icon" href="images/logo.png">


    <style>
        .competition-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .competition-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        /* --- START: STYLES FOR PAPERET FEATURED CARD --- */
        .paperet-featured-card {
            border: 3px solid #f6c23e !important;
            /* Gold Border */
            box-shadow: 0 0 20px rgba(246, 194, 62, 0.5) !important;
            /* Gold Glow */
            background-color: #fffaf0;
            /* Creamy background */
        }

        .paperet-featured-card .card-title {
            color: #4e73df;
            /* Primary Blue for Title */
            font-weight: 900;
            /* Extra bold */
        }

        .paperet-featured-card .status-badge.bg-primary {
            background-color: #f6c23e !important;
            /* Gold badge color */
            color: #333 !important;
            font-weight: 700;
        }

        .paperet-featured-card .btn-primary {
            background-color: #d63384 !important;
            /* Hot pink button */
            border-color: #d63384 !important;
        }

        /* --- END: STYLES FOR PAPERET FEATURED CARD --- */

        .card-title {
            font-weight: bold;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-primary,
        .btn-info {
            background-color: #4242f0;
        }
    </style>
</head>

<body class="bg-light">

    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-3">Discover Competitions</h1>
                <p class="lead">Browse competitions hosted by organizations and creators. Join or create your own.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="host_competition.php" class="btn btn-primary btn-lg">Create Competition</a>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12 d-flex flex-wrap gap-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">All Statuses</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-filter="all">All Statuses</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="upcoming">Upcoming</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="active">Active</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="completed">Completed</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item fw-bold text-primary" href="#" data-filter="my_competitions">My Competitions (Organizer)</a></li>
                        <li><a class="dropdown-item text-danger" href="#" data-filter="expired">Expired/Finished</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0"><?php echo count($competitions); ?> results</p>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Sort by</span>
                <a href="#" class="text-decoration-none">Recent</a>
                <span class="text-muted">|</span>
                <a href="#" class="text-decoration-none">Popular</a>
                <button class="btn btn-outline-secondary btn-sm ms-3">Apply Filters</button>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($competitions as $competition) :
                // Check if this is the main Paperet account competition by checking 'organizer_user_id' (which is mapped to DB 'user_id')
                $is_paperet = (isset($competition['organizer_user_id']) && $competition['organizer_user_id'] == '1');
                $card_class = $is_paperet ? 'paperet-featured-card' : '';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card competition-card h-100 <?php echo $card_class; ?>">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary rounded-pill mb-2 status-badge"><?php echo htmlspecialchars($competition['status']); ?></span>
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($competition['title']); ?>
                                <?php if ($is_paperet): ?>
                                    <i class="fas fa-crown text-warning ms-1" title="Official Paperet Competition"></i>
                                <?php endif; ?>
                            </h5>
                            <p class="card-text text-muted mb-1"><small>
                                    <i class="bi bi-building me-1"></i><span class="organizer-name"><?php echo htmlspecialchars($competition['organizer']); ?></span>
                                    <i class="bi bi-calendar-event me-1 ms-3"></i><?php echo htmlspecialchars($competition['date']); ?>
                                    <i class="bi bi-gift me-1 ms-3"></i><?php echo htmlspecialchars($competition['prize']); ?>
                                </small></p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge text-bg-light border"><?php echo htmlspecialchars($competition['format']); ?></span>
                                    <span class="badge text-bg-light border"><?php echo htmlspecialchars($competition['submission']); ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#competitionModal" data-bs-id="<?php echo $competition['id']; ?>">View</button>
                                    <button class="btn btn-primary btn-sm participate-btn-main" data-competition-id="<?php echo $competition['id']; ?>">Participate</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>



    <div class="modal fade" id="competitionModal" tabindex="-1" aria-labelledby="competitionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="competitionModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-content-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button class="btn btn-outline-secondary" id="modal-share-btn">Share</button>
                        <a href="#" class="btn btn-secondary" id="modal-download-btn" target="_blank">Download Rubric</a>
                    </div>
                    <div>
                        <button class="btn btn-primary" id="modal-participate-btn">Participate</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Define PHP variable in JavaScript for use in 'My Competitions' filter
        const LOGGED_IN_ORGANIZER_NAME = '<?php echo $loggedInOrganizerName ? addslashes($loggedInOrganizerName) : ''; ?>';

        const competitionModal = document.getElementById('competitionModal');
        const participateBtn = document.getElementById('modal-participate-btn');
        const shareBtn = document.getElementById('modal-share-btn');
        const downloadBtn = document.getElementById('modal-download-btn');
        let currentCompetitionId = null;

        // Define the base URL for the profile page
        const PROFILE_BASE_URL = 'https://localhost/paper-new/people/profile.php?id=';

        // New function to show custom modal messages
        function showMessageModal(message, isSuccess = true) {
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            const modalTitle = document.getElementById('messageModalLabel');
            const modalBody = document.getElementById('messageModalBody');

            modalTitle.textContent = isSuccess ? 'Success' : 'Error';
            modalTitle.className = 'modal-title ' + (isSuccess ? 'text-success' : 'text-danger');
            modalBody.innerHTML = `<p>${message}</p>`;

            modal.show();
        }

        competitionModal.addEventListener('show.bs.modal', async event => {
            const button = event.relatedTarget;
            currentCompetitionId = button.getAttribute('data-bs-id');
            const modalTitle = competitionModal.querySelector('.modal-title');
            const modalBody = competitionModal.querySelector('.modal-body #modal-content-container');

            modalBody.innerHTML = `<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
            modalTitle.textContent = 'Loading...';
            participateBtn.disabled = false;
            downloadBtn.style.display = 'none';

            try {
                const response = await fetch(`fetch_competition_details.php?id=${currentCompetitionId}`);
                const data = await response.json();

                if (data.success) {
                    modalTitle.textContent = data.competition.title;

                    const deadlineDate = new Date(data.competition.endDate);
                    const now = new Date();
                    const timeLeftMs = deadlineDate - now;
                    const daysLeft = Math.floor(timeLeftMs / (1000 * 60 * 60 * 24));
                    const deadlineText = daysLeft > 0 ? `${daysLeft} days left` : 'Expired';

                    // Construct the profile URL using the user ID
                    const profileLink = PROFILE_BASE_URL + data.competition.organizerUserId;

                    let contentHtml = `
                        <p class="text-muted"><span class="fw-bold">Description:</span> ${data.competition.description}</p>
                        <div class="row">
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Organizer:</span> <a href="${profileLink}" target="_blank">${data.competition.organizer}</a></p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Date:</span> ${data.competition.startDate}</p></div>
                            
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Status:</span> ${data.competition.status}</p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Deadline:</span> ${deadlineText}</p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Prizes:</span> ${data.competition.prize}</p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Participants:</span> ${data.competition.participants}</p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Views:</span> ${data.competition.views}</p></div>
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Format:</span> ${data.competition.format}</p></div>
                            ${data.competition.room_link ? `<div class="col-md-12 mt-2"><p class="text-muted mb-0"><span class="fw-bold">Live link:</span> <a href="${data.competition.room_link}" target="_blank">Link will be shared after acceptance</a></p></div>` : ''}
                        </div>
                        <div class="mt-3">
                            <span class="fw-bold">Tags:</span>
                            ${data.competition.tags.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('')}
                        </div>
                    `;

                    if (data.competition.rubric && Object.keys(data.competition.rubric).length > 0) {
                        contentHtml += `
                            <hr class="my-4">
                            <h5>Evaluation Rubric</h5>
                            <table class="table table-striped table-sm">
                                <thead><tr><th>Criteria</th><th>Weight</th></tr></thead>
                                <tbody>
                                    ${Object.keys(data.competition.rubric).map(criteria => `
                                        <tr><td>${criteria}</td><td>${data.competition.rubric[criteria]}%</td></tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    }
                    if (data.competition.rubric_pdf) {
                        downloadBtn.style.display = 'inline-block';
                        downloadBtn.href = data.competition.rubric_pdf;
                    } else {
                        downloadBtn.style.display = 'none';
                    }

                    modalBody.innerHTML = contentHtml;
                } else {
                    modalTitle.textContent = 'Error';
                    modalBody.innerHTML = `<p class="text-danger">${data.message}</p>`;
                }
            } catch (error) {
                modalTitle.textContent = 'Error';
                modalBody.innerHTML = `<p class="text-danger">Failed to fetch data. Please try again.</p>`;
                console.error('Fetch error:', error);
            }
        });

        // Handle Share button click for modal
        shareBtn.addEventListener('click', () => {
            const url = `${window.location.protocol}//${window.location.host}${window.location.pathname}?id=${currentCompetitionId}`;
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this competition!',
                    url: url
                }).catch(console.error);
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    showMessageModal('Competition URL copied to clipboard!', true);
                }).catch(err => {
                    showMessageModal('Could not copy text to clipboard.', false);
                    console.error('Could not copy text: ', err);
                });
            }
        });

        // Handle Participate button click for modal
        participateBtn.addEventListener('click', async () => {
            const competitionId = currentCompetitionId;
            try {
                const registerResponse = await fetch(`register_participant.php?competition_id=${competitionId}`);
                const registerData = await registerResponse.json();

                if (registerData.success) {
                    showMessageModal(registerData.message, true);
                    // No need to reload, just show a success message
                    // window.location.reload(); 
                } else {
                    if (registerData.message.includes("must be logged in")) {
                        showMessageModal(registerData.message, false);
                        const redirectUrl = encodeURIComponent(`${window.location.pathname}?id=${competitionId}`);
                        window.location.href = `login.php?redirect_to=${redirectUrl}`;
                    } else {
                        showMessageModal(registerData.message, false);
                    }
                }
            } catch (error) {
                showMessageModal('An error occurred. Please try again.', false);
                console.error('Participation error:', error);
            }
        });

        // Handle Participate button click on the main page
        document.addEventListener('click', async e => {
            if (e.target.classList.contains('participate-btn-main')) {
                const competitionId = e.target.getAttribute('data-competition-id');
                try {
                    const registerResponse = await fetch(`register_participant.php?competition_id=${competitionId}`);
                    const registerData = await registerResponse.json();

                    if (registerData.success) {
                        showMessageModal(registerData.message, true);
                        // No need to reload, just show a success message
                        // window.location.reload(); 
                    } else {
                        if (registerData.message.includes("must be logged in")) {
                            showMessageModal(registerData.message, false);
                            const redirectUrl = encodeURIComponent(`${window.location.pathname}?id=${competitionId}`);
                            window.location.href = `login.php?redirect_to=${redirectUrl}`;
                        } else {
                            showMessageModal(registerData.message, false);
                        }
                    }
                } catch (error) {
                    showMessageModal('An error occurred. Please try again.', false);
                    console.error('Participation error:', error);
                }
            }
        });

        // ----------------------------------------------------
        // New competition card filtering logic (based on client request)
        // ----------------------------------------------------
        document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                const filterType = this.getAttribute('data-filter');
                const dropdownButton = this.closest('.dropdown').querySelector('.dropdown-toggle');

                // Update dropdown button text
                dropdownButton.textContent = this.textContent;

                const competitionCards = document.querySelectorAll('.competition-card');
                let visibleCount = 0;

                competitionCards.forEach(card => {
                    // Status is extracted from the card's badge
                    const statusBadge = card.querySelector('.status-badge').textContent.trim();
                    const cardElement = card.closest('.col-md-6.col-lg-4');

                    // Organizer name is extracted from the <span> tag with class .organizer-name
                    const organizerName = card.querySelector('.organizer-name').textContent.trim();

                    // Check if this competition is hosted by the logged-in user
                    const IS_MY_COMPETITION = (LOGGED_IN_ORGANIZER_NAME && organizerName === LOGGED_IN_ORGANIZER_NAME);

                    let shouldShow = false;

                    if (filterType === 'all') {
                        shouldShow = true;
                    } else if (filterType === 'upcoming' && statusBadge === 'Upcoming') {
                        shouldShow = true;
                    } else if (filterType === 'active' && statusBadge === 'Active') {
                        shouldShow = true;
                    } else if (filterType === 'completed' && statusBadge === 'Completed') {
                        shouldShow = true;
                    } else if (filterType === 'expired' && statusBadge === 'Completed') {
                        // "Expired/Finished" is currently 'Completed' in your PHP logic
                        shouldShow = true;
                    } else if (filterType === 'my_competitions' && IS_MY_COMPETITION) {
                        shouldShow = true;
                    }

                    if (shouldShow) {
                        cardElement.style.display = 'block';
                        visibleCount++;
                    } else {
                        cardElement.style.display = 'none';
                    }
                });

                // Update results count
                document.querySelector('.d-flex.justify-content-between.align-items-center.mb-3 p.text-muted').innerHTML = `${visibleCount} results`;

            });
        });
    </script>


    <?php include 'footer.php'; ?>

</body>

</html>