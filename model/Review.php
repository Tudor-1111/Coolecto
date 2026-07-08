<?php

class Review {
    public $id;
    public $collection_id;
    public $user_id;
    public $nota;
    public $descriere;
    public $created_at;

    public function __construct($data) {
        
        $this->id = $data['id'] ?? null;
        $this->collection_id = $data['collection_id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->nota = $data['nota'] ?? null;
        $this->descriere = $data['descriere'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
    }
}
?>