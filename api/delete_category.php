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

// Check if category is connected with expenses
$stmt = $db->prepare(
    'SELECT * FROM `expenses` WHERE `category` = ? AND `user_id` = ?'
);
$values = [$data['id'], $_SESSION['userId']];
if ($stmt->execute($values)) {
    $query = $stmt->fetch();

    if ($query !== false) {
        echo json_encode([
            "success" => false,
            "message" => "Category is connected with expenses and can't be deleted.",
            "data" => ""
        ]);

        exit();
    }
}

// Delete category
if (isset($data['id'])) {
    $stmt = $db->prepare(
        'DELETE FROM `categories` WHERE `id` = ?'
    );

    if ($stmt->execute([$data['id']])) {
        echo json_encode([
            "success" => true,
            "message" => "Category was deleted successfully.",
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
