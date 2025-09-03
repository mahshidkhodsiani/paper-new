<?php

session_start();
include "config.php";
header('Content-Type: application/json');

$competition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($competition_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid competition ID.']);
    exit;
}

try {
    // Fetch main competition details
    $sql_main = "SELECT * FROM competitions WHERE id = ?";
    $stmt_main = $conn->prepare($sql_main);
    $stmt_main->bind_param("i", $competition_id);
    $stmt_main->execute();
    $result_main = $stmt_main->get_result();
    $competition = $result_main->fetch_assoc();
    $stmt_main->close();

    if (!$competition) {
        echo json_encode(['success' => false, 'message' => 'Competition not found.']);
        exit;
    }

    // Determine status
    $status = 'Upcoming';
    $now = new DateTime();
    $start_date = new DateTime($competition['start_date']);
    $end_date = new DateTime($competition['end_date']);
    if ($now > $start_date && $now < $end_date) {
        $status = 'Active';
    } elseif ($now >= $end_date) {
        $status = 'Completed';
    }

    // Fetch prizes
    $prizes = [];
    $sql_awards = "SELECT award_name, award_value FROM competition_awards WHERE competition_id = ?";
    $stmt_awards = $conn->prepare($sql_awards);
    $stmt_awards->bind_param("i", $competition_id);
    $stmt_awards->execute();
    $result_awards = $stmt_awards->get_result();
    while ($row = $result_awards->fetch_assoc()) {
        $prizes[] = htmlspecialchars($row['award_name']) . ' - ' . htmlspecialchars($row['award_value']);
    }
    $stmt_awards->close();
    $prize_string = !empty($prizes) ? implode(' + ', $prizes) : 'Not specified';

    // Fetch rubric PDF file path
    $rubric_pdf_path = null;
    $sql_rubric = "SELECT file_path FROM competition_uploads WHERE competition_id = ? AND type = 'competition_rubric'";
    $stmt_rubric = $conn->prepare($sql_rubric);
    $stmt_rubric->bind_param("i", $competition_id);
    $stmt_rubric->execute();
    $result_rubric = $stmt_rubric->get_result();
    if ($row_rubric = $result_rubric->fetch_assoc()) {
        $rubric_pdf_path = $row_rubric['file_path'];
    }
    $stmt_rubric->close();

    // Fetch and parse rubric from JSON string
    $rubric = [];
    if (!empty($competition['scoring_rubric'])) {
        $rubric = json_decode($competition['scoring_rubric'], true);
    }

    // Simulated data
    $tags = ['Pitch', '10 min live'];
    $participants_count = rand(50, 150);
    $views_count = rand(500, 2000);

    $competition_data = [
        'success' => true,
        'competition' => [
            'title' => $competition['competition_title'],
            'description' => $competition['competition_description'],
            'organizer' => $competition['organizer_name'],
            'startDate' => $competition['start_date'],
            'endDate' => $competition['end_date'],
            'status' => $status,
            'prize' => $prize_string,
            'participants' => $participants_count,
            'views' => $views_count,
            'format' => $competition['session_track'],
            'room_link' => $competition['room_link'],
            'tags' => $tags,
            'rubric' => $rubric,
            'rubric_pdf' => $rubric_pdf_path
        ]
    ];

    echo json_encode($competition_data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
