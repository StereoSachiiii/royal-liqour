<?php 

require_once __DIR__ . '/../controllers/CategoryController.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$categoryController = new CategoryController();


if($method === "GET"){
    switch ($action) {
        case 'getAllCategories':
            $limit = intval($_GET['limit']) ?? 50;
            $offset = intval($_GET['offset']) ?? 0;

            echo json_encode($categoryController->getAllCategories($limit, $offset));
            break;
        
        default:
            
            break;
    }





}















?>