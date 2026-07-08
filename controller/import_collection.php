<?php
require_once __DIR__ . "/../autoload.php";

$currentUser = authenticateUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fisier_import'])) {
    
    $user_id = $currentUser['user_id'];
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null; 

    $file_tmp = $_FILES['fisier_import']['tmp_name'];
    $json_content = file_get_contents($file_tmp);
    $data = json_decode($json_content, true);

    if ($data === null) {
        die("Eroare la citirea sau procesarea fisierului JSON. Format invalid.");
    }

    $collectionDAO = new CollectionDAO($pdo);
    $itemDAO = new ItemDAO($pdo);

    $rootCollectionData = [
        'user_id' => $user_id,
        'parent_id' => $parent_id, 
        'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
        'name' => !empty($data['name']) ? $data['name'] : 'Colectie Importata',
        'description' => $data['description'] ?? '',
        'is_public' => 0,
        'collection_image' => !empty($data['collection_image']) ? $data['collection_image'] : 'default_collection.png'
    ];
    
    $rootCol = new Collection($rootCollectionData);
    $collectionDAO->add($rootCol);
    $new_root_id = $pdo->lastInsertId(); 

    function importItemsAndSubcollectionsFromJSON($node, $created_collection_id, $user_id, $collectionDAO, $itemDAO, $pdo) {
        
        if (isset($node['items']) && is_array($node['items'])) {
            foreach ($node['items'] as $itemData) {
                $dateItem = [
                    'collection_id' => $created_collection_id,
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
                    'parent_id' => $created_collection_id,
                    'category_id' => !empty($subData['category_id']) ? (int)$subData['category_id'] : null,
                    'name' => $subData['name'],
                    'description' => $subData['description'],
                    'is_public' => 0,
                    'collection_image' => !empty($subData['collection_image']) ? $subData['collection_image'] : 'default_collection.png'
                ];
                
                $sub = new Collection($dateSubcolectie);
                $collectionDAO->add($sub);
                
                $new_sub_id = $pdo->lastInsertId();

                importItemsAndSubcollectionsFromJSON($subData, $new_sub_id, $user_id, $collectionDAO, $itemDAO, $pdo);
            }
        }
    }

    importItemsAndSubcollectionsFromJSON($data, $new_root_id, $user_id, $collectionDAO, $itemDAO, $pdo);

    $redirect_id = $parent_id ? $parent_id : $new_root_id;
    header("Location: ../view_collection.html?id=" . $redirect_id);
    exit();
}
?>