<?php
require_once __DIR__ . "/auth_check.php";

header("Content-Type: application/json; charset=utf-8");

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        respond(false, "Please log in to continue.");
    }
}
?>
