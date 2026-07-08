<?php
require_once __DIR__ . "/../autoload.php";

class UpdateCollection 
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

        if (!$this->currentUser) {
            header("Location: ../loginpage.html");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processData();
        }
    }

    public function processData()
    {
        $collection_id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $nume_colectie = trim($_POST['name'] ?? '');
        $descriere = trim($_POST['description'] ?? '');
        $is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;
        $category_id = (!empty($_POST['category_id'])) ? intval($_POST['category_id']) : null;

        if (!$collection_id || empty($nume_colectie)) {
            header("Location: ../profilepage.html");
            exit();
        }

        $existingCollection = $this->collectionDAO->getById($collection_id);

        if (!$existingCollection || $existingCollection['user_id'] != $this->currentUser['user_id']) {
            die("Eroare de securitate: Nu ai dreptul sa editezi aceasta colectie.");
        }

        $nume_imagine = $existingCollection['collection_image'];

        if (isset($_FILES['poza_colectie']) && $_FILES['poza_colectie']['error'] === UPLOAD_ERR_OK) {
            $fisier_temporar = $_FILES['poza_colectie']['tmp_name'];
            $nume_original = $_FILES['poza_colectie']['name'];
            
            $extensie = strtolower(pathinfo($nume_original, PATHINFO_EXTENSION));
            $nume_imagine = uniqid('col_') . '.' . $extensie;
            $cale_upload = __DIR__ . "/../imagini/imagini_collection/" . $nume_imagine;
            
            if (move_uploaded_file($fisier_temporar, $cale_upload)) {
                
                $imagine_veche = $existingCollection['collection_image'];
                $nr_utilizari = $this->collectionDAO->countImageUsage($imagine_veche);
                
                if ($imagine_veche !== 'default_collection.png' && $nr_utilizari == 1) {
                    $cale_fisier_vechi = __DIR__ . "/../imagini/imagini_collection/" . $imagine_veche;
                    
                    if (file_exists($cale_fisier_vechi)) {
                        unlink($cale_fisier_vechi);
                    }
                }
            } else {
                $nume_imagine = $existingCollection['collection_image']; 
            }
        }

        $data = [
            'id' => $collection_id, 
            'user_id' => $this->currentUser['user_id'], 
            'parent_id' => $existingCollection['parent_id'] ?? null,
            'category_id' => $category_id,
            'name' => $nume_colectie,
            'description' => $descriere,
            'is_public' => $is_public,
            'collection_image' => $nume_imagine
        ];

        $updatedCollection = new Collection($data);

        try {
            $this->collectionDAO->update($updatedCollection);
            
            header("Location: ../view_collection.html?id=" . $collection_id);
            exit();
            
        } catch (PDOException $e) {
            die("Eroare la baza de date: " . $e->getMessage());
        }
    }
}

$dao = new CollectionDAO($pdo);
$updateCollectionProcessor = new UpdateCollection($dao);
$updateCollectionProcessor->handleRequest();

?>