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

$id_colectie = isset($_GET['id']) ? intval($_GET['id']) : null; 

if (!$id_colectie) {
    echo json_encode(['success' => false, 'message' => 'ID colectie lipseste.']);
    exit();
}

$collectionDAO = new CollectionDAO($pdo);
$colectie = $collectionDAO->getById($id_colectie);

if (!$colectie || $colectie['user_id'] != $currentUser['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Eroare de securitate sau colectia nu exista.']);
    exit();
}

echo json_encode(['success' => true, 'collection' => $colectie]);
?>