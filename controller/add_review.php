<?php
require_once __DIR__ . "/../autoload.php";

class AddReview {
    private $reviewDAO;
    private $currentUser;

    public function __construct($reviewDAO) {
        $this->reviewDAO = $reviewDAO;
    }

    public function handleRequest() {
        $this->currentUser = authenticateUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processData();
        } else {
            die("Acces interzis!");
        }
    }

    public function processData() {
        $collection_id = $_POST['collection_id'] ?? null;
        $user_id = $this->currentUser['user_id'];
        $nota = $_POST['nota'] ?? null;
        $descriere = trim($_POST['descriere'] ?? '');

        if (!$collection_id || !$nota || empty($descriere)) {
            die("Eroare: Toate campurile sunt obligatorii (inclusiv nota)!");
        }

        try {
            if ($this->reviewDAO->isCollectionOwner($collection_id, $user_id)) {
                die("Eroare de securitate: Nu iti poti lasa recenzii la propria colectie!");
            }

            $data = [
                'collection_id' => $collection_id,
                'user_id'       => $user_id,
                'nota'          => $nota,
                'descriere'     => $descriere
            ];
            $newReview = new Review($data);

            $this->reviewDAO->add($newReview);

            header("Location: ../view_collection.html?id=" . $collection_id . "&mode=view&success=review_added");
            exit();

        } catch (PDOException $e) {
            die("Eroare la baza de date: " . $e->getMessage());
        }
    }
}

$dao = new ReviewDAO($pdo);
$addReviewProcessor = new AddReview($dao);
$addReviewProcessor->handleRequest();
?>