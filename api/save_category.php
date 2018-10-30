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

$data = json_decode(file_get_contents('php://input'), true);

// Check if category with the same name already exists
$stmt = $db->prepare(
    'SELECT * FROM `categories` WHERE `category` = ?'
);
$values = [$data['category']];
if ($stmt->execute($values)) {
    $query = $stmt->fetch();

    if ($query !== false) {
        echo json_encode([
            "success" => false,
            "message" => "Category with same name already exists.",
            "data" => ""
        ]);

        exit();
    }
}

// Check if it is an update or new entry
if (isset($data['id'])) {
    $stmt = $db->prepare(
        'UPDATE `categories` SET `category` = ? WHERE `id` = ?'
    );
    $values = [$data['category'], $data['id']];
} else {
    $stmt = $db->prepare(
        'INSERT INTO `categories` (`user_id`, `category`, `count`) VALUES (?, ?, 0)'
    );
    $values = [$_SESSION['userId'], $data['category']];
}

if ($stmt->execute($values)) {
    echo json_encode([
        "success" => true,
        "message" => "Category was saved successfully.",
        "data" => ["id" => $db->lastInsertId()]
    ]);
} else {
    // DB interaction was not successful. Inform user with message.
    echo json_encode([
        "success" => false,
        "message" => "Problem executing statement in DB. Try again later.",
        "data" => ""
    ]);
}
