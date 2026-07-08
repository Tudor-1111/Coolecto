<?php
require_once __DIR__ . "/../autoload.php";

class AddCollection 
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

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processData();
        }
    }

    public function processData()
    {
        $nume_colectie = trim($_POST['name'] ?? '');
        $descriere = trim($_POST['description'] ?? '');
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $category_id = $_POST['category_id'] ?? null; 
        $category_name = $_POST['custom_category'] ?? '';
        $nume_imagine = 'default_collection.png';

        if (isset($_FILES['poza_colectie']) && $_FILES['poza_colectie']['error'] === UPLOAD_ERR_OK) {
            $fisier_temporar = $_FILES['poza_colectie']['tmp_name'];
            $nume_original = $_FILES['poza_colectie']['name'];
            
            $extensie = strtolower(pathinfo($nume_original, PATHINFO_EXTENSION));
            $nume_imagine = uniqid('col_') . '.' . $extensie;
            $cale_upload = __DIR__ . "/../imagini/imagini_collection/" . $nume_imagine;
            
            move_uploaded_file($fisier_temporar, $cale_upload);
        }

        $data = [
            'user_id' => $this->currentUser['user_id'], 
            'parent_id' => $parent_id,
            'category_id' => $category_id,
            'category_name' => $category_name, 
            'name' => $nume_colectie,
            'description' => $descriere,
            'is_public' => $is_public,
            'collection_image' => $nume_imagine
        ];

        $newCollection = new Collection($data);

        try {
            $this->collectionDAO->add($newCollection);

            if ($parent_id == null)
                header("Location: ../profilepage.html#colectii");
            else
                header("Location: ../view_collection.html?id=". $parent_id);
            exit();
            
        } catch (PDOException $e) {
           
            $redirect_url = "../new_collection.html?error=db_error";
            if ($parent_id !== null) {
                $redirect_url = "../new_collection.html?parent_id=" . $parent_id . "&error=db_error";
            }
            
            header("Location: " . $redirect_url);
            exit();
            
          
        }
    }
}

$dao = new CollectionDAO($pdo);
$addCollectionProcessor = new AddCollection($dao);
$addCollectionProcessor->handleRequest();

?>