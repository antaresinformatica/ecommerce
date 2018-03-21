<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;


$app->get("/admin/products", function(){

	User::verifyLogin(); // esta dentro de model/user
	$products = Product::listAll();
	$page = new PageAdmin();
	$page->setTpl("products", [
		"products"=>$products
	]);

});

$app->get("/admin/products/create", function(){
	User::verifyLogin(); // esta dentro de model/user
	$page = new PageAdmin();
	$page->setTpl("products-create");

});

// ao clicar no botao cadastrar ( na tela create - chamada acima)
$app->post("/admin/products/create", function(){
	User::verifyLogin(); // esta dentro de model/user
	$product = new Product();
	$product->setData($_POST);
	$product->save();
	header("Location: /admin/products");
	exit;
});


// ao clicar no botao editar 
$app->get("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin(); // esta dentro de model/user
	// carrega os dados do produto
	$product = new Product();
	$product->get((int)$idproduct);
//var_dump($product->getValues());



	$page = new PageAdmin();
	$page->setTpl("products-update", [
		'product'=>$product->getValues()
		]);
});

// na edicao, quando clicar no botao gravar
$app->post("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin(); // esta dentro de model/user
	$product = new Product();
	$product->get((int)$idproduct);
	$product->setData($_POST);
	$product->save();
	// agora vamos gravar a imagem na pasta /res/site/img/products/
	$product->setPhoto($_FILES["file"]);
	header('Location: /admin/products');
	exit;

});

// ao clicar no excluir
$app->get("/admin/products/:idproduct/delete", function($idproduct){
	User::verifyLogin(); // esta dentro de model/user
	// carrega os dados do produto
	$product = new Product();
	$product->get((int)$idproduct);
	$product->delete();

	header('Location: /admin/products');
	exit;

});

 ?>