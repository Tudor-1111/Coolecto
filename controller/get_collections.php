<?php
header('Content-Type: application/json');


require_once __DIR__ . "/../autoload.php";


if (!isset($_COOKIE['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'Nu esti logat.']);
    exit();
}

$token = $_COOKIE['auth_token'];
$currentUser = JwtHelper::verifyToken($token);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Token invalid sau expirat.']);
    exit();
}

$collectionDAO = new CollectionDAO($pdo);

$colectii = $collectionDAO->getCollectionsByUserId($currentUser['user_id']);

echo json_encode(['success' => true, 'colectii' => $colectii]);
?>