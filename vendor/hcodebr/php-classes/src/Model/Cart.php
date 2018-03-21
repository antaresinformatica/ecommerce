<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

	const SESSION = "Cart";
	public static function getFromSession()
	{
		$cart = new Cart();
		if (isset($_SESSION[Cart::SESSION]) && $_SESSION[Cart::SESSION]['idcart']>0){
			$cart->get((int)$_SESSION[cART::SESSION]['idcart']);
		} else {
			$cart->getFromSessionID();
			if (!(int)$cart->getidcart() > 0){
				$data = [
					'dessessionid'=>session_id()
					];

					if (User::checkLogin(false)){


						$user = User::getFromSession();
						$data['iduser'] = $user->getiduser();
					}
					$cart->setData($data);
					$cart->save();
					$cart->setToSession();


			}
		}
		return $cart;
	}

	public function setToSession()
	{

		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	public function getFromSessionID()
	{

		$sql = new Sql();
		$results = $sql->select("SELECT * from tb_carts where dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
			]);
		if (count($results) > 0 ){
		    $this->setData($results[0]);	
		}

	}


	public function get(int $idcart)
	{

		$sql = new Sql();
		$results = $sql->select("SELECT * from tb_carts where idcart = :idcart", [
			':idcart'=>$idcart
			]);
		if (count($results) > 0 ){
		    $this->setData($results[0]);	
		}
		

	}



	public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(:idcart ,:dessessionid , :iduser ,:deszipcode ,:vlfreight ,:nrdays)", [
				':idcart'      =>$this->getidcart(),
				':dessessionid'=>$this->getdessessionid(),
				':iduser'      =>$this->getiduser(),
				':deszipcode'  =>$this->getdeszipcode(),
				':vlfreight'   =>$this->getvlfreight(),
				':nrdays'      =>$this->getnrdays()
			]);
		$this->setData($results[0]);
	}

	public function addProduct(Product $product)
	{
		$sql = new Sql();
		$sql->query("INSERT into tb_cartsproducts (idcart, idproduct) values (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
			]);
	}
	public function removeProduct(Product $product, $all = false)
	{
		// esta função pode remover um produto ou todos, isso porque
		// na linha do produto tem a quantidade, de diminuir a quantidade diminui 1 produto
		// porem se clicar no X que esta na linha do produto e tinha 3 unidades do produto , entao remove as tres unidades do produto
		$sql = new Sql();
		if ($all){
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() where idcart = :idcart and idproduct = :idproduct and dtremoved is null", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()

				]);
		} else {
			// essa parte é executada quando clica no botao menos (menos 1 produto)
			// por isso tem o LIMIT 1 (funcao do sql para executar so um registro)
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() where idcart = :idcart and idproduct = :idproduct and  tdremoved is null limit 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()

				]);


		}

	}

	public function getProducts()
	{
		$sql = new Sql();
//		var_dump ( 
//				SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vllength, b.vlweight, b.desurl, count(*) as nrqtd , sum(b.vlprice) as vltotal
//				from tb_cartsproducts a 
//				INNER JOIN tb_products b on a.idproduct = b.idproduct
//				where a.idcart = :idcart and a.dtremoved is null
//				group by b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vllength, b.vlweight, b.desurl
//				order by b.desproduct
//			);
//			exit;

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vllength, b.vlweight, b.desurl, count(*) as nrqtd , sum(b.vlprice) as vltotal
			from tb_cartsproducts a 
			INNER JOIN tb_products b on a.idproduct = b.idproduct
			where a.idcart = :idcart and a.dtremoved is null
			group by b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vllength, b.vlweight, b.desurl
			order by b.desproduct ",[
				':idcart'=>$this->getidcart()
			]);
		return Product::checkList($rows);
	}

}

?>