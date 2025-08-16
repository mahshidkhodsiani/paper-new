<?php
// search_live.php

// اصلاح مسیردهی به فایل config.php
include '../config.php';

$all_results = [];

if (isset($_GET['query'])) {
    $search_query = $_GET['query'];

    // حداقل ۳ کاراکتر برای جستجو
    if (strlen($search_query) >= 3) {
        $sql = "SELECT * FROM `presentations` WHERE `title` LIKE ? OR `keywords` LIKE ? ORDER BY `created_at` DESC";
        $stmt = $conn->prepare($sql);
        $search_param = "%" . $search_query . "%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $all_results[] = $row;
            }
        }
        $stmt->close();
    }
    $conn->close();
}

if (!empty($all_results)) {
    echo '<ul class="list-group">';
    // محدود کردن نتایج به ۵ مورد
    for ($i = 0; $i < min(5, count($all_results)); $i++) {
        $result = $all_results[$i];
        $title = htmlspecialchars($result['title'] ?? 'N/A');
        $filePath = htmlspecialchars($result['pdf_path'] ?? '#');
        $description = htmlspecialchars($result['description'] ?? 'N/A');

        echo '<li class="list-group-item">';
        echo '<h5><a href="' . $filePath . '" target="_blank">' . $title . '</a></h5>';
        echo '<p>' . $description . '</p>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<div class="list-group-item">No results found</div>';
}
