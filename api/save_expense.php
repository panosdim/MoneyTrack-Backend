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
        'UPDATE `expenses` SET `amount` = ?, `date` = ?, `comment` = ? , `category` = ? WHERE `id` = ?'
    );
    $values = [$data['amount'], $data['date'], $data['comment'], $data['category_id'], $data['id']];
} else {
    $stmt = $db->prepare(
        'INSERT INTO `expenses` (`user_id`, `amount`, `date`, `comment`, `category`) VALUES (?, ?, ?, ?, ?)'
    );
    $values = [$_SESSION['userId'], $data['amount'], $data['date'], $data['comment'], $data['category_id']];
}

if ($stmt->execute($values)) {
    echo json_encode([
        "success" => true,
        "message" => "Expense was saved successfully.",
        "data" => ["id" => $db->lastInsertId()]
    ]);
    if (!isset($data['id'])) {
        // Increase the count in category
        $stm = $db->prepare(
            'UPDATE `categories` SET `count` = count + 1 WHERE `id` = ? AND `user_id` = ?'
        );
        $val = [$data['category_id'], $_SESSION['userId']];
        $stm->execute($val);
    }
} else {
    // DB interaction was not successful. Inform user with message.
    echo json_encode([
        "success" => false,
        "message" => "Problem executing statement in DB. Try again later.",
        "data" => ""
    ]);
}
