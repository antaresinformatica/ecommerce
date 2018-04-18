<?php 


use \Hcode\PageAdmin;
use \Hcode\Model\User;



$app->get("/admin/users", function(){
	User::verifyLogin();
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;


	if ($search != '')
	{
		$pagination = User::getPageSearch($search, $page);
	} else {
		$pagination = User::getPage($page);  
	}

	$pages = [];
	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1

		]);

	}

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$pagination['data'], 
		"search"=>$search,
		"pages"=>$pages
		));

	//$page->setTpl("users", array("users"=>$users)); // é o users.html

});

$app->get("/admin/users/create", function(){
	User::verifyLogin();
	$page = new PageAdmin([
		"header"=>true, // esta logado, entao quero o header e footer
		"footer"=>true
		]);
	$page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete", function($iduser){
	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;


		
});

$app->get('/admin/users/:iduser', function($iduser){
   User::verifyLogin();
   $user = new User();
   $user->get((int)$iduser);
   $page = new PageAdmin();
   $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

//$app->get("/admin/users/:iduser", function($iduser){
//	User::verifyLogin();
//	$page = new PageAdmin(); // pode nao passar os parametros header e footer 
//	$page->setTpl("users-update");
//});




// post
//$app->post("/admin/users/create", function(){
//	User::verifyLogin();
//	//var_dump($_POST);	
//	$user = new User();
//	// abaixo verifica se o campo inadimin (checkbox)
//	// isset verifica se foi definido valor, entao o valor é 1, senao é 0
//
// //   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
// //   $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, ["cost"=>12]);
//  	$_POST['inadmin'] = (isset($_POST["inadmin"])) ? 1 : 0;
// 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, ["cost"=>12]);

//	$user->setData($_POST);
//	// vai executar o save, que é uma funcao que esta em user.php que por sua vez vai executar uma procedure que salva dados em 2 tabelas
//	$user->save();
//	//var_dump($user);	
//	header("Location: /admin/users");
//	exit;
//});

// antes estava como acima, mudei e deixei o codigo abaixo

$app->post("/admin/users/create", function () {
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $user->setData($_POST);
    $user->save();
    header("Location: /admin/users");
    exit;
});




$app->post("/admin/users/:iduser", function($iduser){
	User::verifyLogin();
	$user = new User();
	$_POST['inadmin'] = (isset($_POST["inadmin"])) ? 1 : 0;
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();
	header("Location: /admin/users");
	exit;


		
});


 ?>