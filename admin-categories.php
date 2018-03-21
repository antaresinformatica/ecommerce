<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


$app->get("/admin/categories", function(){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$categories = Category::listAll();
	$page = new PageAdmin();
	$page->setTpl("categories", [
		'categories'=>$categories]);

});

$app->get("/admin/categories/create", function(){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$page = new PageAdmin();
	$page->setTpl("categories-create");	
});


$app->post("/admin/categories/create", function(){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$category = new Category();
	$category->setData($_POST);
	$category->save();
	header('Location: /admin/categories');
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$category = new Category();
    // carrega o registro que depois vai ser excluido
	$category->get((int)$idcategory); // metodo get esta em category.php
	$category->delete(); // metodo delete esta em category.php
	// volta para a tela anterior
	header('Location: /admin/categories');
	exit;

});


$app->get("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$category = new Category();
	$category->get((int)$idcategory); // metodo get esta em category.php

	$page = new PageAdmin();
	$page->setTpl("categories-update",[
		'category'=>$category->getValues()
	]);


});

$app->post("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando direto a url)
	$category = new Category();
	$category->get((int)$idcategory); // metodo get esta em category.php

	$category->setData($_POST);
	$category->save();
	header('Location: /admin/categories');
	exit;
});



$app->get("/admin/categories/:idcategory/products", function($idcategory){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando
	$category = new Category();
	$category->get((int)$idcategory);
    // Category() retorna um html
	$page = new PageAdmin();
	$page->setTpl("categories-products",[
		'category'=>$category->getValues(),

		'productsRelated'=>$category->getProducts(),  // no html (categories-products.html) vai usar isso
		'productsNotRelated'=>$category->getProducts(false) // no html (categories-products.html) vai usar isso
	]);	
});


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando
	$category = new Category();
	$category->get((int)$idcategory);
	$product = new Product();
	$product->get((int)$idproduct);
	$category->addProduct($product);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});


$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	User::verifyLogin(); // verifica se usuario esta logado (se nao esta acessando
	$category = new Category();
	$category->get((int)$idcategory);
	$product = new Product();
	$product->get((int)$idproduct);
	$category->removeProduct($product);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});




 ?>