<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../autoload.php';

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

$id_item = isset($_GET['id']) ? intval($_GET['id']) : null; 

if (!$id_item) {
    echo json_encode(['success' => false, 'message' => 'ID item lipseste.']);
    exit();
}

$itemDAO = new ItemDAO($pdo);
$item = $itemDAO->getById($id_item);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Acest item nu exista.']);
    exit();
}

$collectionDAO = new CollectionDAO($pdo);
$parentCollection = $collectionDAO->getById($item['collection_id']);

if (!$parentCollection || $parentCollection['user_id'] != $currentUser['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Eroare de securitate. Nu poti edita acest item.']);
    exit();
}

echo json_encode(['success' => true, 'item' => $item]);
?>