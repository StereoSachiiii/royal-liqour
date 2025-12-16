<?php

class RecipeIngredientModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM recipe_ingredients WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function findByRecipeId($recipeId) {
        $sql = "SELECT ri.*, i.name as ingredient_name, i.unit 
                FROM recipe_ingredients ri 
                JOIN ingredients i ON ri.ingredient_id = i.id 
                WHERE ri.recipe_id = ?";
        return $this->db->fetchAll($sql, [$recipeId]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) 
                VALUES (?, ?, ?, ?)";
        return $this->db->insert($sql, [
            $data['recipe_id'],
            $data['ingredient_id'],
            $data['quantity'],
            $data['unit']
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE recipe_ingredients 
                SET recipe_id = ?, ingredient_id = ?, quantity = ?, unit = ? 
                WHERE id = ?";
        return $this->db->update($sql, [
            $data['recipe_id'],
            $data['ingredient_id'],
            $data['quantity'],
            $data['unit'],
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM recipe_ingredients WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    public function deleteByRecipeId($recipeId) {
        $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = ?";
        return $this->db->delete($sql, [$recipeId]);
    }
}
