<?php 
//use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get('/', function() {	
	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("index", [
		'products'=>Product::checkList($products)

		]);	
});


$app->get("/categories/:idcategory", function($idcategory){
	// se a url esta passando a pagina, se nao valor inicial é 1
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	$category = new Category();
	$category->get((int)$idcategory);
    $pagination = $category->getProductsPage($page);


    $pages = [];
    for ($i=1; $i <= $pagination['pages'] ; $i++) { 
    	array_push($pages, [
    		'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
    		'page'=>$i
    		]);
    
    }
	$page = new Page();
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);	
});

$app->get("/products/:desurs", function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl("product-detail",[
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
		]);
});

$app->get("/cart", function(){
	$cart = Cart::getFromSession();
	$page = new Page();
//	var_dump($cart->getValues());
//	exit;





	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

// rota para quando clica no adicionar mais 1  produto
$app->get("/cart/:idproduct/add", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->addProduct($product);
	// redireciona para o carrinho
	header("Location: /cart");
	exit;

});

// rota para quando clica no remover  produto
$app->get("/cart/:idproduct/minus", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product); // para remover só um, nao precisa passar o parametro , pois por padrão é FALSE
	// redireciona para o carrinho
	header("Location: /cart");
	exit;

});

// rota para quando clica remover todos os itens do produto
$app->get("/cart/:idproduct/remove", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true); // o parametro true indica para a função que são todos os produtos
	// redireciona para o carrinho
	header("Location: /cart");
	exit;

});
           
$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();
 	$cart->setFreight($_POST['zipcode']);
// 	var_dump($cart);
 	header("Location: /cart");
 	exit;

});

$app->get("/checkout", function(){
	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();
	$page = new Page();
	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()

	]);

});

$app->get("/login", function(){

	$page = new Page();
//	$page->setTpl("login");

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);

});

$app->post("/login", function(){
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch(Excption $e) {
		User::setError($e->getMessage());

	}



	User::login($_POST['login'], $_POST['password']);
	header("Location: /checkout");
	exit;
});


$app->get("/logout", function(){
	User::logout();
	header("Location: /login");
	exit;
});

$app->post("/register", function(){
	// registra os dados que foram digitados, pois se houver algum campo errado, o usuario nao precisara digitar tudo novamente
	$_SESSION['registerValues'] = $_POST;

	// antes de gravar vamos fazer algumas validações
	if (!isset($_POST['name']) || $_POST['name']== ''){
		// verifica se o POST foi criado e enviado para este formulario
		// verifica se o POST nao esta em branco
		User::setErrorRegister("Preencha o seu nome.");
		header('Location: /login');
		exit;
	}
	if (!isset($_POST['email']) || $_POST['email']== ''){
		// verifica se o POST foi criado e enviado para este formulario
		// verifica se o POST nao esta em branco
		User::setErrorRegister("Preencha o seu email.");
		header('Location: /login');
		exit;
	}	
	if (!isset($_POST['password']) || $_POST['password']== ''){
		// verifica se o POST foi criado e enviado para este formulario
		// verifica se o POST nao esta em branco
		User::setErrorRegister("Preencha a senha.");
		header('Location: /login');
		exit;
	}
	// verifica se tem 2 usuarios com mesmo email
	if (User::checkLoginExist($_POST['email']) === true){
		User::setErrorRegister("Este endereço de email já esta sendo usado por outro usuário.");
		header('Location: /login');
		exit;		
	}

	$user = new User();
	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']

		]);

	$user->save();
	user::login($_POST['email'], $_POST['password']);
	header('Location: /checkout');
	exit;

});





$app->get("/forgot", function(){
	$page = new Page();
	$page->setTpl("forgot");
});

$app->post("/forgot", function(){
	              
	$user = User::getForgot($_POST["email"], false);
	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent", function(){
	$page = new Page();
	$page->setTpl("forgot-sent");

});


$app->get("/forgot/reset", function(){
	//$user vai ter os dados do usuario
	$user = User::validForgotDecrypt($_GET["code"]);


	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"] // codigo criptigrafado
		));


});

$app->post("/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);
	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);



	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");


});


$app->get("/profile", function(){

	User::verifyLogin(false); 
	$user = User::getFromSession();
	$page = new Page();
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()

		]);
});


// salva alguma alteracao de usuario
$app->post("/profile", function(){
	User::verifyLogin(false);
	// se nao existe desperson ou nao foi definido
	if (!isset($_POST['desperson']) || $_POST['desperson'] ==='') {
		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;
	}
	if (!isset($_POST['desemail']) || $_POST['desemail'] ==='') {
		User::setError("Preencha o seu email.");
		header('Location: /profile');
		exit;
	}	



	$user = User::getFromSession();
	// se o usuario alterou o email
	if ($_POST['desemail'] !== $user->getdesemail()){
		// alterou e email, vamos ver se o email ja esta cadastrado para outro usuario
		if (User::checkLoginExist($_POST['desemail']) === true){
			User::setError("Este endereço de e-mail ja está cadastrado.");
			header('Location: /profile');
			exit;

		}

	}
	// sobrescreve o inadmin e despassword por seguranca
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	// vamos definir o login como o proprio email informado
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);
$user->update(false);
$_SESSION[User::SESSION] = $user->getValues();
	//$user->save();
	User::setSuccess('Dados alterados com sucesso!');
	header('Location: /profile');
	exit;

});


 ?>
