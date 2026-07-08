<?php

require_once __DIR__ . "/../autoload.php"; 

header('Content-Type: application/json');

try {
    $categoryDAO = new CategoryDAO($pdo);
    $categories = $categoryDAO->getAllCategories();

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la incarcarea categoriilor.'
    ]);
}
?>