<?php
require_once __DIR__ . "/../autoload.php";

class DeleteCollection 
{
    private $collectionDAO;
    private $currentUser;

    public function __construct($collectionDAO) 
    {
        $this->collectionDAO = $collectionDAO;
    }

    public function handleRequest()
    {
        $this->currentUser = authenticateUser();

        if (isset($_GET['id'])) {
            $this->processDeletion(intval($_GET['id']));
        } else {
            header("Location: ../profilepage.html");
            exit();
        }
    }

    private function processDeletion($collection_id)
    {
        $user_id = $this->currentUser['user_id'];

        $collection = $this->collectionDAO->getById($collection_id);

        if (!$collection || $collection['user_id'] != $user_id) {
            die("Eroare de securitate: Nu ai dreptul sa stergi aceasta colectie.");
        }

        $imagine = $collection['collection_image'];
        $nr_utilizari = $this->collectionDAO->countImageUsage($imagine);
        if (!empty($imagine) && $imagine !== 'default_collection.png' && $nr_utilizari == 1) {
            $cale_fisier = __DIR__ . "/../imagini/imagini_collection/" . $imagine;
            if (file_exists($cale_fisier)) {
                unlink($cale_fisier);
            }
        }

        try {
            if (!empty($collection['parent_id'])) {
                $back_url = "../view_collection.html?id=" . $collection['parent_id'];
            } else {
                $back_url = "../profilepage.html#colectii"; 
            }

            $this->collectionDAO->delete($collection_id);
            
            header("Location: $back_url");
            exit();
            
        } catch (PDOException $e) {
            die("Eroare la baza de date in timpul stergerii: " . $e->getMessage());
        }
    }
}

$dao = new CollectionDAO($pdo);
$deleteProcessor = new DeleteCollection($dao);
$deleteProcessor->handleRequest();
?>