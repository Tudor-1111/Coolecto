<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../autoload.php';

$id_item = isset($_GET['id']) ? intval($_GET['id']) : null; 

if (!$id_item) {
    echo json_encode(['success' => false, 'message' => 'ID item lipseste.']);
    exit();
}

$itemDAO = new ItemDAO($pdo);
$item = $itemDAO->getItemDetailsById($id_item);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item-ul nu exista.']);
    exit();
}

$isOwner = false;
if (isset($_COOKIE['auth_token'])) {
    $currentUser = JwtHelper::verifyToken($_COOKIE['auth_token']);
    if ($currentUser && $currentUser['user_id'] == $item['user_id']) {
        $isOwner = true;
    }
}

echo json_encode([
    'success' => true,
    'item' => $item,
    'is_owner' => $isOwner
]);
?>