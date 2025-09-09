<?php

session_start();
include "config.php";

function get_competitions()
{
    global $conn;
    $competitions = [];

    $sql = "SELECT id, competition_title, organizer_name, start_date, end_date, submission_type FROM competitions ORDER BY start_date DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = 'Upcoming';
            $now = new DateTime();
            $start_date = new DateTime($row['start_date']);
            $end_date = new DateTime($row['end_date']);

            if ($now > $start_date && $now < $end_date) {
                $status = 'Active';
            } elseif ($now >= $end_date) {
                $status = 'Completed';
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
            ];
        }
    }

    $conn->close();

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

        .card-title {
            font-weight: bold;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
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
                        <li><a class="dropdown-item" href="#">All Statuses</a></li>
                        <li><a class="dropdown-item" href="#">Upcoming</a></li>
                        <li><a class="dropdown-item" href="#">Active</a></li>
                        <li><a class="dropdown-item" href="#">Completed</a></li>
                        <li><a class="dropdown-item" href="#">Expired</a></li>
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
            <?php foreach ($competitions as $competition) : ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card competition-card h-100">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary rounded-pill mb-2 status-badge"><?php echo htmlspecialchars($competition['status']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($competition['title']); ?></h5>
                            <p class="card-text text-muted mb-1"><small>
                                    <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($competition['organizer']); ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const competitionModal = document.getElementById('competitionModal');
        const participateBtn = document.getElementById('modal-participate-btn');
        const shareBtn = document.getElementById('modal-share-btn');
        const downloadBtn = document.getElementById('modal-download-btn');
        let currentCompetitionId = null;

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

                    let contentHtml = `
                        <p class="text-muted"><span class="fw-bold">Description:</span> ${data.competition.description}</p>
                        <div class="row">
                            <div class="col-md-6"><p class="text-muted mb-0"><span class="fw-bold">Organizer:</span> ${data.competition.organizer}</p></div>
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
    </script>


    <?php include 'footer.php'; ?>

</body>

</html>