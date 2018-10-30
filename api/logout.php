<?php

// Initialize the session.
session_start();
require_once 'database.php';

// Define variables and set to empty values
$selector = "";

$data = json_decode(file_get_contents('php://input'), true);
$selector = $data["selector"];

// Unset all of the session variables.
$_SESSION = [];

// Delete authentication token from DB
$stmt = $db->prepare(
	'DELETE FROM `auth_tokens` WHERE `selector` = ?'
);

if ($stmt->execute([$selector])) {
	echo json_encode([
		"success" => true,
		"message" => "Log out was successful.",
		"data" => "",
	]);
}

// Finally, destroy the session.
session_destroy();