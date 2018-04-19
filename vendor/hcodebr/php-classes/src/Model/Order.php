<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model {

	const SUCCESS = "Order-Success";
	const ERROR = "Order-Error";

	public function save()
	{

		$sql = new Sql();
		//$results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [		
		$results = $sql->select("CALL sp_orders_save(:idorder,:idcart,:iduser,:idstatus,:idaddress,:vltotal)",array(

			"idorder"  =>$this->getidorder(),
			"idcart"   =>$this->getidcart(),
			"iduser"   =>$this->getiduser(),
			"idstatus" =>$this->getidstatus(),
			"idaddress"=>$this->getidaddress(),
			"vltotal"  =>$this->getvltotal()

		));

		if(count($results)>0 ){
			$this->setData($results[0]);
		}
	}

	public function get($idorder)
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT * from tb_orders a 
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_persons f on f.idperson = d.idperson
			where a.idorder = :idorder
			", [
				':idorder'=>$idorder
			]);
//echo "<br>";		
//var_dump($results);

//echo "<br>";
//echo "<em order.php";
//echo "<br>";
//echo $idorder;
//echo "<br>";
//exit;

		if (count($results) > 0){

			$this->setData($results[0]);

		}

	}

	public static function listAll()
	{

		$sql = new Sql();
		return $sql->select ("
			SELECT * from tb_orders a 
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_persons f on f.idperson = d.idperson
			order by a.dtregister DESC


		");

	}
	public function delete()
	{
		$sql = new Sql();
		$sql->query("DELETE from tb_orders where idorder = :idorder",[
			':idorder'=>$this->getidorder()
		]);

	}
	public function getCart():Cart
	{
		$cart = new Cart();

		$cart->get((int)$this->getidcart());
		return $cart;

	}




	public static function setError($msg)
	{
		$_SESSION[Order::ERROR] = $msg;
	}
	public static function getError()
	{
		$msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : "";
		Order::clearError();
		return $msg;
	}
	public static function clearError()
	{
		$_SESSION[Order::ERROR] = NULL;
	}	
    // mensagens de sucesso
	public static function setSuccess($msg)
	{
		$_SESSION[Order::SUCCESS] = $msg;
	}
	public static function getSuccess()
	{
		$msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : "";
		Order::clearSuccess();
		return $msg;
	}
	public static function clearSuccess()
	{
		$_SESSION[Order::SUCCESS] = NULL;
	}

	public static function getPage($page = 1 , $itemsPerPage = 10)
	{

		$start = ($page - 1 ) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			from tb_orders a
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_persons f on f.idperson = d.idperson
			order by a.dtregister DESC
			LIMIT $start, $itemsPerPage;

		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1 , $itemsPerPage = 10)
	{

		$start = ($page - 1 ) * $itemsPerPage;

		$sql = new Sql();



		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			from tb_orders a
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_persons f on f.idperson = d.idperson
			where a.idorder = :id or f.desperson like :search
			order by a.dtregister DESC
			LIMIT $start, $itemsPerPage;


		", [
			':search'=>'%'.$search.'%',
			':id'=>$search
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}	

}

?>