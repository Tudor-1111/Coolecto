<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../autoload.php";
 

if (!isset($_COOKIE['auth_token'])) {
    echo json_encode(['success' => false, 'message' => 'Nu esti logat.']);
    exit();
}

$token = $_COOKIE['auth_token'];
$currentUser = JwtHelper::verifyToken($token);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Token invalid sau expirat.']);
    exit();
}

if (isset($_FILES['poza_profil']) && $_FILES['poza_profil']['error'] === UPLOAD_ERR_OK) {
    
    $fisier = $_FILES['poza_profil'];
    $extensie = strtolower(pathinfo($fisier['name'], PATHINFO_EXTENSION));
    $extensiiPermise = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extensie, $extensiiPermise)) {
        echo json_encode(['success' => false, 'message' => 'Format fisier nepermis.']);
        exit();
    }

    $numeNou = 'user_' . $currentUser['user_id'] . '_' . time() . '.' . $extensie;
    $caleDestinatie = __DIR__ . '/../imagini/imagini_profile/' . $numeNou;

    if (move_uploaded_file($fisier['tmp_name'], $caleDestinatie)) {
        
        $pozaVeche = $currentUser['user_pfp'] ?? 'default_pfp.png'; 

        $userDAO = new UserDAO($pdo);
        $userDAO->updateProfilePicture($currentUser['user_id'], $numeNou);

        if ($pozaVeche !== 'default.png' && $pozaVeche !== 'default_pfp.png') {
            $calePozaVeche = __DIR__ . '/../imagini/imagini_profile/' . $pozaVeche;
            if (file_exists($calePozaVeche)) {
                unlink($calePozaVeche);
            }
        }

        $newPayload = $currentUser;
        $newPayload['user_pfp'] = $numeNou; 
        $newPayload['exp'] = time() + (86400 * 30); 

        $newToken = JwtHelper::generateToken($newPayload);
        setcookie("auth_token", $newToken, time() + (86400 * 30), "/", "", false, true);

        echo json_encode(['success' => true, 'nume_poza' => $numeNou]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Eroare la salvarea fisierului pe server.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Niciun fisier incarcat.']);
}
?>