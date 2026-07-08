<?php
header('Content-Type: application/json; charset=utf-8');

           
require_once __DIR__ . '/../autoload.php';
 

if (!isset($_COOKIE['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'Neautorizat: Trebuie sa fii logat.']);
    exit();
}

$token = $_COOKIE['auth_token'];
$currentUser = JwtHelper::verifyToken($token);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Neautorizat: Token invalid.']);
    exit();
}

$collectionDAO = new CollectionDAO($pdo);

$filters = [
    'name'     => $_GET['name'] ?? '',
    'category' => $_GET['category'] ?? '',
    'user'     => $_GET['user'] ?? '',
    'sort'     => $_GET['sort'] ?? ''
];

try {
    $publicCollections = $collectionDAO->getPublicCollections($filters);
    
    $resultData = [];
    if (!empty($publicCollections)) {
        foreach($publicCollections as $c) {
            $resultData[] = [
                'id' => is_object($c) ? $c->id : $c['id'],
                'collection_image' => is_object($c) ? $c->collection_image : $c['collection_image'],
                'name' => is_object($c) ? $c->name : $c['name'],
                'description' => is_object($c) ? $c->description : $c['description'],
                'category_name' => is_object($c) ? $c->category_name : $c['category_name']
            ];
        }
    }

    echo json_encode(['success' => true, 'collections' => $resultData]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare la preluarea colectiilor: ' . $e->getMessage()]);
}
?>