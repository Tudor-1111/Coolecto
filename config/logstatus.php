<?php 
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../utils/JwtHelper.php';

$response = [
    'esteLogat' => false,
    'username' => ''
];

if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
    $currentUser = JwtHelper::verifyToken($token);

    if ($currentUser) {
        $response['esteLogat'] = true;
        $response['username'] = $currentUser['username'] ?? '';
    }
}

echo json_encode($response);
exit();
?>