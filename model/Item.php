<?php


class Item {
    public $id;
    public $collection_id;
    public $name;
    public $description;
    public $price;
    public $currency;
    public $date_of_purchase;
    public $created_at;
    public $item_image;
    public $country;
    public $usage_start_date;
    public $usage_end_date;
    public $history;
    public $has_label;


   public function __construct($data){
    
        $this->id = $data['id'] ?? null;
        $this->collection_id = $data['collection_id'] ?? null;
        $this->name = $data['name'] ?? 'Nume necunoscut';
        $this->description = $data['description'] ?? 'Fara descriere';
        $this->price = $data['price'] ?? null;
        $this->currency=$data['currency'] ?? null;
        $this->date_of_purchase=$data['date_of_purchase'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->item_image=$data['item_image'];
        $this->country = $data['country'] ?? null;
        $this->usage_start_date = $data['usage_start_date'] ?? null;
        $this->usage_end_date = $data['usage_end_date'] ?? null;
        $this->history = $data['history'] ?? null;
        $this->has_label = isset($data['has_label']) ? (bool)$data['has_label'] : false;
   }

}

?>