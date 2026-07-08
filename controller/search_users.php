<?php
require_once __DIR__ . "/../autoload.php"; 


header('Content-Type: application/json');


$searchQuery = trim($_GET['q'] ?? '');


if (strlen($searchQuery) < 1) {
    echo json_encode([]); 
    exit;
}


$userDAO = new UserDAO($pdo);
$users = $userDAO->searchUsersByUsername($searchQuery);
echo json_encode($users);


?>