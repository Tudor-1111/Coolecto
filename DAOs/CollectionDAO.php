<?php


class CollectionDAO {
    private $db;


    public function __construct($pdo){
        $this ->db = $pdo;
    }

    public function add (Collection $collection)
    {

        if ($collection->category_id === 'custom' && !empty($collection->category_name)) {
            $nume_custom = trim($collection->category_name);
            
     
            $stmtCat = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name)");
            $stmtCat->execute([':name' => $nume_custom]);
            $existentId = $stmtCat->fetchColumn();

            if ($existentId) {
        
                $collection->category_id = $existentId;
            } else {

           
                $stmtInsert = $this->db->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmtInsert->execute([':name' => $nume_custom]);
            
                $collection->category_id = $this->db->lastInsertId(); 
            }
        }

       
        $sql="INSERT INTO collections (user_id, parent_id, category_id, name, description, collection_image, is_public) 
        VALUES (:user_id, :parent_id, :category_id, :name, :description, :image, :is_public)";
            
        $stmt=$this->db->prepare($sql);

        $stmt->execute([
            ':user_id'     => $collection->user_id,
            ':parent_id'   => $collection->parent_id,
            ':category_id' => $collection->category_id,
            ':name'        => $collection->name,
            ':description' => $collection->description,
            ':image'       => $collection->collection_image,
            ':is_public'   => $collection->is_public
        ]);
    }

    public function getById($collection_id)
    {
        $sql="SELECT * FROM COLLECTIONS WHERE id=:collection_id";
        $stmt=$this->db->prepare($sql);
        $stmt->execute([':collection_id' => $collection_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCollectionsByUserId($id)
    {
        $sql="SELECT collections.*, categories.name AS category_name 
                FROM collections 
                LEFT JOIN categories ON collections.category_id = categories.id 
                WHERE user_id=:id AND parent_id IS NULL";
        $stmt=$this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubcollectionsByCollectionId($collection_id, $is_from_community = false)
    {
       
        $sql = "SELECT collections.*, categories.name AS category_name 
                FROM collections 
                LEFT JOIN categories ON collections.category_id = categories.id 
                WHERE parent_id = :parent_id";

        
        if ($is_from_community) {
            $sql .= " AND collections.is_public = TRUE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':parent_id' => $collection_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(Collection $collection)
    {
        $sql="UPDATE collections 
                   SET name = :name, 
                       description = :description, 
                       collection_image = :collection_image,
                       category_id=:category_id, 
                       is_public = :is_public 
                   WHERE id = :id";
            
        $stmt=$this->db->prepare($sql);

        $stmt->execute([
            ':category_id' => $collection->category_id,
            ':name'        => $collection->name,
            ':description' => $collection->description,
            ':collection_image'       => $collection->collection_image,
            ':is_public'   => $collection->is_public,
            ':id' => $collection->id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM collections WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function getCollectionDetailsById($id)
    {
        $sql = "SELECT c.*, cat.name AS category_name, u.username 
                FROM collections c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countImageUsage($image_name)
    {
        if ($image_name === 'default_collection.png') {
            return 999; 
        }

        $sql = "SELECT COUNT(*) FROM collections WHERE collection_image = :image_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':image_name' => $image_name]);

        return $stmt->fetchColumn();
    }

   
    public function getPublicCollections($filters = []) {

        
        $sql = "SELECT c.*, u.username, cat.name AS category_name, COALESCE(AVG(r.nota), 0) AS medie_rating
                FROM collections c 
                JOIN users u ON c.user_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN reviews r ON c.id = r.collection_id
                WHERE c.is_public = TRUE";

        $params = [];

        
        if (!empty($filters['name'])) {
            $sql .= " AND (LOWER(c.name) LIKE LOWER(:keyword) OR LOWER(c.description) LIKE LOWER(:keyword))";
            $params[':keyword'] = '%' . trim($filters['name']) . '%';
        }

        if (!empty($filters['category']) && is_numeric($filters['category']) && $filters['category'] != 7) {
            $sql .= " AND c.category_id = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['user'])) {
            $sql .= " AND u.username = :user";
            $params[':user'] = $filters['user'];
        }

        
        $sql .= " GROUP BY c.id, u.username, cat.name";

     
        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'newest') {
            $sql .= " ORDER BY c.created_at DESC";
        } elseif ($sort === 'oldest') {
            $sql .= " ORDER BY c.created_at ASC";
        } elseif ($sort === 'az') {
            $sql .= " ORDER BY LOWER(c.name) ASC";
        } elseif ($sort === 'za') {
            $sql .= " ORDER BY LOWER(c.name) DESC";
        } elseif ($sort === 'rating_desc') {
            $sql .= " ORDER BY medie_rating DESC, c.created_at DESC"; 
        } elseif ($sort === 'rating_asc') {
            $sql .= " ORDER BY medie_rating ASC, c.created_at DESC";  
        } else {
            $sql .= " ORDER BY c.created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $collections = [];
        foreach($rows as $row){
            
            $collections[] = new Collection($row);
        }

        return $collections;
    }


    public function getReviewsByCollectionId($collectionId) {
        try {
            $sql = "
                SELECT r.nota, r.descriere, r.created_at, u.username 
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.collection_id = :cid
                ORDER BY r.created_at DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cid' => $collectionId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Eroare la extragerea recenziilor: " . $e->getMessage());
            return []; 
        }
    }


    public function getGlobalStatistics() {
        try {
          
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $totalUsers = $stmt->fetchColumn();

            
            $stmt = $this->db->query("SELECT COUNT(*) FROM collections WHERE is_public = TRUE");
            $totalCollections = $stmt->fetchColumn();

       
            $stmt = $this->db->query("
                SELECT COUNT(r.id) 
                FROM reviews r 
                JOIN collections c ON r.collection_id = c.id 
                WHERE c.is_public = TRUE
            ");
            $totalReviews = $stmt->fetchColumn();

        
            $sqlTopRated = "
                SELECT c.name AS collection_name, u.username, COALESCE(AVG(r.nota), 0) AS medie_rating
                FROM collections c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN reviews r ON c.id = r.collection_id
                WHERE c.is_public = TRUE
                GROUP BY c.id, u.username
                ORDER BY medie_rating DESC, c.id DESC 
                LIMIT 1
            ";
            $stmt = $this->db->query($sqlTopRated);
            $topRated = $stmt->fetch(PDO::FETCH_ASSOC);

            
            $sqlExpensive = "
                SELECT c.name AS collection_name, u.username, 
                       COALESCE(SUM(
                           CASE 
                            WHEN UPPER(i.currency) IN ('EUR', 'EURO') THEN i.price * 4.97
                            WHEN UPPER(i.currency) IN ('USD', 'DOLLARS') THEN i.price * 4.60
                            WHEN UPPER(i.currency) IN ('GBP', 'POUNDS') THEN i.price * 5.85  
                            WHEN UPPER(i.currency) IN ('RON' , 'LEI') THEN i.price 
                            ELSE i.price 
                           END
                       ), 0) AS total_valoare_ron
                FROM collections c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN items i ON c.id = i.collection_id
                WHERE c.is_public = TRUE
                GROUP BY c.id, u.username
                ORDER BY total_valoare_ron DESC, c.id DESC 
                LIMIT 1
            ";
            $stmt = $this->db->query($sqlExpensive);
            $mostExpensive = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total_users'       => $totalUsers,
                'total_collections' => $totalCollections,
                'total_reviews'     => $totalReviews,
                'top_rated_name'    => $topRated['collection_name'] ?? 'N/A',
                'top_rated_owner'   => $topRated['username'] ?? 'N/A',
                'top_rated_score'   => round($topRated['medie_rating'] ?? 0, 2),
                'expensive_name'    => $mostExpensive['collection_name'] ?? 'N/A',
                'expensive_owner'   => $mostExpensive['username'] ?? 'N/A',
                'expensive_value'   => round($mostExpensive['total_valoare_ron'] ?? 0, 2),
                'currency'          => 'RON' 
            ];

        } catch (PDOException $e) {
            error_log("Eroare statistici globale: " . $e->getMessage());
            return [];
        }
    }

    public function getFilteredStatistics($filters = []) {
        try {
            $sql = "
                SELECT 
                    COUNT(DISTINCT c.id) AS total_filtered_collections,
                    COUNT(i.id) AS total_filtered_items,
                    COALESCE(SUM(
                        CASE 
                        WHEN UPPER(i.currency) IN ('EUR', 'EURO') THEN i.price * 4.97
                        WHEN UPPER(i.currency) IN ('USD', 'DOLLARS') THEN i.price * 4.60
                        WHEN UPPER(i.currency) IN ('GBP', 'POUNDS') THEN i.price * 5.85  
                        WHEN UPPER(i.currency) IN ('RON' , 'LEI') THEN i.price 
                        ELSE i.price 
                        END
                    ), 0) AS total_filtered_value_ron
                FROM collections c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN items i ON c.id = i.collection_id
                WHERE c.is_public = TRUE
            ";

            $params = [];
            
            if (!empty($filters['name'])) {
                $sql .= " AND (LOWER(c.name) LIKE LOWER(:keyword) OR LOWER(c.description) LIKE LOWER(:keyword))";
                $params[':keyword'] = '%' . trim($filters['name']) . '%';
            }
            if (!empty($filters['category']) && is_numeric($filters['category']) && $filters['category'] != 7) {
                $sql .= " AND c.category_id = :category";
                $params[':category'] = $filters['category'];
            }
            if (!empty($filters['user'])) {
                $sql .= " AND u.username = :user";
                $params[':user'] = $filters['user'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Eroare statistici filtrate: " . $e->getMessage());
            return [];
        }
    }
    
    public function getFilteredCollectionsList($filters = []) {
        try {
            $sql = "
                SELECT 
                    c.id, c.name AS collection_name, c.created_at,
                    u.username AS owner,
                    cat.name AS category_name,
                    COUNT(DISTINCT i.id) AS items_count,
                    COALESCE(SUM(
                        CASE 
                        WHEN UPPER(i.currency) IN ('EUR', 'EURO') THEN i.price * 4.97
                        WHEN UPPER(i.currency) IN ('USD', 'DOLLARS') THEN i.price * 4.60
                        WHEN UPPER(i.currency) IN ('GBP', 'POUNDS') THEN i.price * 5.85  
                        WHEN UPPER(i.currency) IN ('RON' , 'LEI') THEN i.price  
                        ELSE i.price 
                        END
                    ), 0) AS total_value_ron,
                    COALESCE(AVG(r.nota), 0) AS medie_rating
                FROM collections c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN items i ON c.id = i.collection_id
                LEFT JOIN reviews r ON c.id = r.collection_id
                WHERE c.is_public = TRUE
            ";

            $params = [];
           
            if (!empty($filters['name'])) {
                $sql .= " AND (LOWER(c.name) LIKE LOWER(:keyword) OR LOWER(c.description) LIKE LOWER(:keyword))";
                $params[':keyword'] = '%' . trim($filters['name']) . '%';
            }
            if (!empty($filters['category']) && is_numeric($filters['category']) && $filters['category'] != 7) {
                $sql .= " AND c.category_id = :category";
                $params[':category'] = $filters['category'];
            }
            if (!empty($filters['user'])) {
                $sql .= " AND u.username = :user";
                $params[':user'] = $filters['user'];
            }

            $sql .= " GROUP BY c.id, u.username, cat.name";
            $sql .= " ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Eroare la lista de rapoarte: " . $e->getMessage());
            return [];
        }
    }


    public function getTopPopularCollections() {
        try {
            $sql = "
                SELECT c.id, c.name, c.description, c.created_at, u.username, 
                       COALESCE(AVG(r.nota), 0) AS medie_rating
                FROM collections c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN reviews r ON c.id = r.collection_id
                WHERE c.is_public = TRUE
                GROUP BY c.id, c.name, c.description, c.created_at, u.username
                ORDER BY medie_rating DESC, c.id DESC
                LIMIT 10
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Eroare la generarea RSS: " . $e->getMessage());
            return [];
        }
    }
}