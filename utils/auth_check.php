<?php
require_once __DIR__ . '/JwtHelper.php';

function authenticateUser() {
    if (!isset($_COOKIE['auth_token'])) {
        header("Location: ../loginpage.php?error=unauthorized");
        exit();
    }

    $token = $_COOKIE['auth_token'];

    $payload = JwtHelper::verifyToken($token);

    if (!$payload) {
        setcookie("auth_token", "", time() - 3600, "/", "", false, true);
        header("Location: ../loginpage.php?error=unauthorized");
        exit();
    }

    return $payload;
}
?>