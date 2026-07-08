<?php
require_once __DIR__ . "/../autoload.php";

$currentUser = authenticateUser();

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    die("Cerere invalida.");
}

$collectionDAO = new CollectionDAO($pdo);
$itemDAO = new ItemDAO($pdo);

function buildCollectionTree($collection_id, $collectionDAO, $itemDAO) {
    
    $coll = $collectionDAO->getById($collection_id);
    
    if (!$coll) return null;

    $node = [
        'name' => $coll['name'],
        'description' => $coll['description'],
        'category_id' => $coll['category_id'],
        'collection_image' => $coll['collection_image'],
        'items' => [],
        'subcollections' => []
    ];

    $items = $itemDAO->getItemsByCollectionId($collection_id);

    if ($items) {
        foreach ($items as $item) {
            $node['items'][] = [
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'currency' => $item['currency'],
                'item_image' => $item['item_image'],
                'country' => $item['country'] ?? null,
                'usage_start_date' => $item['usage_start_date'] ?? null,
                'usage_end_date' => $item['usage_end_date'] ?? null,
                'history' => $item['history'] ?? null,
                'has_label' => $item['has_label'] ?? 0
            ];
        }
    }

    $subs = $collectionDAO->getSubcollectionsByCollectionId($collection_id);

    if ($subs) {
        foreach ($subs as $sub) {
            $subTree = buildCollectionTree($sub['id'], $collectionDAO, $itemDAO);
            if ($subTree) {
                $node['subcollections'][] = $subTree;
            }
        }
    }

    return $node;
}

$tree = buildCollectionTree($id, $collectionDAO, $itemDAO);

if ($tree) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_colectie_' . $id . '.json"');
    
    echo json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
} else {
    die("Colectia nu exista.");
}
?>