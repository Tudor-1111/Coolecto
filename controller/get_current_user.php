<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../autoload.php';

if (!isset($_COOKIE['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'Nu ești logat.']);
    exit();
}

$token = $_COOKIE['auth_token'];
$currentUser = JwtHelper::verifyToken($token);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Token invalid sau expirat.']);
    exit();
}

echo json_encode([
    'success' => true, 
    'user' => [
        'username' => $currentUser['username'] ?? '',
        'email' => $currentUser['email'] ?? '',
        'user_pfp' => $currentUser['user_pfp'] ?? 'default_pfp.png'
    ]
]);
?>