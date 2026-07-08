<?php
require_once __DIR__ . "/../autoload.php";

class DeleteItem 
{
    private $itemDAO;
    private $collectionDAO;
    private $currentUser;

    public function __construct($itemDAO, $collectionDAO) 
    {
        $this->itemDAO = $itemDAO;
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

    private function processDeletion($item_id)
    {
        $user_id = $this->currentUser['user_id'];

        $item = $this->itemDAO->getById($item_id);

        if (!$item) {
            die("Acest item nu exista.");
        }

        $parentCollection = $this->collectionDAO->getById($item['collection_id']);
        
        if (!$parentCollection || $parentCollection['user_id'] != $user_id) {
            die("Eroare de securitate: Nu ai dreptul sa stergi acest item.");
        }

        $id_colectie_parinte = $item['collection_id'];

        $imagine = $item['item_image'];
        $nr_utilizari = $this->itemDAO->getImageUsage($imagine);
        if (!empty($imagine) && $imagine !== 'default_item.png' && $nr_utilizari == 1) {
            $cale_fisier = __DIR__ . "/../imagini/imagini_item/" . $imagine;
            if (file_exists($cale_fisier)) {
                unlink($cale_fisier);
            }
        }

        try {
            $this->itemDAO->delete($item_id);
            
            header("Location: ../view_collection.html?id=" . $id_colectie_parinte);
            exit();
            
        } catch (PDOException $e) {
            die("Eroare la baza de date in timpul stergerii: " . $e->getMessage());
        }
    }
}

$itemDAO = new ItemDAO($pdo);
$collectionDAO = new CollectionDAO($pdo);
$deleteItemProcessor = new DeleteItem($itemDAO, $collectionDAO);

$deleteItemProcessor->handleRequest();
?>