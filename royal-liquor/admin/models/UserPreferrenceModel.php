<?php
class UserPreferrenceModel{
    public $id;
    public $user_id;
    public $preferred_sweetness;
    public $preferred_bitterness;
    public $preferred_strength;
    public $preferred_smokiness;
    public $preferred_fruitiness;
    public $preferred_spiciness;
    public $favorite_categories; // This can be stored as a JSON string or serialized array

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->preferred_sweetness = $data['preferred_sweetness'] ?? null;
        $this->preferred_bitterness = $data['preferred_bitterness'] ?? null;
        $this->preferred_strength = $data['preferred_strength'] ?? null;
        $this->preferred_smokiness = $data['preferred_smokiness'] ?? null;
        $this->preferred_fruitiness = $data['preferred_fruitiness'] ?? null;
        $this->preferred_spiciness = $data['preferred_spiciness'] ?? null;
        $this->favorite_categories = isset($data['favorite_categories']) ? json_decode($data['favorite_categories'], true) : [];
}


public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'preferred_sweetness' => $this->preferred_sweetness,
            'preferred_bitterness' => $this->preferred_bitterness,
            'preferred_strength' => $this->preferred_strength,
            'preferred_smokiness' => $this->preferred_smokiness,
            'preferred_fruitiness' => $this->preferred_fruitiness,
            'preferred_spiciness' => $this->preferred_spiciness,
            'favorite_categories' => json_encode($this->favorite_categories),
        ];
    }
}
?>