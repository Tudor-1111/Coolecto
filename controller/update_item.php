<?php
session_start();

require_once __DIR__ . "/../autoload.php";

class UpdateItem
{
    private $itemDAO;

    public function __construct($itemDAO) 
    {
        $this->itemDAO = $itemDAO;
    }

    public function handleRequest()
    {
        $currentUser = authenticateUser(); 

        if (!$currentUser) {
            header("Location: ../loginpage.html");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processData();
        }
    }

    public function processData()
    {
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : null;
        if (!$item_id) {
            die("ID item lipsa!");
        }

        $nume_item = trim($_POST['name'] ?? '');
        $descriere = trim($_POST['description'] ?? '');
        
        $price_value = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
        $currency_id = isset($_POST['currency']) ? intval($_POST['currency']) : null;
        
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : null;
        
        $date_of_purchase = !empty($_POST['datepurchase']) ? trim($_POST['datepurchase']) : null;

        $country = !empty($_POST['country']) ? trim($_POST['country']) : null;
        $usage_start_date = !empty($_POST['usage_start_date']) ? trim($_POST['usage_start_date']) : null;
        $usage_end_date = !empty($_POST['usage_end_date']) ? trim($_POST['usage_end_date']) : null;
        $history = trim($_POST['history'] ?? '');
        $has_label = isset($_POST['has_label']) ? (bool)$_POST['has_label'] : false;

        $currency_name = null;
        if ($price_value !== null && $currency_id) {
            switch ($currency_id) {
                case 1: $currency_name = 'RON'; break;
                case 2: $currency_name = 'EUR'; break;
                case 3: $currency_name = 'USD'; break;
                case 4: $currency_name = 'GBP'; break;
            }
        }

        $nume_imagine = !empty($_POST['current_image']) ? $_POST['current_image'] : 'default_item.png';

        if (isset($_FILES['poza_colectie']) && $_FILES['poza_colectie']['error'] === UPLOAD_ERR_OK) {
            $fisier_temporar = $_FILES['poza_colectie']['tmp_name'];
            $nume_original = $_FILES['poza_colectie']['name'];
            
            $extensie = strtolower(pathinfo($nume_original, PATHINFO_EXTENSION));
            $nume_imagine = uniqid('item_') . '.' . $extensie;
            $cale_upload = __DIR__ . "/../imagini/imagini_item/" . $nume_imagine;
            
            move_uploaded_file($fisier_temporar, $cale_upload);
        }

        $data = [
            'id' => $item_id, 
            'collection_id' => $collection_id, 
            'name' => $nume_item,
            'description' => $descriere,
            'price' => $price_value,            
            'currency' => $currency_name,       
            'date_of_purchase' => $date_of_purchase, 
            'item_image' => $nume_imagine,
            'country' => $country,
            'usage_start_date' => $usage_start_date,
            'usage_end_date' => $usage_end_date,
            'history' => $history,
            'has_label' => $has_label
        ];

        $updatedItem = new Item($data);

        try {
            $this->itemDAO->update($updatedItem);
            
            header("Location: ../view_item.html?id=" . $item_id);
            exit();
            
        } catch (PDOException $e) {
            die("EROARE LA BAZA DE DATE: " . $e->getMessage());
        }        
        
    }
}

$dao = new ItemDAO($pdo);
$updateItemProcessor = new UpdateItem($dao);
$updateItemProcessor->handleRequest();
?>