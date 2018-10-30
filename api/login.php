<?php
session_set_cookie_params(86400, "/");
session_start();
require_once 'database.php';

// Define variables and set to empty values
$username = $password = "";

$data = json_decode(file_get_contents('php://input'), true);
$username = $data["username"];
$password = $data["password"];

// Get hashed password for user
$stmt = $db->prepare(
    'SELECT * FROM users WHERE username = ?'
);
if ($stmt->execute([$username])) {
    $query = $stmt->fetch();

    if ($query !== false) {
        // Hashing the DB password with the salt returns the same hash
        if (password_verify($password, $query['password'])) {
            // Authentication successful - Set session
            echo json_encode([
                "success" => true,
                "message" => "Login was successful.",
                "data" => ""
            ]);
            $_SESSION['userId'] = $query['id'];
            $_SESSION['username'] = $username;
        } else {
            // Authentication was not successful. Inform user with message.
            echo json_encode([
                "success" => false,
                "message" => "Login was not successful.",
                "data" => ""
            ]);
        }
    } else {
        // Authentication was not successful. Inform user with message.
        echo json_encode([
            "success" => false,
            "message" => "Login was not successful.",
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


