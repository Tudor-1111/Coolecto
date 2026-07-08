<?php
require_once __DIR__ . "/../autoload.php";
header('Content-Type: application/json');

$currentUser = authenticateUser();

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Nu esti logat.']);
    exit();
}

date_default_timezone_set('Europe/Bucharest');

try {
    $collectionDAO = new CollectionDAO($pdo);
    
    
    $globalStats = $collectionDAO->getGlobalStatistics();
    $filteredStats = $collectionDAO->getFilteredStatistics($_GET);
    $filteredList = $collectionDAO->getFilteredCollectionsList($_GET); 

    
    echo json_encode([
        'success' => true,
        'globalStats' => $globalStats,
        'filteredStats' => $filteredStats,
        'filteredList' => $filteredList,
        'currentDate' => date('d.m.Y H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la generarea raportului: ' . $e->getMessage()
    ]);
}
?>