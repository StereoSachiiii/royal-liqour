<?php
declare(strict_types=1);


require_once __DIR__.'/../controllers/ProductController.php';
require_once __DIR__.'/../repositories/ReportRepository.php';
require_once __DIR__.'/../../core/Session.php';

header('Content-Type: application/json');

$session = Session::getInstance(); 
$repo = new ProductRepository();
$product = new ProductController($repo, $session);




$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';


if($method === 'GET'){

    switch ($action) {
        case 'getAllProducts':
            $offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
            $limit = $_GET['limit'] ?? $_POST['limit'] ??50;
            echo json_encode( $product->getAllProducts(intval($limit),intval($offset)));
            break;
        
        default:
            # code...
            break;
    }



}






















?>