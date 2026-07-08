

<?php


class Collection {
    public $id;
    public $user_id;
    public $parent_id;
    public $category_id;
    public $category_name;
    public $name;
    public $description;
    public $is_public;
    public $created_at;
    public $collection_image;


   public function __construct($data){
    
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->category_name = $data['category_name'] ?? 'Fără categorie';
        $this->parent_id = $data['parent_id'] ?? null;
        $this->name = $data['name'] ?? 'Nume necunoscut';
        $this->description = $data['description'] ?? 'Fara descriere';
        $this->is_public = $data['is_public'] ?? false;
        $this->created_at = $data['created_at'] ?? null;
        $this->collection_image=$data['collection_image'];
   }

}

?>