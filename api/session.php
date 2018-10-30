<?php
session_set_cookie_params(86400, "/");
session_start();
require_once 'database.php';

$sess = [];
if (isset($_SESSION['userId'])) {
    echo json_encode([
        "success" => true,
        "message" => "Session is alive.",
        "data" => ""
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Session is alive.",
        "data" => ""
    ]);
}