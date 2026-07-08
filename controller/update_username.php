<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../autoload.php';

if (!isset($_COOKIE['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'Eroare: Nu esti autentificat.']);
    exit();
}

$token = $_COOKIE['auth_token'];
$currentUser = JwtHelper::verifyToken($token);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Token invalid sau expirat.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeNou = trim($_POST['nume_nou'] ?? '');

    if (empty($numeNou)) {
        echo json_encode(['success' => false, 'message' => 'Numele nu poate fi gol.']);
        exit();
    }

    $userDAO = new UserDAO($pdo);

    $utilizatorExistent = $userDAO->getUserByLogin($numeNou);
    if ($utilizatorExistent && $utilizatorExistent['id'] != $currentUser['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Acest username este deja folosit!']);
        exit();
    }

    try {
        $userDAO->updateUsername($currentUser['user_id'], $numeNou);

        $newPayload = $currentUser; 
        $newPayload['username'] = $numeNou; 
        $newPayload['exp'] = time() + (86400 * 30);

        $newToken = JwtHelper::generateToken($newPayload);
        setcookie("auth_token", $newToken, time() + (86400 * 30), "/", "", false, true);

        echo json_encode(['success' => true, 'message' => 'Username actualizat cu succes!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare la baza de date.']);
    }
    
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Metoda nepermisa.']);
    exit();
}
?>