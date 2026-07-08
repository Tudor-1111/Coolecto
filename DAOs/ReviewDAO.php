<?php

class ReviewDAO {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    
    public function isCollectionOwner($collection_id, $user_id) {
        $stmt = $this->db->prepare("SELECT user_id FROM collections WHERE id = :cid");
        $stmt->execute([':cid' => $collection_id]);
        $owner_id = $stmt->fetchColumn();
        
        return $owner_id == $user_id;
    }

   
    public function add(Review $review) {
        $sql = "INSERT INTO reviews (collection_id, user_id, nota, descriere, created_at) 
                VALUES (:cid, :uid, :nota, :desc, NOW())";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cid'  => $review->collection_id,
            ':uid'  => $review->user_id,
            ':nota' => $review->nota,
            ':desc' => $review->descriere
        ]);
    }
}
?>