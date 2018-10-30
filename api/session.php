<?php

session_start();
require_once 'database.php';

// Define variables and set to empty values
$selector = $series = $token = "";

$data = json_decode(file_get_contents('php://input'), true);
$selector = $data["selector"];
$series = $data["series"];
$token = $data["token"];

function generateToken($length = 20) {
	return bin2hex(random_bytes($length));
}

// Get hashed token for user
$stmt = $db->prepare(
	'SELECT `id`, `username`, `user_id`, `expire`, `series`, `hashedToken`, `expire` FROM `auth_tokens` WHERE selector = ?'
);

if ($stmt->execute([$selector])) {
	$query = $stmt->fetch();

	if ($query !== false) {
		// Check if authentication token is expired
		if (time() < strtotime($query['expire'])) {
			// Check for valid series
			if (hash_equals($series, $query['series'])) {
				$hashedToken = hash('sha256', $token);
				// Check for valid token
				if (hash_equals($hashedToken, $query['hashedToken'])) {
					$_SESSION['userId'] = $query['user_id'];
					$_SESSION['username'] = $query['username'];

					// Generate a new token and update DB
					$token = generateToken();
					$hashedToken = hash('sha256', $token);

					$stmt = $db->prepare(
						'UPDATE `auth_tokens` SET `hashedToken` = ? WHERE `id` = ?'
					);

					if ($stmt->execute([$hashedToken, $query['id']])) {
						echo json_encode([
							"success" => true,
							"message" => "Session is alive.",
							"data" => $token,
						]);
					}
				} else {
					// Invalid token when series and selector are valid means cookie stolen attack
					// Delete ALL authentication tokens for the user

					// Unset all of the session variables.
					$_SESSION = [];

					// Delete authentication token from DB
					$stmt = $db->prepare(
						'DELETE FROM `auth_tokens` WHERE `username` = ?'
					);

					if ($stmt->execute([$query['username']])) {
						echo json_encode([
							"success" => false,
							"message" => "Session is not alive.",
							"data" => "",
						]);
					}

					// Finally, destroy the session.
					session_destroy();
				}
			} else {
				echo json_encode([
					"success" => false,
					"message" => "Session is not alive.",
					"data" => "",
				]);
			}
		} else {
			echo json_encode([
				"success" => false,
				"message" => "Session is not alive.",
				"data" => "",
			]);
		}
	} else {
		echo json_encode([
			"success" => false,
			"message" => "Session is not alive.",
			"data" => "",
		]);
	}
} else {
	echo json_encode([
		"success" => false,
		"message" => "Session is not alive.",
		"data" => "",
	]);
}