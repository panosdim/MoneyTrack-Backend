<?php
session_set_cookie_params(86400, "/");
session_start();
// If not logged in exit
if (!isset($_SESSION['userId'])) {
    echo json_encode([
        "success" => false,
        "message" => "You are not logged in.",
        "data" => ""
    ]);
    exit();
}

require_once 'database.php';

// Find the income of the specific user id
$stmt = $db->prepare(
    'SELECT `id`, `amount`, `comment`, `date` FROM income WHERE user_id = ? ORDER BY `date` DESC'
);

if ($stmt->execute([$_SESSION['userId']])) {
    $query = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($query !== false) {
        echo json_encode([
            "success" => true,
            "message" => "Expenses fetched.",
            "data" => $query
        ], JSON_NUMERIC_CHECK);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Problem executing statement in DB. Try again later.",
        "data" => ""
    ]);
}