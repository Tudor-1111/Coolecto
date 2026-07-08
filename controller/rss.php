<?php
require_once __DIR__ . "/../autoload.php";


header("Content-Type: application/rss+xml; charset=UTF-8");

$collectionDAO = new CollectionDAO($pdo);
$topCollections = $collectionDAO->getTopPopularCollections();


echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
?>
<rss version="2.0">
    <channel>
        <title>Top Colecții - Coolecto</title>
        <link>http://localhost/P_WEB/</link>
        <description>Clasamentul celor mai populare colecții</description>
        <language>ro-ro</language>

        <?php foreach ($topCollections as $col): ?>
            <item>
                <title><?php echo htmlspecialchars($col['name']); ?> (Nota: <?php echo number_format($col['medie_rating'], 2); ?>)</title>
                
                <link>http://localhost/P_WEB/Panainte_Tudor-Emanuel_Covalciuc_Luca-Mihnea_2A4_Web/view_collection.html?id=<?php echo $col['id']; ?></link>
                
                <description>Colecție publicată de <?php echo htmlspecialchars($col['username']); ?>. <?php echo htmlspecialchars($col['description'] ?? ''); ?></description>
                
                <pubDate><?php echo date(DATE_RSS, strtotime($col['created_at'])); ?></pubDate>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>