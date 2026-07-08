<?php
require_once __DIR__ . "/../autoload.php";

$currentUser = authenticateUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fisier_merge'])) {
    
    $user_id = $currentUser['user_id'];
    $target_collection_id = !empty($_POST['target_collection_id']) ? intval($_POST['target_collection_id']) : null; 
    
    if (!$target_collection_id) {
        die("Nu s-a specificat colectia tinta pentru merge.");
    }

    $file_tmp = $_FILES['fisier_merge']['tmp_name'];
    $json_content = file_get_contents($file_tmp);
    $data = json_decode($json_content, true);

    if ($data === null) {
        die("Eroare la citirea sau procesarea fisierului JSON. Format invalid.");
    }

    $collectionDAO = new CollectionDAO($pdo);
    $itemDAO = new ItemDAO($pdo);

    function mergeItemsAndSubcollectionsFromJSON($node, $destination_id, $user_id, $collectionDAO, $itemDAO, $pdo) {
        
        if (isset($node['items']) && is_array($node['items'])) {
            foreach ($node['items'] as $itemData) {
                $dateItem = [
                    'collection_id' => $destination_id,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => !empty($itemData['price']) ? (float)$itemData['price'] : null,
                    'currency' => $itemData['currency'] ?? 'RON',
                    'date_of_purchase' => date('Y-m-d'), 
                    'item_image' => !empty($itemData['item_image']) ? $itemData['item_image'] : 'default_item.png',
                    'country' => $itemData['country'] ?? null,
                    'usage_start_date' => $itemData['usage_start_date'] ?? null,
                    'usage_end_date' => $itemData['usage_end_date'] ?? null,
                    'history' => $itemData['history'] ?? null,
                    'has_label' => isset($itemData['has_label']) ? (bool)$itemData['has_label'] : false
                ];
                
                $item = new Item($dateItem);
                $itemDAO->add($item);
            }
        }

        if (isset($node['subcollections']) && is_array($node['subcollections'])) {
            foreach ($node['subcollections'] as $subData) {
                $dateSubcolectie = [
                    'user_id' => $user_id,
                    'parent_id' => $destination_id,
                    'category_id' => !empty($subData['category_id']) ? (int)$subData['category_id'] : null,
                    'name' => $subData['name'],
                    'description' => $subData['description'],
                    'is_public' => 0,
                    'collection_image' => !empty($subData['collection_image']) ? $subData['collection_image'] : 'default_collection.png'
                ];
                
                $sub = new Collection($dateSubcolectie);
                $collectionDAO->add($sub);
                
                $new_sub_id = $pdo->lastInsertId();

                mergeItemsAndSubcollectionsFromJSON($subData, $new_sub_id, $user_id, $collectionDAO, $itemDAO, $pdo);
            }
        }
    }

    mergeItemsAndSubcollectionsFromJSON($data, $target_collection_id, $user_id, $collectionDAO, $itemDAO, $pdo);

    header("Location: ../view_collection.html?id=" . $target_collection_id);
    exit();
}
?>