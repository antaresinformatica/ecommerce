<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model {
	public function save()
	{

//		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
//			":idcategory"=>$this->getidcategory(),
//			":descategory"=>$this->getdescategory()
//			));



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
//var_dump($results);
//		endereco esta indo em branco
//echo "<br>";
//echo " ============================================";
//echo "<br>";
//echo "order:".$this->getidorder() ;
//echo "<br>";
//echo  "carrinho:".$this->getidcart();
//echo "<br>";
//echo  "usuario:".$this->getiduser();
//echo "<br>";
//echo  "status:".$this->getidstatus();
//echo "<br>";
//echo  "endereco:".$this->getidaddress();
//echo "<br>";
//echo  "total:".$this->getvltotal();
//echo "<br>";
//echo " ============================================";

		if(count($results)>0 ){
			$this->setData($results[0]);
		}
	}
	public function get($idorder)
	{
		$sql = new Sql();
		$results = $sql->select ("
			SELECT * from tb_orders a 
			inner join tb_ordersstatus b using(idstatus)
			inner join tb_carts c using(idcart)
			inner join tb_users d on d.iduser = a.iduser
			inner join tb_addresses e using(idaddress)
			inner join tb_person f on f.idperson = d.idperson
			where a.idorder = :idorder
			", [
				':idorder'=>$idorder
			]);
		if (count($results)[0]){
			$this->setData($results[0]);
		}

	}




}

?>