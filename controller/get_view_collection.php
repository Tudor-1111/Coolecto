<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../autoload.php';

$id_colectie = isset($_GET['id']) ? intval($_GET['id']) : null; 

if (!$id_colectie) {
    echo json_encode(['success' => false, 'message' => 'ID colectie lipseste.']);
    exit();
}

$collectionDAO = new CollectionDAO($pdo);
$colectie = $collectionDAO->getCollectionDetailsById($id_colectie);

if (!$colectie) {
    echo json_encode(['success' => false, 'message' => 'Colectia nu exista.']);
    exit();
}

$currentUser = null;
$isLoggedIn = false;

if (isset($_COOKIE['auth_token'])) {
    $tokenInfo = JwtHelper::verifyToken($_COOKIE['auth_token']);
    if ($tokenInfo) {
        $currentUser = $tokenInfo;
        $isLoggedIn = true;
    }
}

$isAbsoluteOwner = ($isLoggedIn && $currentUser['user_id'] == $colectie['user_id']);

$reviews = $collectionDAO->getReviewsByCollectionId($id_colectie);

echo json_encode([
    'success' => true,
    'collection' => $colectie,
    'reviews' => $reviews,
    'is_logged_in' => $isLoggedIn,
    'is_owner' => $isAbsoluteOwner
]);
?>