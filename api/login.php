<?php

session_start();
require_once 'database.php';

// Define variables and set to empty values
$username = $password = "";

$data = json_decode(file_get_contents('php://input'), true);
$username = $data["username"];
$password = $data["password"];

function generateToken($length = 20) {
	return bin2hex(random_bytes($length));
}

// Get hashed password for user
$stmt = $db->prepare(
	'SELECT * FROM users WHERE username = ?'
);
if ($stmt->execute([$username])) {
	$query = $stmt->fetch();

	if ($query !== false) {
		// Hashing the DB password with the salt returns the same hash
		if (password_verify($password, $query['password'])) {
			$series = generateToken();
			$token = generateToken();
			$selector = generateToken(5);
			$hashedToken = hash('sha256', $token);

			$date = date_create('now');
			date_add($date, date_interval_create_from_date_string('30 days'));

			$stmt = $db->prepare(
				'INSERT INTO `auth_tokens` (`selector`, `series`, `hashedToken`, `username`, `user_id`, `expire`) VALUES (?, ?, ?, ?, ?, ?)'
			);

			if ($stmt->execute([$selector, $series, $hashedToken, $username, $query['id'], date_format($date, 'Y-m-d')])) {
                        $data = [
                            "series" => $series,
                            "token" => $token,
                            "selector" => $selector
                        ]
			}
			// Authentication successful - Set session
			echo json_encode([
				"success" => true,
				"message" => "Login was successful.",
				"data" => $data,
			]);
			$_SESSION['userId'] = $query['id'];
			$_SESSION['username'] = $username;
		} else {
			// Authentication was not successful. Inform user with message.
			echo json_encode([
				"success" => false,
				"message" => "Login was not successful.",
				"data" => "",
			]);
		}
	} else {
		// Authentication was not successful. Inform user with message.
		echo json_encode([
			"success" => false,
			"message" => "Login was not successful.",
			"data" => "",
		]);
	}
} else {
	// DB interaction was not successful. Inform user with message.
	echo json_encode([
		"success" => false,
		"message" => "Problem executing statement in DB. Try again later.",
		"data" => "",
	]);
}
