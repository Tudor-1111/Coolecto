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

$collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : null;

if (!$collection_id) {
    echo json_encode(['success' => false, 'message' => 'Collection ID lipseste.']);
    exit();
}

$itemDAO = new ItemDAO($pdo);

$items = $itemDAO->getItemsByCollectionId($collection_id);

echo json_encode(['success' => true, 'items' => $items]);
?>