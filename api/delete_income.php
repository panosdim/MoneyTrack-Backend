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
        'DELETE FROM `income` WHERE `id` = ?'
    );

    if ($stmt->execute([$data['id']])) {
        echo json_encode([
            "success" => true,
            "message" => "Income was deleted successfully.",
            "data" => ""
        ]);
    } else {
        // DB interaction was not successful. Inform user with message.
        echo json_encode([
            "success" => false,
            "message" => "Problem executing statement in DB. Try again later.",
            "data" => ""
        ]);
    }
} else {
    // DB interaction was not successful. Inform user with message.
    echo json_encode([
        "success" => false,
        "message" => "Problem executing statement in DB. Try again later.",
        "data" => ""
    ]);
}
