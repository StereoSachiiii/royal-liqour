<?php 
require_once __DIR__.'/admin/controllers/ProductController.php';
require_once __DIR__.'/admin/repositories/ProductRepository.php';
require_once __DIR__.'/core/session.php';

$session = Session::getInstance(); 
$repo = new ProductRepository();
$product = new ProductController($repo, $session);


$products = $product->getAllProducts(50,0);


foreach($products as $product){
    echo "<pre>";
    print_r($product);
    echo "</pre>";
}




?>