<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;


class Address extends Model {
	const SESSION_ERROR = "AddressError";
	public static function getCEP($nrcep)
	{
		$nrcep = str_replace("-", "", $nrcep); // retira o traco do cep
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $data;
	}
	public  function loadFromCEP($nrcep){
		$data = Address::getCEP($nrcep);
		if (isset($data['logradouro']) && $data['logradouro']) {

			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescoutry('Brasil');
			$this->setdeszipcode($nrcep);
			


		}



	}
	public function save()
	{
		$sql = new Sql();
		//$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
		//	':idaddress'=>$this->getidaddress(),
		//	':idperson'=>$this->getidperson(),
		//	':desaddress'=>$this->getdesaddress(),
		//	':descomplement'=>$this->getdescomplement(),
		//	':descity'=>$this->getdescity(),
		//	':desstate'=>$this->getdesstate(),
		//	':descountry'=>$this->getdescountry(),
		//	':deszipcode'=>$this->getdeszipcode(),
		//	':desdistrict'=>$this->getdesdistrict()
		//	]);


		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
		     ':idaddress'=> $this->getidaddress(),
		     ':idperson'=> $this->getidperson(),
		     ':desaddress'=>utf8_decode($this->getdesaddress()),
		     ':desnumber'=>utf8_decode( $this->getdesnumber()),
		     ':descomplement'=>utf8_decode( $this->getdescomplement()),
		     ':descity'=>utf8_decode( $this->getdescity()),
		     ':desstate'=>utf8_decode( $this->getdesstate()),
		     ':descountry'=>utf8_decode( $this->getdescountry()),
		     ':deszipcode'=> $this->getdeszipcode(),
		     ':desdistrict'=> $this->getdesdistrict()
 			]);
//		echo "aqui     <br";
//		echo "teste";
//		echo "cod.endereco:" . $this->getidperson();
//		echo "<br>";


//		var_dump($results);
//		exit;


echo "quantidade=";
echo "<br>";
echo count($results);
echo "<br>";


		if (count($results) > 0 ){
//			echo "<br>";
//			echo " dentro do if";
//			echo "<br>";
			$this->setData($results[0]);
//			echo "<br>";
//			echo $this->setData($results[0]);
//		var_dump($results);
//		exit;
		}

	}

	public static function setMsgError($msg)
	{
		$_SESSION[Address::SESSION_ERROR] = $msg;
	}

	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
		Address::clearMsgError();
		return $msg;
	}

	public static function clearMsgError()
	{

		$_SESSION[Address::SESSION_ERROR] = NULL;
	}
	
}

?>