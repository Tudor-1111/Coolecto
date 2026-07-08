<?php

class ItemDAO {
    private $db;

    public function __construct($pdo){
        $this->db = $pdo;
    }

    public function add(Item $item)
    {
        try {
            $sql="INSERT INTO items (collection_id, name, description, item_image, price, currency, date_of_purchase, country, usage_start_date, usage_end_date, history, has_label) 
                  VALUES (:collection_id, :name, :description, :image, :price, :currency, :date_of_purchase, :country, :usage_start_date, :usage_end_date, :history, :has_label)";
                
            $stmt=$this->db->prepare($sql);

            $result = $stmt->execute([
                ':collection_id'    => $item->collection_id,
                ':name'             => $item->name,
                ':description'      => $item->description,
                ':image'            => $item->item_image,
                ':price'            => $item->price,
                ':currency'         => $item->currency,
                ':date_of_purchase' => $item->date_of_purchase,
                ':country'          => $item->country,
                ':usage_start_date' => $item->usage_start_date,
                ':usage_end_date'   => $item->usage_end_date,
                ':history'          => $item->history,
                ':has_label'        => $item->has_label ? 1 : 0 
            ]);

            return $result; 
            
        } catch (PDOException $e) {
            die("Eroare MySQL la insert: " . $e->getMessage()); 
        }
    }

    public function getItemsByCollectionId($id)
    {
        $sql="SELECT * FROM ITEMS WHERE collection_id=:id";
            
        $stmt=$this->db->prepare($sql);

        $stmt->execute([
            ':id'=>$id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($item_id)
    {
        $sql="SELECT * FROM ITEMS WHERE id=:item_id";
        $stmt=$this->db->prepare($sql);
        $stmt->execute([':item_id' => $item_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(Item $item)
    {
        $sql="UPDATE items 
                   SET name = :name, 
                       description = :description, 
                       item_image = :item_image, 
                       price = :price,
                       currency = :currency, 
                       date_of_purchase = :date_of_purchase,
                       country = :country,
                       usage_start_date = :usage_start_date,
                       usage_end_date = :usage_end_date,
                       history = :history,
                       has_label = :has_label
                   WHERE id = :id";
            
        $stmt=$this->db->prepare($sql);

        $stmt->execute([
            ':name'             => $item->name,
            ':description'      => $item->description,
            ':item_image'       => $item->item_image,
            ':price'            => $item->price,
            ':currency'         => $item->currency,
            ':date_of_purchase' => $item->date_of_purchase,
            ':country'          => $item->country,
            ':usage_start_date' => $item->usage_start_date,
            ':usage_end_date'   => $item->usage_end_date,
            ':history'          => $item->history,
            ':has_label'        => $item->has_label ? 'true' : 'false',
            ':id'               => $item->id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM items WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function getItemDetailsById($id)
    {
        $sql = "SELECT items.*, collections.user_id
                FROM items 
                JOIN collections ON items.collection_id = collections.id
                WHERE items.id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getImageUsage($image_name)
    {
        if ($image_name === 'default_item.png') {
            return 999; 
        }

        $sql = "SELECT COUNT(*) FROM items WHERE item_image = :image_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':image_name' => $image_name]);

        return $stmt->fetchColumn();
    }

}

?>