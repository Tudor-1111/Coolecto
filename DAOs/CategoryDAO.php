<?php

class CategoryDAO {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAllCategories() {
        try {
            $sql = "SELECT id, name FROM categories ORDER BY id ASC";
            $stmt = $this->db->query($sql);
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categoriesList = [];

           
            foreach ($rows as $row) {
                $categoriesList[] = new Category($row);
            }

            return $categoriesList;

        } catch (PDOException $e) {
            error_log("Eroare la extragerea categoriilor: " . $e->getMessage());
            return [];
        }
    }
}
?>