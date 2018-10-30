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

// Find the categories of the specific user id
$stmt = $db->prepare(
    'SELECT `id`, `category` 
    FROM categories 
    WHERE `user_id` = ? ORDER BY `count` DESC'
);

if ($stmt->execute([$_SESSION['userId']])) {
    $query = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($query !== false) {
        echo json_encode([
            "success" => true,
            "message" => "Categories fetched.",
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