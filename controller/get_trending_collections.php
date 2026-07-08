<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../autoload.php';

try {
    $collectionDAO = new CollectionDAO($pdo);
    $topCollections = $collectionDAO->getTopPopularCollections();
    
    $trending3 = array_slice($topCollections, 0, 3);
    
    echo json_encode(['success' => true, 'data' => $trending3]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare la preluarea colectiilor populare.']);
}
?>