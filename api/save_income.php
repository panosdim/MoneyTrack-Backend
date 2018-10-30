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

// Check if it is an update or new entry
if (isset($data['id'])) {
    $stmt = $db->prepare(
        'UPDATE `income` SET `amount` = ?, `date` = ?, `comment` = ? WHERE `id` = ?'
    );
    $values = [$data['amount'], $data['date'], $data['comment'], $data['id']];
} else {
    $stmt = $db->prepare(
        'INSERT INTO `income` (`user_id`, `amount`, `date`, `comment`) VALUES (?, ?, ?, ?)'
    );
    $values = [$_SESSION['userId'], $data['amount'], $data['date'], $data['comment']];
}

if ($stmt->execute($values)) {
    echo json_encode([
        "success" => true,
        "message" => "Income was saved successfully.",
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
