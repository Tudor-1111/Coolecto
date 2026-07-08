<?php
require_once __DIR__ . "/../autoload.php";

$currentUser = authenticateUser();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Application_Statistics.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 

$collectionDAO = new CollectionDAO($pdo);

$globalStats = $collectionDAO->getGlobalStatistics();
$filteredStats = $collectionDAO->getFilteredStatistics($_GET);
$filteredList = $collectionDAO->getFilteredCollectionsList($_GET); 

$globalScore = number_format($globalStats['top_rated_score'] ?? 0, 2, '.', '');
$globalExpensiveValue = number_format($globalStats['expensive_value'] ?? 0, 2, '.', '');
$filteredValueRon = number_format($filteredStats['total_filtered_value_ron'] ?? 0, 2, '.', '');

fputcsv($output, ['GLOBAL APPLICATION STATISTICS']);
fputcsv($output, ['Metric', 'Value', 'Informations']);
fputcsv($output, ['Total Users', $globalStats['total_users'], '']);
fputcsv($output, ['Total Collections', $globalStats['total_collections'], '']);
fputcsv($output, ['Total Reviews', $globalStats['total_reviews'], '']);

$formattedRating = '="' . $globalScore . '"';
fputcsv($output, ['Top Rated Collection', $globalStats['top_rated_name'], 'Owned by: ' . $globalStats['top_rated_owner'] . ' (Score: ' . $formattedRating . ' stars)']);
fputcsv($output, ['Most Expensive Collection', $globalStats['expensive_name'], 'Owned by: ' . $globalStats['expensive_owner'] . ' (Value: ' . $globalExpensiveValue . ' RON)']);

fputcsv($output, []); 

fputcsv($output, ['FILTERED STATISTICS SUMMARY']);
fputcsv($output, ['Filtered Metric', 'Value']);
fputcsv($output, ['Total Collections matching filters', $filteredStats['total_filtered_collections'] ?? 0]);
fputcsv($output, ['Total Items inside these collections', $filteredStats['total_filtered_items'] ?? 0]);
fputcsv($output, ['Total Cumulative Value of items', $filteredValueRon . ' RON']);

fputcsv($output, []); 

fputcsv($output, ['DETAILED FILTERED COLLECTIONS LIST']);
fputcsv($output, ['Collection Name', 'Category', 'Owner', 'Items Count', 'Total Value', 'Avg Rating', 'Created At']);

if (empty($filteredList)) {
    fputcsv($output, ['No collections found matching current filters.']);
} else {
    foreach ($filteredList as $row) {
        
        $rowValueRon = number_format($row['total_value_ron'] ?? 0, 2, '.', '');
        $rowRating = number_format($row['medie_rating'] ?? 0, 2, '.', '');
        
        $listRatingEscaped = '="' . $rowRating . '"';
        
        fputcsv($output, [
            $row['collection_name'],
            $row['category_name'] ?? 'Fara categorie',
            $row['owner'],
            $row['items_count'],
            $row['total_value_ron'] . ' RON', 
            $listRatingEscaped,                      
            date('Y-m-d', strtotime($row['created_at']))
        ]);
    }
}

fclose($output);
exit();
?>